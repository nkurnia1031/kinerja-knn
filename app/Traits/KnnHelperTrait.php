<?php

namespace app\Traits;

/**
 * ============================================================================
 * KnnHelperTrait
 * ============================================================================
 * Trait yang menyediakan fungsi-fungsi bersama untuk module KNN.
 * Digunakan oleh AnalisaKinerjaController dan KlasifikasiKinerjaController.
 * 
 * Features:
 * - Konstanta klasifikasi (labels & threshold)
 * - Perhitungan jarak (Euclidean & Manhattan)
 * - Klasifikasi KNN
 * - Helper untuk data retrieval
 * - Badge & label formatting
 * - Logging
 * 
 * @package    app\Traits
 * @version    1.0.0
 */
trait KnnHelperTrait
{
    protected $kinerjaDataRepository = null;

    /*
    |--------------------------------------------------------------------------
    | Classification Constants
    |--------------------------------------------------------------------------
    */
    
    /**
     * Available classification labels
     * 
     * @return array
     */
    protected function getKlasifikasiLabels(): array
    {
        return ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'];
    }

    /**
     * Classification threshold ranges
     * 
     * @return array
     */
    protected function getKlasifikasiThreshold(): array
    {
        return [
            'Sangat Baik' => ['min' => 3.5, 'max' => 4.0],
            'Baik'        => ['min' => 2.75, 'max' => 3.49],
            'Cukup'       => ['min' => 2.0, 'max' => 2.74],
            'Kurang'      => ['min' => 0, 'max' => 1.99]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | KNN Algorithm Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Perform KNN classification on test data
     * 
     * @param array $dataTest Test data with 'nilai' array
     * @param array $dataTraining Training data collection
     * @param int $k Number of nearest neighbors
     * @param array $bobot Reserved for compatibility, not used in unweighted KNN
     * @param string $metode Distance calculation method ('euclidean' or 'manhattan')
     * @return array Classification result with voting details
     */
    protected function klasifikasiKNN(array $dataTest, array $dataTraining, int $k, array $bobot, string $metode = 'euclidean'): array
    {
        // Calculate distances to all training data
        $distancesOriginal = [];
        foreach (array_values($dataTraining) as $index => $train) {
            $distance = $this->hitungJarak($dataTest['nilai'], $train['nilai'], $bobot, $metode);
            $distancesOriginal[] = [
                'id'              => $train['id'],
                'nama'            => $train['nama'],
                'nama_periode'    => $train['nama_periode'] ?? '',
                'nilai_training'  => $train['nilai'],
                'klasifikasi'     => $train['klasifikasi'],
                'distance'        => $distance,
                'training_order'  => $index + 1,
            ];
        }

        $distances = $distancesOriginal;

        // Sort by distance (ascending)
        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        $rankingById = [];
        foreach ($distances as $index => &$distanceItem) {
            $distanceItem['ranking'] = $index + 1;
            $distanceItem['is_k_nearest'] = $index < $k;
            $rankingById[(string) ($distanceItem['id'] ?? '')] = [
                'ranking' => $distanceItem['ranking'],
                'is_k_nearest' => $distanceItem['is_k_nearest'],
            ];
        }
        unset($distanceItem);

        foreach ($distancesOriginal as &$distanceItem) {
            $rankingMeta = $rankingById[(string) ($distanceItem['id'] ?? '')] ?? [
                'ranking' => null,
                'is_k_nearest' => false,
            ];
            $distanceItem['ranking'] = $rankingMeta['ranking'];
            $distanceItem['is_k_nearest'] = $rankingMeta['is_k_nearest'];
        }
        unset($distanceItem);

        // Get K nearest neighbors
        $kNearest = array_slice($distances, 0, $k);

        // Perform majority voting
        $votes = [];
        foreach ($kNearest as $neighbor) {
            $kelas = $neighbor['klasifikasi'];
            $votes[$kelas] = ($votes[$kelas] ?? 0) + 1;
        }

        // Determine winner
        $hasil = '';
        $maxVotes = 0;
        foreach ($votes as $kelas => $count) {
            if ($count > $maxVotes) {
                $maxVotes = $count;
                $hasil = $kelas;
            }
        }

        return [
            'karyawan'              => $dataTest,
            'all_distances_original'=> $distancesOriginal,
            'all_distances'         => $distances,
            'k_nearest'             => $kNearest,
            'voting'                => $votes,
            'hasil_klasifikasi'     => $hasil,
            'confidence'            => round(($maxVotes / $k) * 100, 1)
        ];
    }

    /**
     * Calculate distance between two value vectors
     * Supports Euclidean and Manhattan distance methods
     * 
     * @param array $nilai1 First value vector
     * @param array $nilai2 Second value vector
     * @param array $bobot Reserved for compatibility, not used in unweighted distance
     * @param string $metode Distance method ('euclidean' or 'manhattan')
     * @return float Calculated distance
     */
    protected function hitungJarak(array $nilai1, array $nilai2, array $bobot, string $metode = 'euclidean'): float
    {
        $sum = 0;
        $n = max(count($nilai1), count($nilai2));

        for ($i = 0; $i < $n; $i++) {
            $v1 = floatval($nilai1[$i] ?? 0);
            $v2 = floatval($nilai2[$i] ?? 0);
            $selisih = $v1 - $v2;

            if ($metode === 'manhattan') {
                $sum += abs($selisih);
            } else {
                $sum += pow($selisih, 2);
            }
        }

        return $metode === 'manhattan' ? $sum : sqrt($sum);
    }

    /*
    |--------------------------------------------------------------------------
    | Statistical Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate average of values
     * 
     * @param array|null $nilai Array of numeric values
     * @return float Average value rounded to 2 decimals
     */
    protected function hitungRataRata($nilai): float
    {
        $nilai = $this->normalizeNilaiKriteria($nilai);

        if (empty($nilai) || !is_array($nilai)) {
            return 0.0;
        }
        
        $values = array_values($nilai);
        $numericValues = array_filter($values, fn($v) => is_numeric($v));
        
        if (empty($numericValues)) {
            return 0.0;
        }
        
        return round(array_sum($numericValues) / count($numericValues), 2);
    }

    /**
     * Normalize nilai_kriteria from various JSON shapes into a numeric array
     *
     * Supports:
     * - [4,3,2,...]
     * - {"1":4,"2":3,...}
     * - [{"kriteria_id":1,"nilai":4}, ...]
     */
    protected function normalizeNilaiKriteria($nilai): array
    {
        if (empty($nilai) || !is_array($nilai)) {
            return [];
        }

        $normalized = [];

        foreach ($nilai as $key => $item) {
            if (is_numeric($item)) {
                $normalized[(int) $key] = floatval($item);
                continue;
            }

            if (is_array($item) && isset($item['nilai'])) {
                $index = isset($item['kriteria_id']) ? ((int) $item['kriteria_id']) - 1 : (int) $key;
                $normalized[$index] = floatval($item['nilai']);
            }
        }

        ksort($normalized);

        return array_values($normalized);
    }

    /**
     * Determine classification based on average value
     * 
     * @param float $rata Average value
     * @return string Classification label
     */
    protected function determineKlasifikasi(float $rata): string
    {
        $threshold = $this->getKlasifikasiThreshold();
        
        foreach ($threshold as $label => $range) {
            if ($rata >= $range['min'] && $rata <= $range['max']) {
                return $label;
            }
        }
        
        return 'Kurang';
    }

    /**
     * Update statistics counters
     * 
     * @param array &$statistik Reference to statistics array
     * @param string $klasifikasi Classification label
     */
    protected function updateStatistik(array &$statistik, string $klasifikasi): void
    {
        $map = [
            'Sangat Baik' => 'total_sangat_baik',
            'Baik'        => 'total_baik',
            'Cukup'       => 'total_cukup',
            'Kurang'      => 'total_kurang'
        ];
        
        if (isset($map[$klasifikasi])) {
            $statistik[$map[$klasifikasi]]++;
        }
    }

    /**
     * Get statistics grouped by classification
     * 
     * @param array $data Data collection with klasifikasi property
     * @return array Statistics by classification
     */
    protected function getStatistikByKlasifikasi(array $data): array
    {
        $labels = $this->getKlasifikasiLabels();
        $stats = [];
        
        foreach ($labels as $label) {
            $filtered = array_filter($data, function($d) use ($label) {
                $klas = is_object($d) ? ($d->klasifikasi ?? '') : ($d['klasifikasi'] ?? '');
                return $klas === $label;
            });
            $stats[$label] = count($filtered);
        }
        
        return $stats;
    }

    /*
    |--------------------------------------------------------------------------
    | Data Retrieval Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get active assessment criteria
     * 
     * @return array List of active criteria
     */
    protected function getKriteria(): array
    {
        $db = $this->getDB();
        $data = $db->run("SELECT * FROM kriteria WHERE status = 'aktif' ORDER BY urutan");
        
        return array_map(function($item) {
            return [
                'id'      => $item->id,
                'kode'    => $item->kode,
                'nama'    => $item->nama,
                'nama_en' => $item->nama_en ?? '',
                'bobot'   => floatval($item->bobot)
            ];
        }, $data);
    }

    /**
     * Get assessment period list
     * 
     * @return array List of assessment periods
     */
    protected function getPeriodeList(): array
    {
        $db = $this->getDB();
        return $db->run("SELECT id, nama_periode, status FROM periode_penilaian ORDER BY tanggal_mulai DESC");
    }

    /**
     * Get currently active assessment period ID.
     */
    protected function getActivePeriodeId(): ?int
    {
        $db = $this->getDB();
        $row = $db->run(
            "SELECT id
             FROM periode_penilaian
             WHERE status = 'aktif'
             ORDER BY tanggal_mulai DESC, id DESC
             LIMIT 1"
        )[0] ?? null;

        return $row ? intval($row->id) : null;
    }

    /**
     * Resolve requested period, falling back to active period when omitted.
     */
    protected function resolveTestingPeriodeId(?int $periode_id = null): ?int
    {
        return $periode_id ?: $this->getActivePeriodeId();
    }

    protected function getKinerjaDataRepository(): \app\Model\KinerjaDataRepository
    {
        if ($this->kinerjaDataRepository === null) {
            $this->kinerjaDataRepository = new \app\Model\KinerjaDataRepository();
        }

        return $this->kinerjaDataRepository;
    }

    /**
     * Get training data from database
     * 
     * @param int|null $periode_id Optional period filter
     * @return array Formatted training data
     */
    protected function getDataTrainingFromDB(?int $periode_id = null): array
    {
        $testingPeriodeId = $this->resolveTestingPeriodeId($periode_id);
        $data = $this->getKinerjaDataRepository()->getTrainingDataset($testingPeriodeId);
        
        return array_map(function($item) {
            $nilai = json_decode($item->nilai_kriteria, true);
            $normalized = $this->normalizeNilaiKriteria($nilai);
            return [
                'id'          => $item->id,
                'karyawan_id' => $item->karyawan_id,
                'nama'        => $item->nama,
                'jabatan'     => $item->jabatan ?? '',
                'nilai'       => $normalized,
                'nilai_kriteria' => $normalized,
                'klasifikasi' => $item->klasifikasi,
                'total_nilai' => floatval($item->total_nilai ?? 0),
                'periode_id'  => intval($item->periode_id ?? 0),
                'nama_periode'=> $item->nama_periode ?? '',
            ];
        }, $data);
    }

    /**
     * Get testing data from database
     * 
     * @param int|null $periode_id Optional period filter
     * @return array Formatted testing data
     */
    protected function getDataTestingFromDB(?int $periode_id = null): array
    {
        $testingPeriodeId = $this->resolveTestingPeriodeId($periode_id);
        $data = $this->getKinerjaDataRepository()->getTestingDataset($testingPeriodeId);
        
        return array_map(function($item) {
            $nilai = json_decode($item->nilai_kriteria, true);
            $normalized = $this->normalizeNilaiKriteria($nilai);
            return [
                'id'          => $item->id,
                'karyawan_id' => $item->karyawan_id,
                'nama'        => $item->nama,
                'jabatan'     => $item->jabatan ?? '',
                'nilai'       => $normalized,
                'nilai_kriteria' => $normalized,
                'rata_rata'   => floatval($item->total_nilai),
                'total_nilai' => floatval($item->total_nilai),
                'periode_id'  => intval($item->periode_id ?? 0),
                'klasifikasi_penilaian' => $item->klasifikasi ?? '',
                'nama_periode'=> $item->nama_periode ?? '',
                'nip'         => $item->nomor_pekerja ?? '',
                'nomor_pekerja' => $item->nomor_pekerja ?? '',
                'pekerjaan'   => $item->pekerjaan ?? '',
                'atasan'      => $item->atasan_nama ?? '',
            ];
        }, $data);
    }

    /**
     * Get training statistics
     * 
     * @return array Training data statistics
     */
    protected function getStatistikTraining(): array
    {
        $activePeriodeId = $this->getActivePeriodeId();
        return $this->getKinerjaDataRepository()->getTrainingStatistics($activePeriodeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Formatting Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get badge class for classification
     * 
     * @param string $klasifikasi Classification label
     * @return string Bootstrap badge class
     */
    protected function getKlasifikasiBadge(string $klasifikasi): string
    {
        $map = [
            'Sangat Baik' => 'success',
            'Baik'        => 'primary',
            'Cukup'       => 'warning',
            'Kurang'      => 'danger'
        ];
        
        return $map[$klasifikasi] ?? 'secondary';
    }

    /**
     * Get status label
     * 
     * @param string $status Status code
     * @return string Human-readable status label
     */
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'proses'  => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'gagal'   => 'Gagal',
            'draft'   => 'Draft',
            'aktif'   => 'Aktif',
            'nonaktif' => 'Non-Aktif'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get badge class for status
     * 
     * @param string $status Status code
     * @return string Bootstrap badge class
     */
    protected function getStatusBadge(string $status): string
    {
        $map = [
            'selesai'  => 'success',
            'proses'   => 'info',
            'gagal'    => 'danger',
            'draft'    => 'secondary',
            'aktif'    => 'success',
            'nonaktif' => 'secondary'
        ];
        
        return $map[$status] ?? 'secondary';
    }

    /*
    |--------------------------------------------------------------------------
    | Logging Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Log user activity
     * 
     * @param object $Session User session
     * @param string $module Module name
     * @param string $action Action performed
     * @param int $targetId Target record ID
     * @param string $description Description of activity
     */
    protected function logKnnActivity($Session, string $module, string $action, int $targetId, string $description): void
    {
        $db = $this->getDB();
        
        try {
            $db->run(
                "INSERT INTO log (user_id, module, action, target_id, description, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$Session->id ?? 0, $module, $action, $targetId, $description]
            );
        } catch (\Exception $e) {
            // Log table might not exist, ignore silently
        }
    }

    /**
     * Log calibration action for audit trail
     * 
     * @param object $db Database connection
     * @param object $Session User session
     * @param int $penilaianId Assessment ID
     * @param string|null $oldKlasifikasi Previous classification
     * @param string $newKlasifikasi New classification
     * @param string $catatan Notes
     */
    protected function logCalibration($db, $Session, int $penilaianId, ?string $oldKlasifikasi, string $newKlasifikasi, string $catatan): void
    {
        try {
            $description = "Kalibrasi dari '{$oldKlasifikasi}' ke '{$newKlasifikasi}'";
            if (!empty($catatan)) {
                $description .= ". Catatan: {$catatan}";
            }
            
            $db->run(
                "INSERT INTO log (user_id, module, action, target_id, description, created_at)
                 VALUES (?, 'KlasifikasiKinerja', 'calibrate', ?, ?, NOW())",
                [$Session->id ?? 0, $penilaianId, $description]
            );
        } catch (\Exception $e) {
            // Ignore if log table doesn't exist
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mock Data Methods (for Demo/Testing)
    |--------------------------------------------------------------------------
    */

    /**
     * Get mock training data for demonstration
     * 
     * @return array Mock training data
     */
    protected function getMockTrainingData(): array
    {
        return [
            ['id' => 'T1', 'nama' => 'Ahmad Supardi', 'nilai' => [4,4,3,4,4,4,4,3,4,4,4,4,4], 'klasifikasi' => 'Sangat Baik'],
            ['id' => 'T2', 'nama' => 'Budi Santoso', 'nilai' => [3,4,4,3,4,3,4,4,3,4,4,3,4], 'klasifikasi' => 'Sangat Baik'],
            ['id' => 'T3', 'nama' => 'Citra Dewi', 'nilai' => [3,3,3,3,3,3,3,3,3,3,3,3,3], 'klasifikasi' => 'Baik'],
            ['id' => 'T4', 'nama' => 'Deni Firmansyah', 'nilai' => [3,3,2,3,3,3,3,3,2,3,3,3,3], 'klasifikasi' => 'Baik'],
            ['id' => 'T5', 'nama' => 'Eka Pratama', 'nilai' => [2,2,3,2,3,2,3,2,3,2,3,2,2], 'klasifikasi' => 'Cukup'],
            ['id' => 'T6', 'nama' => 'Fani Wijaya', 'nilai' => [2,2,2,2,2,2,2,2,2,2,2,2,2], 'klasifikasi' => 'Cukup'],
            ['id' => 'T7', 'nama' => 'Gunawan Putra', 'nilai' => [1,2,1,2,1,2,2,2,1,2,2,1,2], 'klasifikasi' => 'Kurang'],
            ['id' => 'T8', 'nama' => 'Hendra Kusuma', 'nilai' => [1,1,2,1,2,1,1,2,2,1,2,1,1], 'klasifikasi' => 'Kurang'],
            ['id' => 'T9', 'nama' => 'Irma Sari', 'nilai' => [4,3,4,4,3,4,4,4,4,3,4,4,3], 'klasifikasi' => 'Sangat Baik'],
            ['id' => 'T10', 'nama' => 'Joko Widodo', 'nilai' => [3,3,3,2,3,3,3,3,3,3,3,3,3], 'klasifikasi' => 'Baik'],
        ];
    }

    /**
     * Get mock testing data for demonstration
     * 
     * @return array Mock testing data
     */
    protected function getMockTestingData(): array
    {
        return [
            ['id' => 1, 'nip' => 'EMP-2024-001', 'nama' => 'Andi Pratama', 'jabatan' => 'Staff IT', 'departemen' => 'IT', 'nilai' => [3,4,3,4,3,3,4,3,3,4,3,3,4]],
            ['id' => 2, 'nip' => 'EMP-2024-002', 'nama' => 'Bella Safitri', 'jabatan' => 'Staff HRD', 'departemen' => 'HRD', 'nilai' => [2,2,3,2,2,2,3,2,2,2,3,2,2]],
            ['id' => 3, 'nip' => 'EMP-2024-003', 'nama' => 'Cahyo Nugroho', 'jabatan' => 'Staff Finance', 'departemen' => 'Finance', 'nilai' => [4,4,4,3,4,4,4,4,3,4,4,4,4]],
            ['id' => 4, 'nip' => 'EMP-2024-004', 'nama' => 'Dina Maulida', 'jabatan' => 'Staff Marketing', 'departemen' => 'Marketing', 'nilai' => [1,2,2,1,2,1,2,1,2,2,2,1,2]],
            ['id' => 5, 'nip' => 'EMP-2024-005', 'nama' => 'Eko Setiawan', 'jabatan' => 'Staff Produksi', 'departemen' => 'Produksi', 'nilai' => [3,3,3,3,3,3,3,3,3,3,3,3,3]],
            ['id' => 6, 'nip' => 'EMP-2024-006', 'nama' => 'Fitri Handayani', 'jabatan' => 'Admin', 'departemen' => 'General Affair', 'nilai' => [4,3,4,4,4,4,4,3,4,4,4,4,4]],
            ['id' => 7, 'nip' => 'EMP-2024-007', 'nama' => 'Gilang Ramadhan', 'jabatan' => 'Staff QC', 'departemen' => 'Quality Control', 'nilai' => [2,3,2,2,3,2,2,3,2,3,2,2,3]],
            ['id' => 8, 'nip' => 'EMP-2024-008', 'nama' => 'Hana Permata', 'jabatan' => 'Staff Legal', 'departemen' => 'Legal', 'nilai' => [3,3,4,3,3,3,4,3,4,3,3,3,3]],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Validate training data has sufficient quality
     * 
     * @return array Validation result with issues
     */
    protected function validateTrainingData(): array
    {
        $db = $this->getDB();
        $labels = $this->getKlasifikasiLabels();
        $issues = [];
        $activePeriodeId = $this->getActivePeriodeId();
        $whereExcludeActive = '';
        $params = [];

        if ($activePeriodeId) {
            $whereExcludeActive = " AND periode_id <> ?";
            $params[] = $activePeriodeId;
        }

        // Check for empty klasifikasi
        $emptySql = "SELECT COUNT(*) as total
                     FROM penilaian
                     WHERE is_training = 1
                       AND status = 'selesai'
                       {$whereExcludeActive}
                       AND (klasifikasi IS NULL OR klasifikasi = '')";
        $emptyKlasifikasi = ($params ? $db->safeQuery($emptySql, $params) : $db->run($emptySql))[0]->total ?? 0;

        if ($emptyKlasifikasi > 0) {
            $issues[] = [
                'type'    => 'warning',
                'message' => "{$emptyKlasifikasi} data training tidak memiliki klasifikasi"
            ];
        }

        // Check for imbalanced classes
        $distSql = "SELECT klasifikasi, COUNT(*) as total
                    FROM penilaian
                    WHERE is_training = 1
                      AND status = 'selesai'
                      {$whereExcludeActive}
                      AND klasifikasi IS NOT NULL
                    GROUP BY klasifikasi";
        $distribusi = $params ? $db->safeQuery($distSql, $params) : $db->run($distSql);

        $totals = [];
        foreach ($distribusi as $d) {
            $totals[$d->klasifikasi] = $d->total;
        }
        
        $avg = count($totals) > 0 ? array_sum($totals) / count($totals) : 0;

        foreach ($labels as $label) {
            $count = $totals[$label] ?? 0;
            if ($count < 3) {
                $issues[] = [
                    'type'    => 'error',
                    'message' => "Klasifikasi '{$label}' hanya memiliki {$count} data (minimal 3)"
                ];
            } elseif ($avg > 0 && $count < $avg * 0.5) {
                $issues[] = [
                    'type'    => 'warning',
                    'message' => "Klasifikasi '{$label}' kurang seimbang ({$count} data)"
                ];
            }
        }

        // Check total training data
        $total = array_sum($totals);
        if ($total < 10) {
            $issues[] = [
                'type'    => 'error',
                'message' => "Total data training ({$total}) terlalu sedikit (minimal 10)"
            ];
        }

        return [
            'issues'         => $issues,
            'distribusi'     => $distribusi,
            'total_training' => $total,
            'is_valid'       => empty(array_filter($issues, fn($i) => $i['type'] === 'error'))
        ];
    }
}
