<?php

namespace app;

use \ParagonIE\EasyDB\EasyStatement;

class RekapPenilaianController extends Controller
{
    public function __construct()
    {
        $this->judul = "RekapPenilaian";
        $this->allowedWhere = ['periode_id', 'klasifikasi', 'status'];
        $this->selectFilter = ['periode_id', 'klasifikasi', 'status'];
        $this->fulltext = '`nama`';
        $this->mainModel = 'app\Model\Penilaian';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id'];
        $this->exceptExcel = ['nilai_kriteria'];
        $this->tambahan = [];
        $this->Icon = 'fa fa-file-alt';
        $this->Link = 'RekapPenilaian';
        $this->files = [];
        $this->SortBy = 'id';
        $this->SortWith = 'DESC';
        $this->selectWhere = [];
    }

    /**
     * Set model before data retrieval - add joins for related data
     */
    public function setModelBefore($x)
    {
        $x->Addwith('karyawan', "nama as nama_karyawan, jabatan as jabatan_karyawan, username as nomor_pekerja, pekerjaan", ["karyawan_id", "id"], "app\Model\Karyawan");
        $x->Addwith('penilai', "nama as nama_penilai", ["penilai_id", "id"], "app\Model\Karyawan");
        $x->Addwith('periode', "nama_periode", ["periode_id", "id"], "app\Model\PeriodePenilaian");
        return $x;
    }

    /**
     * Transform data after retrieval
     */
    public function setModelAfter($x)
    {
        $x->data = $x->data->map(function ($item) {
            if (!empty($item->updated_at)) {
                $item->tanggal_penilaian = date('d/m/Y H:i', strtotime($item->updated_at));
            } else {
                $item->tanggal_penilaian = '-';
            }

            if (!empty($item->nilai_kriteria) && is_string($item->nilai_kriteria)) {
                $item->nilai_kriteria_decoded = json_decode($item->nilai_kriteria, true);
            }

            if (empty($item->klasifikasi)) {
                $item->klasifikasi = $this->hitungKlasifikasi($item->total_nilai);
            }

            return $item;
        });

        if ($x->data->isEmpty()) {
            return $x;
        }

        $knnMap = (new KinerjaService())->getLatestKnnLookup();

        $x->data = $x->data->map(function ($item) use ($knnMap) {
            $item->nama = $item->karyawan->first()->nama_karyawan ?? $item->nama ?? null;
            $item->jabatan = $item->karyawan->first()->jabatan_karyawan ?? $item->jabatan ?? null;
            $item->nomor_pekerja = $item->karyawan->first()->nomor_pekerja ?? $item->nomor_pekerja ?? null;
            $item->pekerjaan = $item->karyawan->first()->pekerjaan ?? $item->pekerjaan ?? null;
            $item->periode = $item->periode->first()->nama_periode ?? $item->periode ?? null;
            $item->nama_penilai = $item->penilai->first()->nama_penilai ?? $item->nama_penilai ?? null;

            $key = ($item->karyawan_id ?? '') . '-' . ($item->periode_id ?? '');
            $knn = $knnMap[$key] ?? null;
            $item->klasifikasi_knn = $knn['hasil_klasifikasi'] ?? null;
            $item->knn_confidence = isset($knn['confidence']) ? floatval($knn['confidence']) : null;

            return $item;
        });

        return $x;
    }

    /**
     * Calculate classification based on total score
     */
    private function hitungKlasifikasi($totalNilai)
    {
        if ($totalNilai >= 90) return 'Sangat Baik';
        if ($totalNilai >= 80) return 'Baik';
        if ($totalNilai >= 70) return 'Cukup';
        if ($totalNilai >= 60) return 'Kurang';
        return 'Sangat Kurang';
    }

    /**
     * Transform data for Excel export
     */
    public function setModelForExcel($x)
    {
        $x = $x->map(function ($item) {
            // Remove complex JSON field for Excel
            unset($item->nilai_kriteria);
            return $item;
        });
        return $x;
    }

    /**
     * Get list of periods for filter
     */
    public function getPeriodeList()
    {
        return $this->getDB()->run("SELECT id, nama_periode FROM periode_penilaian ORDER BY tanggal_mulai DESC");
    }

    /**
     * Get all active kriteria
     */
    public function getKriteriaList()
    {
        return $this->getDB()->run("SELECT id, kode, nama, nama_en, bobot, urutan FROM kriteria WHERE status = 'aktif' ORDER BY urutan ASC");
    }

    /**
     * View detail penilaian
     */
    public function View($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Detail Rekap Penilaian',
            'path' => 'Pages.RekapPenilaian.View',
            'link' => 'RekapPenilaian-View',
            'icon' => 'fa fa-file-alt',
            'penilaian_id' => $Request->id ?? null,
            'penilaian' => null,
            'kriteria' => [],
            'detail_nilai' => [],
            'knn_hasil' => null,
            'knn_detail' => []
        ];

        if (!empty($Request->id)) {
            $knnLookup = (new KinerjaService())->getLatestKnnLookup();
            $knnKey = null;
            $penilaian = $this->getDB()->row(
                "SELECT 
                    p.id,
                    p.periode_id,
                    p.karyawan_id,
                    p.penilai_id,
                    p.nilai_kriteria,
                    p.total_nilai,
                    p.klasifikasi,
                    p.catatan,
                    p.status,
                    p.is_training,
                    p.created_at,
                    p.updated_at,
                    k.nama as nama_karyawan,
                    k.jabatan as jabatan_karyawan,
                    k.username as nomor_pekerja,
                    k.pekerjaan,
                    k.status as status_karyawan,
                    k.tanggal_bergabung,
                    pp.nama_periode,
                    pp.tanggal_mulai,
                    pp.tanggal_selesai,
                    pen.nama as nama_penilai,
                    pen.jabatan as jabatan_penilai
                FROM penilaian p
                LEFT JOIN karyawan k ON p.karyawan_id = k.id
                LEFT JOIN periode_penilaian pp ON p.periode_id = pp.id
                LEFT JOIN karyawan pen ON p.penilai_id = pen.id
                WHERE p.id = ?",
                $Request->id
            );

            if ($penilaian) {
                $knnKey = ($penilaian->karyawan_id ?? '') . '-' . ($penilaian->periode_id ?? '');
                $knnRow = $knnLookup[$knnKey] ?? [];
                $penilaian->klasifikasi_knn = $knnRow['hasil_klasifikasi'] ?? null;
                $penilaian->knn_confidence = $knnRow['confidence'] ?? null;
                $data['penilaian'] = $penilaian;
                $kriteria = $this->getKriteriaList();
                $data['kriteria'] = $kriteria;
                $nilaiKriteria = [];
                if (!empty($penilaian->nilai_kriteria)) {
                    $nilaiKriteria = json_decode($penilaian->nilai_kriteria, true) ?? [];
                }
                $detailNilai = [];
                foreach ($kriteria as $kr) {
                    $nilai = 0;
                    foreach ($nilaiKriteria as $nk) {
                        if (isset($nk['kriteria_id']) && $nk['kriteria_id'] == $kr->id) {
                            $nilai = floatval($nk['nilai'] ?? 0);
                            break;
                        }
                    }
                    
                    $nilaiBobot = round(($nilai * $kr->bobot) / 100, 2);
                    
                    $detailNilai[] = [
                        'kriteria_id' => $kr->id,
                        'kode' => $kr->kode,
                        'nama' => $kr->nama,
                        'nama_en' => $kr->nama_en,
                        'bobot' => floatval($kr->bobot),
                        'nilai' => $nilai,
                        'nilai_bobot' => $nilaiBobot
                    ];
                }
                $data['detail_nilai'] = $detailNilai;
                if (!empty($knnRow['result_id'])) {
                    $payload = (new KinerjaService())->getDetailPerhitunganPayload($knnRow['result_id']);
                    $data['knn_hasil'] = (object) [
                        'id' => $knnRow['result_id'],
                        'analisa_id' => $knnRow['analysis_id'] ?? null,
                        'hasil_klasifikasi' => $knnRow['hasil_klasifikasi'] ?? null,
                        'confidence' => $knnRow['confidence'] ?? null,
                    ];
                    $data['knn_detail'] = array_map(function ($item) {
                        return (object) $item;
                    }, array_values(array_filter($payload['detail_jarak'] ?? [], function ($item) {
                        return intval($item['is_k_nearest'] ?? 0) === 1;
                    })));
                }
            } else {
                return $blade->run('Pages.Error.404', [
                    'Request' => $Request,
                    'Session' => $Session,
                ]);
            }
        }

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }

    /**
     * Get detail penilaian via API
     */
    public function DetailApi($Request, $Session, $blade)
    {
        $id = $Request->id ?? null;
        
        if (empty($id)) {
            echo json_encode(['status' => false, 'message' => 'ID tidak valid']);
            return;
        }

        $knnLookup = (new KinerjaService())->getLatestKnnLookup();
        $penilaianRows = $this->getDB()->run(
            "SELECT 
                p.*,
                k.nama as nama_karyawan,
                k.jabatan as jabatan_karyawan,
                k.username as nomor_pekerja,
                k.pekerjaan,
                pp.nama_periode,
                pen.nama as nama_penilai
            FROM penilaian p
            LEFT JOIN karyawan k ON p.karyawan_id = k.id
            LEFT JOIN periode_penilaian pp ON p.periode_id = pp.id
            LEFT JOIN karyawan pen ON p.penilai_id = pen.id
            WHERE p.id = ?",
            $id
        );
        $penilaian = $penilaianRows[0] ?? null;

        if (!$penilaian) {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $key = ($penilaian->karyawan_id ?? '') . '-' . ($penilaian->periode_id ?? '');
        $knnRow = $knnLookup[$key] ?? [];
        $kriteria = $this->getKriteriaList();
        $nilaiKriteria = json_decode($penilaian->nilai_kriteria, true) ?? [];
        
        $detailKriteria = [];
        foreach ($kriteria as $kr) {
            $nilai = 0;
            foreach ($nilaiKriteria as $nk) {
                if (isset($nk['kriteria_id']) && $nk['kriteria_id'] == $kr->id) {
                    $nilai = floatval($nk['nilai'] ?? 0);
                    break;
                }
            }
            
            $detailKriteria[] = [
                'kode' => $kr->kode,
                'nama_kriteria' => $kr->nama,
                'bobot' => floatval($kr->bobot),
                'nilai' => $nilai,
                'nilai_bobot' => round(($nilai * $kr->bobot) / 100, 2)
            ];
        }

        $result = [
            'id' => $penilaian->id,
            'nama_karyawan' => $penilaian->nama_karyawan,
            'jabatan' => $penilaian->jabatan_karyawan,
            'nomor_pekerja' => $penilaian->nomor_pekerja,
            'pekerjaan' => $penilaian->pekerjaan,
            'periode' => $penilaian->nama_periode,
            'total_nilai' => $penilaian->total_nilai,
            'klasifikasi' => $penilaian->klasifikasi ?? $this->hitungKlasifikasi($penilaian->total_nilai),
            'klasifikasi_knn' => $knnRow['hasil_klasifikasi'] ?? null,
            'knn_confidence' => $knnRow['confidence'] ?? null,
            'nama_penilai' => $penilaian->nama_penilai,
            'tanggal_penilaian' => date('d/m/Y H:i', strtotime($penilaian->updated_at)),
            'catatan' => $penilaian->catatan,
            'detail_kriteria' => $detailKriteria
        ];

        echo json_encode(['status' => true, 'data' => $result]);
    }

    /**
     * Get statistics summary
     */
    public function StatistikApi($Request, $Session, $blade)
    {
        $periodeId = $Request->periode_id ?? null;
        
        $whereClause = "WHERE p.status = 'selesai'";
        $params = [];
        
        if (!empty($periodeId)) {
            $whereClause .= " AND p.periode_id = ?";
            $params[] = $periodeId;
        }

        // Get statistics by classification
        $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN klasifikasi = 'Sangat Baik' THEN 1 ELSE 0 END) as sangat_baik,
            SUM(CASE WHEN klasifikasi = 'Baik' THEN 1 ELSE 0 END) as baik,
            SUM(CASE WHEN klasifikasi = 'Cukup' THEN 1 ELSE 0 END) as cukup,
            SUM(CASE WHEN klasifikasi = 'Kurang' THEN 1 ELSE 0 END) as kurang,
            SUM(CASE WHEN klasifikasi = 'Sangat Kurang' THEN 1 ELSE 0 END) as sangat_kurang,
            AVG(total_nilai) as rata_rata,
            MAX(total_nilai) as tertinggi,
            MIN(total_nilai) as terendah
        FROM penilaian p
        $whereClause";

        $stats = empty($params) 
            ? $this->getDB()->row($sql) 
            : $this->getDB()->row($sql, ...$params);

        echo json_encode(['status' => true, 'data' => $stats]);
    }

    /**
     * Print/Cetak rekap penilaian
     */
    public function Cetak($data, $blade)
    {
        $data2 = [
            'judul' => 'Rekap Penilaian Kinerja',
            'path' => "Pages.RekapPenilaian.Cetak",
            'icon' => 'fas fa-fw fa-home',
            'tglExport' => date('d/m/Y H:i:s')
        ];
        
        // If specific ID is provided, print single detail
        if (!empty($_GET['id'])) {
            $penilaian = $this->getDB()->row(
                "SELECT 
                    p.*,
                    k.nama as nama_karyawan,
                    k.jabatan as jabatan_karyawan,
                    k.pekerjaan,
                    pp.nama_periode,
                    pen.nama as nama_penilai,
                    pen.jabatan as jabatan_penilai
                FROM penilaian p
                LEFT JOIN karyawan k ON p.karyawan_id = k.id
                LEFT JOIN periode_penilaian pp ON p.periode_id = pp.id
                LEFT JOIN karyawan pen ON p.penilai_id = pen.id
                WHERE p.id = ?",
                $_GET['id']
            );
            
            if ($penilaian) {
                $kriteria = $this->getKriteriaList();
                $nilaiKriteria = json_decode($penilaian->nilai_kriteria, true) ?? [];
                
                $detailNilai = [];
                foreach ($kriteria as $kr) {
                    $nilai = 0;
                    foreach ($nilaiKriteria as $nk) {
                        if (isset($nk['kriteria_id']) && $nk['kriteria_id'] == $kr->id) {
                            $nilai = floatval($nk['nilai'] ?? 0);
                            break;
                        }
                    }
                    
                    $detailNilai[] = [
                        'kode' => $kr->kode,
                        'nama' => $kr->nama,
                        'bobot' => floatval($kr->bobot),
                        'nilai' => $nilai,
                        'nilai_bobot' => round(($nilai * $kr->bobot) / 100, 2)
                    ];
                }
                
                $data2['penilaian'] = $penilaian;
                $data2['detail_nilai'] = $detailNilai;
                $data2['mode'] = 'single';
            }
        }
        
        $data = array_merge($data, $data2);
        
        echo $blade->run($data['path'], [
            'data' => $data,
        ]);
    }

    /**
     * Customize filter display
     */
    public function setFilter($filter)
    {
        // Add periode options
        $periodeList = $this->getPeriodeList();
        foreach ($filter as $key => $value) {
            if ($value->name === 'periode_id') {
                $filter[$key]->isi = collect($periodeList)->map(function ($item) {
                    return ['key' => $item->id, 'label' => $item->nama_periode];
                })->toArray();
                $filter[$key]->label = 'Periode';
            }
            if ($value->name === 'klasifikasi') {
                $filter[$key]->isi = [
                    ['key' => 'Sangat Baik', 'label' => 'Sangat Baik'],
                    ['key' => 'Baik', 'label' => 'Baik'],
                    ['key' => 'Cukup', 'label' => 'Cukup'],
                    ['key' => 'Kurang', 'label' => 'Kurang'],
                    ['key' => 'Sangat Kurang', 'label' => 'Sangat Kurang'],
                ];
                $filter[$key]->label = 'Klasifikasi';
            }
            if ($value->name === 'status') {
                $filter[$key]->isi = [
                    ['key' => 'draft', 'label' => 'Draft'],
                    ['key' => 'proses', 'label' => 'Proses'],
                    ['key' => 'selesai', 'label' => 'Selesai'],
                    ['key' => 'batal', 'label' => 'Batal'],
                ];
                $filter[$key]->label = 'Status';
            }
        }
        return $filter;
    }
}
