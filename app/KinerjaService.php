<?php

namespace app;

use app\Model\KinerjaDataRepository;
use app\Traits\KnnHelperTrait;

class KinerjaService
{
    use KnnHelperTrait;

    protected $db;
    protected $storage;

    public function __construct()
    {
        $this->db = DB::con();
        $this->storage = new KnnJsonStorageService();
    }

    public function getTrainingDataList(?int $testingPeriodeId = null, ?string $klasifikasi = null): array
    {
        $sql = "SELECT p.*, k.nama, k.jabatan, k.pekerjaan, k.username, pp.nama_periode,
                       pen.nama as penilai_nama
                FROM penilaian p
                JOIN karyawan k ON p.karyawan_id = k.id
                LEFT JOIN periode_penilaian pp ON p.periode_id = pp.id
                LEFT JOIN karyawan pen ON p.penilai_id = pen.id
                WHERE p.is_training = 1
                  AND p.status = 'selesai'";
        $params = [];

        if ($testingPeriodeId) {
            $sql .= " AND p.periode_id <> ?";
            $params[] = $testingPeriodeId;
        }

        if ($klasifikasi) {
            $sql .= " AND p.klasifikasi = ?";
            $params[] = $klasifikasi;
        }

        $sql .= " ORDER BY p.klasifikasi, k.nama";

        $data = empty($params) ? $this->db->run($sql) : $this->db->safeQuery($sql, $params);

        return array_map(function ($item) {
            $item->nilai_kriteria = $this->normalizeNilaiKriteria(json_decode($item->nilai_kriteria ?? '[]', true));
            $item->rata_rata = $this->hitungRataRata($item->nilai_kriteria);
            return $item;
        }, $data);
    }

    public function previewKlasifikasi(?int $periodeId, int $nilaiK, string $metodeJarak): array
    {
        $analysis = $this->storage->getLatestAnalysis($periodeId ?: null);
        if (!$analysis) {
            throw new \RuntimeException('File hasil analisa belum tersedia. Silakan lakukan proses analisa terlebih dahulu.');
        }

        return [
            'analysis_id' => $analysis['id'] ?? null,
            'kriteria' => $analysis['kriteria'] ?? [],
            'data_training' => $analysis['data_training'] ?? [],
            'hasil' => $analysis['hasil'] ?? [],
            'statistik' => $analysis['statistik'] ?? [],
            'params' => $analysis['params'] ?? [],
        ];
    }

    public function addToTraining(int $penilaianId, string $klasifikasi, ?int $activePeriodeId = null): void
    {
        $penilaian = $this->getPenilaianRow($penilaianId);
        if (!$penilaian) {
            throw new \RuntimeException('Penilaian tidak ditemukan');
        }

        if ($activePeriodeId && intval($penilaian->periode_id) === $activePeriodeId) {
            throw new \RuntimeException('Penilaian pada periode aktif tidak boleh dijadikan data training');
        }

        $this->db->safeQuery(
            "UPDATE penilaian
             SET is_training = 1, klasifikasi = ?, updated_at = NOW()
             WHERE id = ?",
            [$klasifikasi, $penilaianId]
        );
    }

    public function removeFromTraining(int $penilaianId): void
    {
        $this->db->safeQuery(
            "UPDATE penilaian
             SET is_training = 0, updated_at = NOW()
             WHERE id = ?",
            [$penilaianId]
        );
    }

    public function bulkAddToTraining(array $penilaianIds, ?string $klasifikasi, bool $autoClassify, ?int $activePeriodeId = null): array
    {
        $success = 0;
        $failed = 0;

        foreach ($penilaianIds as $id) {
            $penilaian = $this->getPenilaianRow(intval($id));
            if (!$penilaian) {
                $failed++;
                continue;
            }

            if ($activePeriodeId && intval($penilaian->periode_id) === $activePeriodeId) {
                $failed++;
                continue;
            }

            $finalKlasifikasi = $klasifikasi;
            if ($autoClassify) {
                $nilai = json_decode($penilaian->nilai_kriteria ?? '[]', true);
                $finalKlasifikasi = $this->determineKlasifikasi($this->hitungRataRata($nilai));
            }

            if (!$finalKlasifikasi) {
                $failed++;
                continue;
            }

            $this->db->safeQuery(
                "UPDATE penilaian
                 SET is_training = 1, klasifikasi = ?, updated_at = NOW()
                 WHERE id = ?",
                [$finalKlasifikasi, intval($id)]
            );
            $success++;
        }

        return ['success' => $success, 'failed' => $failed];
    }

    public function calibrateClassification(int $penilaianId, ?string $knnKlasifikasi, string $manualKlasifikasi, string $catatanKalibrasi, bool $addToTraining, ?int $activePeriodeId, $session): array
    {
        try {
            $this->db->getPdo()->beginTransaction();

            $penilaian = $this->getPenilaianRow($penilaianId);
            if (!$penilaian) {
                throw new \RuntimeException('Penilaian tidak ditemukan');
            }

            if ($addToTraining && $activePeriodeId && intval($penilaian->periode_id) === $activePeriodeId) {
                throw new \RuntimeException('Penilaian pada periode aktif tidak boleh dijadikan data training');
            }

            $this->db->safeQuery(
                "UPDATE penilaian
                 SET klasifikasi = ?,
                     is_training = ?,
                     catatan = CONCAT(IFNULL(catatan, ''), '\n[Kalibrasi] ', ?),
                     updated_at = NOW()
                 WHERE id = ?",
                [$manualKlasifikasi, $addToTraining ? 1 : 0, $catatanKalibrasi, $penilaianId]
            );

            $this->logCalibration($this->db, $session, $penilaianId, $knnKlasifikasi, $manualKlasifikasi, $catatanKalibrasi);

            $this->db->getPdo()->commit();

            return [
                'penilaian_id' => $penilaianId,
                'old_klasifikasi' => $knnKlasifikasi,
                'new_klasifikasi' => $manualKlasifikasi,
                'is_training' => $addToTraining,
            ];
        } catch (\Throwable $e) {
            if ($this->db->getPdo()->inTransaction()) {
                $this->db->getPdo()->rollBack();
            }
            throw $e;
        }
    }

    public function autoCalibrateBatch(?int $periodeId, bool $onlyUnclassified, bool $addToTraining, ?int $activePeriodeId = null): int
    {
        $sql = "SELECT id, nilai_kriteria, klasifikasi
                FROM penilaian
                WHERE status = 'selesai'";
        $params = [];

        if ($periodeId) {
            $sql .= " AND periode_id = ?";
            $params[] = $periodeId;
        }

        if ($addToTraining && $activePeriodeId) {
            $sql .= " AND periode_id <> ?";
            $params[] = $activePeriodeId;
        }

        if ($onlyUnclassified) {
            $sql .= " AND (klasifikasi IS NULL OR klasifikasi = '')";
        }

        $data = empty($params) ? $this->db->run($sql) : $this->db->safeQuery($sql, $params);
        $updated = 0;

        foreach ($data as $item) {
            $nilai = json_decode($item->nilai_kriteria ?? '[]', true);
            $klasifikasi = $this->determineKlasifikasi($this->hitungRataRata($nilai));

            $this->db->safeQuery(
                "UPDATE penilaian
                 SET klasifikasi = ?, is_training = ?, updated_at = NOW()
                 WHERE id = ?",
                [$klasifikasi, $addToTraining ? 1 : 0, $item->id]
            );
            $updated++;
        }

        return $updated;
    }

    public function updateKlasifikasi(int $penilaianId, string $klasifikasi): void
    {
        $this->db->safeQuery(
            "UPDATE penilaian
             SET klasifikasi = ?, updated_at = NOW()
             WHERE id = ?",
            [$klasifikasi, $penilaianId]
        );
    }

    public function getDistribusi(?int $periodeId = null): array
    {
        $analysis = $this->storage->getLatestAnalysis($periodeId);
        if (!$analysis) {
            return [];
        }

        $distribution = [];
        foreach (($analysis['hasil'] ?? []) as $result) {
            $label = $result['hasil_klasifikasi'] ?? null;
            if (!$label) {
                continue;
            }

            if (!isset($distribution[$label])) {
                $distribution[$label] = ['klasifikasi' => $label, 'total' => 0, 'is_training' => 0];
            }

            $distribution[$label]['total']++;
        }

        return array_values($distribution);
    }

    public function getComparison($analisaId = null): array
    {
        $analysis = $analisaId ? $this->storage->getAnalysis((string) $analisaId) : $this->storage->getLatestAnalysis();
        if (!$analysis) {
            return [
                'comparison' => [],
                'accuracy' => 0,
                'matched' => 0,
                'total_compared' => 0,
            ];
        }

        $comparison = [];
        $periodeId = intval($analysis['params']['periode_id'] ?? 0);

        foreach (($analysis['hasil'] ?? []) as $result) {
            $karyawan = $result['karyawan'] ?? [];
            $penilaian = $this->db->safeQuery(
                "SELECT klasifikasi
                 FROM penilaian
                 WHERE karyawan_id = ?
                   AND periode_id = ?
                   AND klasifikasi IS NOT NULL
                 ORDER BY updated_at DESC, id DESC
                 LIMIT 1",
                [intval($karyawan['karyawan_id'] ?? 0), $periodeId]
            )[0] ?? null;

            $comparison[] = [
                'karyawan_id' => intval($karyawan['karyawan_id'] ?? 0),
                'nama' => $karyawan['nama'] ?? '',
                'knn_klasifikasi' => $result['hasil_klasifikasi'] ?? null,
                'confidence' => isset($result['confidence']) ? floatval($result['confidence']) : null,
                'manual_klasifikasi' => $penilaian->klasifikasi ?? null,
                'match' => $penilaian ? (($result['hasil_klasifikasi'] ?? null) === $penilaian->klasifikasi) : null,
            ];
        }

        $matched = count(array_filter($comparison, fn($item) => $item['match'] === true));
        $totalCompared = count(array_filter($comparison, fn($item) => $item['match'] !== null));

        return [
            'comparison' => $comparison,
            'accuracy' => $totalCompared > 0 ? round(($matched / $totalCompared) * 100, 2) : 0,
            'matched' => $matched,
            'total_compared' => $totalCompared,
        ];
    }

    public function getPenilaianDetail(int $id)
    {
        return $this->db->safeQuery(
            "SELECT p.*, k.nama, k.jabatan, k.pekerjaan, k.username, pp.nama_periode, pen.nama as penilai_nama
             FROM penilaian p
             JOIN karyawan k ON p.karyawan_id = k.id
             LEFT JOIN periode_penilaian pp ON p.periode_id = pp.id
             LEFT JOIN karyawan pen ON p.penilai_id = pen.id
             WHERE p.id = ?",
            [$id]
        )[0] ?? null;
    }

    public function getAnalisaById($id)
    {
        $analysis = $this->storage->getAnalysis((string) $id);
        return $analysis ? (object) $this->storageSummary($analysis) : null;
    }

    public function getHasilAnalisaPayload($id): array
    {
        $analysis = $this->storage->getAnalysis((string) $id);
        if (!$analysis) {
            throw new \RuntimeException('Analisa tidak ditemukan');
        }

        return [
            'analisa' => (object) $this->storageSummary($analysis),
            'hasil_klasifikasi' => $analysis['hasil'] ?? [],
            'data_training' => $analysis['data_training'] ?? [],
            'kriteria' => $analysis['kriteria'] ?? [],
        ];
    }

    public function getDetailPerhitunganPayload($hasilKlasifikasiId): array
    {
        $found = $this->storage->findResult((string) $hasilKlasifikasiId);
        if (!$found) {
            throw new \RuntimeException('Hasil klasifikasi tidak ditemukan');
        }

        $analysis = $found['analysis'];
        $result = $found['result'];
        $kriteria = $analysis['kriteria'] ?? [];
        $trainingMap = [];

        foreach (($analysis['data_training'] ?? []) as $trainingIndex => $training) {
            $training['training_order'] = $training['training_order'] ?? ($trainingIndex + 1);
            $trainingMap[(string) ($training['id'] ?? '')] = $training;
        }

        $sortedDistances = array_values($result['all_distances'] ?? []);
        $originalDistances = array_values($result['all_distances_original'] ?? []);

        if (empty($originalDistances) && !empty($analysis['data_training'])) {
            $sortedMap = [];
            foreach ($sortedDistances as $item) {
                $sortedMap[(string) ($item['id'] ?? '')] = $item;
            }

            foreach (($analysis['data_training'] ?? []) as $trainingIndex => $training) {
                $matched = $sortedMap[(string) ($training['id'] ?? '')] ?? [
                    'id' => $training['id'] ?? null,
                    'nama' => $training['nama'] ?? '',
                    'nama_periode' => $training['nama_periode'] ?? '',
                    'nilai_training' => $training['nilai'] ?? ($training['nilai_kriteria'] ?? []),
                    'klasifikasi' => $training['klasifikasi'] ?? '',
                    'distance' => 0,
                    'ranking' => null,
                    'is_k_nearest' => false,
                ];
                $matched['training_order'] = $matched['training_order'] ?? ($trainingIndex + 1);
                $originalDistances[] = $matched;
            }
        }

        $detailJarakUrutanAwal = $this->buildDetailJarakRows(
            $originalDistances,
            $trainingMap,
            $result['karyawan']['nilai'] ?? [],
            $kriteria,
            true
        );

        $detailJarakTerurut = $this->buildDetailJarakRows(
            $sortedDistances,
            $trainingMap,
            $result['karyawan']['nilai'] ?? [],
            $kriteria,
            false
        );

        $kNearest = [];
        foreach (($result['k_nearest'] ?? []) as $index => $item) {
            $training = $trainingMap[(string) ($item['id'] ?? '')] ?? [];
            $kNearest[] = [
                'training_id' => $item['id'] ?? null,
                'nama_training' => $item['nama'] ?? ($training['nama'] ?? ''),
                'periode_training' => $item['nama_periode'] ?? ($training['nama_periode'] ?? '-'),
                'klasifikasi_training' => $item['klasifikasi'] ?? ($training['klasifikasi'] ?? ''),
                'nilai_jarak' => $item['distance'] ?? 0,
                'ranking' => $item['ranking'] ?? ($index + 1),
            ];
        }

        return [
            'hasil' => $result,
            'kriteria' => $kriteria,
            'k_nearest' => $kNearest,
            'detail_jarak' => $detailJarakTerurut,
            'detail_jarak_terurut' => $detailJarakTerurut,
            'detail_jarak_urutan_awal' => $detailJarakUrutanAwal,
        ];
    }

    protected function buildDistanceBreakdown(array $nilaiTesting, array $nilaiTraining, array $kriteria): array
    {
        $nilaiTesting = $this->normalizeNilaiKriteria($nilaiTesting);
        $nilaiTraining = $this->normalizeNilaiKriteria($nilaiTraining);
        $terms = [];
        $totalSquared = 0;
        $count = max(count($nilaiTesting), count($nilaiTraining), count($kriteria));

        for ($index = 0; $index < $count; $index++) {
            $testing = floatval($nilaiTesting[$index] ?? 0);
            $training = floatval($nilaiTraining[$index] ?? 0);
            $selisih = $testing - $training;
            $selisihKuadrat = pow($selisih, 2);
            $totalSquared += $selisihKuadrat;

            $terms[] = [
                'kode' => $kriteria[$index]['kode'] ?? ('C' . ($index + 1)),
                'nama' => $kriteria[$index]['nama'] ?? ('Kriteria ' . ($index + 1)),
                'testing' => $testing,
                'training' => $training,
                'selisih' => $selisih,
                'selisih_kuadrat' => $selisihKuadrat,
            ];
        }

        return [
            'terms' => $terms,
            'sum_squared' => $totalSquared,
            'sqrt_result' => sqrt($totalSquared),
        ];
    }

    protected function buildDetailJarakRows(array $items, array $trainingMap, array $nilaiTesting, array $kriteria, bool $preferTrainingOrder = false): array
    {
        $rows = [];

        foreach (array_values($items) as $index => $item) {
            $training = $trainingMap[(string) ($item['id'] ?? '')] ?? [];
            $rows[] = [
                'training_id' => $item['id'] ?? ($training['id'] ?? null),
                'training_order' => $item['training_order'] ?? ($training['training_order'] ?? ($index + 1)),
                'nama_training' => $item['nama'] ?? ($training['nama'] ?? ''),
                'periode_training' => $item['nama_periode'] ?? ($training['nama_periode'] ?? '-'),
                'klasifikasi_training' => $item['klasifikasi'] ?? ($training['klasifikasi'] ?? ''),
                'nilai_jarak' => isset($item['distance']) ? floatval($item['distance']) : 0,
                'ranking' => $item['ranking'] ?? ($preferTrainingOrder ? null : ($index + 1)),
                'is_k_nearest' => !empty($item['is_k_nearest']),
                'detail_matematis' => $this->buildDistanceBreakdown(
                    $nilaiTesting,
                    $item['nilai_training'] ?? ($training['nilai'] ?? ($training['nilai_kriteria'] ?? [])),
                    $kriteria
                ),
            ];
        }

        return $rows;
    }

    public function getStatistikAnalisaSummary(): array
    {
        $analyses = $this->storage->listAnalyses(1000, 0);
        $distribution = [];
        $totalKlasifikasi = 0;

        foreach (($analyses['data'] ?? []) as $summary) {
            $analysis = $this->storage->getAnalysis((string) ($summary['id'] ?? ''));
            foreach (($analysis['hasil'] ?? []) as $result) {
                $label = $result['hasil_klasifikasi'] ?? null;
                if (!$label) {
                    continue;
                }

                $distribution[$label] = ($distribution[$label] ?? 0) + 1;
                $totalKlasifikasi++;
            }
        }

        $distribusi = [];
        foreach ($distribution as $label => $total) {
            $distribusi[] = ['hasil_klasifikasi' => $label, 'total' => $total];
        }

        return [
            'total_analisa' => $analyses['total'] ?? 0,
            'analisa_selesai' => $analyses['total'] ?? 0,
            'total_klasifikasi' => $totalKlasifikasi,
            'distribusi' => $distribusi,
            'training' => $this->getStatistikTraining(),
        ];
    }

    public function getDaftarAnalisa(int $limit, int $offset): array
    {
        return $this->storage->listAnalyses($limit, $offset);
    }

    public function getHasilKlasifikasiByAnalisa($analisaId): array
    {
        $analysis = $this->storage->getAnalysis((string) $analisaId);
        return $analysis['hasil'] ?? [];
    }

    public function getAnalisaStatistikById($analisaId)
    {
        $analysis = $this->storage->getAnalysis((string) $analisaId);
        return $analysis ? (object) ($analysis['statistik'] ?? []) : null;
    }

    public function persistAnalysis(array $analysis): array
    {
        return $this->storage->saveAnalysis($analysis);
    }

    public function deleteAnalysis(string $analysisId): bool
    {
        return $this->storage->deleteAnalysis($analysisId);
    }

    public function hasStoredAnalysis(): bool
    {
        return $this->storage->hasAnalyses();
    }

    public function getLatestAnalysis(?int $periodeId = null): ?array
    {
        return $this->storage->getLatestAnalysis($periodeId);
    }

    public function getLatestKnnLookup(?int $periodeId = null): array
    {
        return $this->storage->buildLookup($periodeId);
    }

    protected function getKinerjaDataRepository(): KinerjaDataRepository
    {
        if ($this->kinerjaDataRepository === null) {
            $this->kinerjaDataRepository = new KinerjaDataRepository();
        }

        return $this->kinerjaDataRepository;
    }

    protected function getPenilaianRow(int $id)
    {
        return $this->db->safeQuery("SELECT * FROM penilaian WHERE id = ?", [$id])[0] ?? null;
    }

    protected function storageSummary(array $analysis): array
    {
        return [
            'id' => $analysis['id'] ?? null,
            'kode_analisa' => $analysis['kode_analisa'] ?? null,
            'nama_analisa' => $analysis['nama_analisa'] ?? null,
            'periode_id' => $analysis['params']['periode_id'] ?? null,
            'nilai_k' => $analysis['params']['k'] ?? null,
            'metode_jarak' => $analysis['params']['metode_jarak'] ?? null,
            'normalisasi' => $analysis['params']['normalisasi'] ?? null,
            'total_data_training' => count($analysis['data_training'] ?? []),
            'total_data_testing' => count($analysis['hasil'] ?? []),
            'status' => 'selesai',
            'dibuat_oleh' => $analysis['dibuat_oleh'] ?? null,
            'dibuat_oleh_nama' => $analysis['dibuat_oleh_nama'] ?? null,
            'created_at' => $analysis['created_at'] ?? null,
            'updated_at' => $analysis['updated_at'] ?? null,
            'total_sangat_baik' => $analysis['statistik']['total_sangat_baik'] ?? 0,
            'total_baik' => $analysis['statistik']['total_baik'] ?? 0,
            'total_cukup' => $analysis['statistik']['total_cukup'] ?? 0,
            'total_kurang' => $analysis['statistik']['total_kurang'] ?? 0,
            'waktu_proses_detik' => $analysis['waktu_proses'] ?? 0,
        ];
    }
}
