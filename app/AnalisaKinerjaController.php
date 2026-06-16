<?php

namespace app;

use app\Traits\KnnHelperTrait;

/**
 * ============================================================================
 * AnalisaKinerjaController
 * ============================================================================
 * Controller untuk mengelola analisa kinerja karyawan menggunakan algoritma
 * K-Nearest Neighbor (KNN). Menyediakan fungsi untuk menjalankan analisa,
 * menyimpan hasil, dan mengambil detail perhitungan.
 * 
 * @package    app
 * @author     System
 * @version    3.0.0
 */
class AnalisaKinerjaController extends Controller
{
    use KnnHelperTrait;

    protected $kinerjaService = null;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->judul = "Analisa Kinerja KNN";
        $this->allowedWhere = ['created_at', 'status'];
        $this->selectFilter = [];
        $this->fulltext = '`kode_analisa`, `nama_analisa`';
        $this->mainModel = 'app\Model\Penilaian';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id'];
        $this->exceptExcel = [];
        $this->tambahan = [];
        $this->Icon = 'fa fa-brain';
        $this->Link = 'AnalisaKinerja';
        $this->files = [];
        $this->SortBy = 'created_at';
        $this->SortOrder = 'DESC';
        $this->selectWhere = [];
    }

    /*
    |--------------------------------------------------------------------------
    | Model Hooks
    |--------------------------------------------------------------------------
    */

    public function setModelBefore($x)
    {
        return $x;
    }

    public function setModelAfter($x)
    {
        $x->data = $x->data->map(function ($item) {
            $item->status_label = $this->getStatusLabel($item->status ?? 'proses');
            $item->status_badge = $this->getStatusBadge($item->status ?? 'proses');
            return $item;
        });
        return $x;
    }

    public function setModelForExcel($x)
    {
        return $x->map(fn($item) => $item);
    }

    /*
    |--------------------------------------------------------------------------
    | Main Page Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Halaman utama analisa kinerja
     */
    public function index($Request, $Session, $blade)
    {
        $data = [
            'judul'            => $this->judul,
            'link'             => $this->Link,
            'icon'             => $this->Icon,
            'kriteria'         => $this->getKriteria(),
            'periodeList'      => $this->getPeriodeList(),
            'klasifikasiLabels'=> $this->getKlasifikasiLabels(),
            'statistikTraining'=> $this->getStatistikTraining(),
            'hasStoredAnalysis'=> $this->getKinerjaService()->hasStoredAnalysis(),
        ];

        return $blade->run('Pages.AnalisaKinerja.Index', [
            'data'    => $data,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }

    /**
     * View detail analisa
     */
    public function View($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Detail Analisa KNN',
            'path'  => 'Pages.AnalisaKinerja.View',
            'link'  => 'AnalisaKinerja-View',
            'icon'  => $this->Icon,
        ];

        if (!empty($Request->id)) {
            $analisa = $this->getAnalisaById($Request->id);
            if (empty($analisa)) {
                return $blade->run('Pages.Error.404', [
                    'Request' => $Request,
                    'Session' => $Session,
                ]);
            }
            $data['analisa'] = $analisa;
            $data['hasil_klasifikasi'] = $this->getHasilKlasifikasi($Request->id);
            $data['statistik'] = $this->getStatistikAnalisa($Request->id);
            $data['kriteria'] = $this->getKriteria();
        }

        return $blade->run($data['path'], [
            'data'    => $data,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Endpoint utama untuk berbagai action
     */
    public function indexApi($Request, $Session, $blade)
    {
        $action = $Request->action ?? 'daftar';
        
        $handlers = [
            'proses'      => 'prosesAnalisa',
            'proses-mock' => 'prosesAnalisaMock',
            'preview'     => 'previewAnalisa',
            'list'        => 'getDaftarAnalisaApi',
            'hasil'       => 'getHasilAnalisaApi',
            'detail'      => 'getDetailPerhitunganApi',
            'daftar'      => 'getDaftarAnalisaApi',
            'riwayat-penilaian' => 'getRiwayatPenilaianApi',
            'hapus'       => 'hapusAnalisaApi',
            'statistik'   => 'getStatistikApi',
        ];

        if (isset($handlers[$action])) {
            try {
                $result = $this->{$handlers[$action]}($Request, $Session);
            } catch (\ParagonIE\EasyDB\Exception\MustBeOneDimensionalArray $e) {
                $result = ['status' => false, 'message' => 'Parameter database tidak valid: ' . $e->getMessage()];
            } catch (\Throwable $e) {
                $result = ['status' => false, 'message' => $e->getMessage()];
            }
            echo json_encode($result);
            return;
        }

        return parent::indexApi($Request, $Session, $blade);
    }

    /*
    |--------------------------------------------------------------------------
    | KNN Analysis Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Proses analisa KNN dengan mock data (untuk demo)
     */
    private function prosesAnalisaMock($Request, $Session)
    {
        $nilaiK = intval($Request->nilai_k ?? 3);
        $metodeJarak = $Request->metode_jarak ?? 'euclidean';
        $normalisasi = $Request->normalisasi ?? 'min_max';

        // Get kriteria dan bobot
        $kriteria = $this->getKriteria();
        $bobot = array_column($kriteria, 'bobot');

        // Get mock data
        $dataTraining = $this->getMockTrainingData();
        $dataKaryawan = $this->getMockTestingData();

        // Process KNN classification
        $hasil = [];
        $statistik = $this->initializeStatistik();

        foreach ($dataKaryawan as $karyawan) {
            $result = $this->klasifikasiKNN($karyawan, $dataTraining, $nilaiK, $bobot, $metodeJarak);
            $hasil[] = $result;
            $this->updateStatistik($statistik, $result['hasil_klasifikasi']);
        }

        return [
            'status'  => true,
            'message' => 'Analisa KNN berhasil diproses',
            'data'    => [
                'kriteria'      => $kriteria,
                'data_training' => $dataTraining,
                'hasil'         => $hasil,
                'statistik'     => $statistik,
                'params'        => [
                    'k'            => $nilaiK,
                    'metode_jarak' => $metodeJarak,
                    'normalisasi'  => $normalisasi
                ]
            ]
        ];
    }

    /**
     * Preview analisa KNN dengan data real tanpa menyimpan ke database
     */
    private function previewAnalisa($Request, $Session)
    {
        $nilaiK = intval($Request->nilai_k ?? 3);
        $metodeJarak = $Request->metode_jarak ?? 'euclidean';
        $normalisasi = $Request->normalisasi ?? 'min_max';
        $periodeId = $this->resolveTestingPeriodeId(!empty($Request->periode_id) ? intval($Request->periode_id) : null);

        $kriteria = $this->getKriteria();
        $bobot = array_column($kriteria, 'bobot');
        $dataTraining = $this->getDataTrainingFromDB($periodeId);
        $dataKaryawan = $this->getDataTestingFromDB($periodeId);

        if (count($dataTraining) < $nilaiK) {
            return [
                'status' => false,
                'message' => "Data training tidak cukup. Minimal {$nilaiK} data diperlukan."
            ];
        }

        $hasil = [];
        $statistik = $this->initializeStatistik();

        foreach ($dataKaryawan as $karyawan) {
            $result = $this->klasifikasiKNN($karyawan, $dataTraining, $nilaiK, $bobot, $metodeJarak);
            $hasil[] = $result;
            $this->updateStatistik($statistik, $result['hasil_klasifikasi']);
        }

        return [
            'status' => true,
            'message' => 'Preview analisa berhasil diproses',
            'data' => [
                'kriteria' => $kriteria,
                'data_training' => $dataTraining,
                'hasil' => $hasil,
                'statistik' => $statistik,
                'params' => [
                    'k' => $nilaiK,
                    'metode_jarak' => $metodeJarak,
                    'normalisasi' => $normalisasi,
                    'periode_id' => $periodeId
                ]
            ]
        ];
    }

    /**
     * Proses analisa KNN dan simpan ke database
     */
    private function prosesAnalisa($Request, $Session)
    {
        try {
            $params = $this->extractAnalysisParams($Request, $Session);
            $kriteria = $this->getKriteria();
            $bobot = array_column($kriteria, 'bobot');
            $dataTraining = $this->getDataTrainingFromDB($params['periode_id']);
            $dataKaryawan = $this->getDataTestingFromDB($params['periode_id']);

            if (count($dataTraining) < $params['nilai_k']) {
                return [
                    'status'  => false,
                    'message' => "Data training tidak cukup. Minimal {$params['nilai_k']} data diperlukan."
                ];
            }

            $waktuMulai = microtime(true);
            $hasil = [];
            $statistik = $this->initializeStatistik();

            foreach (array_values($dataKaryawan) as $index => $karyawan) {
                $result = $this->klasifikasiKNN($karyawan, $dataTraining, $params['nilai_k'], $bobot, $params['metode_jarak']);
                $result['result_id'] = sprintf('%s-R%03d', $params['analysis_id'], $index + 1);
                $result['rata_rata_nilai'] = $this->hitungRataRata($karyawan['nilai'] ?? []);
                $hasil[] = $result;
                $this->updateStatistik($statistik, $result['hasil_klasifikasi']);
            }

            $waktuProses = microtime(true) - $waktuMulai;
            $analysis = $this->getKinerjaService()->persistAnalysis([
                'id' => $params['analysis_id'],
                'kode_analisa' => $params['kode_analisa'],
                'nama_analisa' => $params['nama_analisa'],
                'params' => [
                    'k' => $params['nilai_k'],
                    'metode_jarak' => $params['metode_jarak'],
                    'normalisasi' => $params['normalisasi'],
                    'periode_id' => $params['periode_id'],
                ],
                'dibuat_oleh' => $params['dibuat_oleh'],
                'dibuat_oleh_nama' => $Session['admin']->nama ?? null,
                'kriteria' => $kriteria,
                'data_training' => $dataTraining,
                'hasil' => $hasil,
                'statistik' => $statistik,
                'waktu_proses' => round($waktuProses, 4),
            ]);

            return [
                'status'  => true,
                'message' => 'Analisa KNN berhasil disimpan ke file JSON',
                'data'    => [
                    'analisa_id'   => $analysis['id'],
                    'kode_analisa' => $params['kode_analisa'],
                    'hasil'        => $hasil,
                    'statistik'    => $statistik,
                    'waktu_proses' => round($waktuProses, 4)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Gagal memproses analisa: ' . $e->getMessage()
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Data Retrieval API Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get daftar analisa API
     */
    private function getDaftarAnalisaApi($Request, $Session)
    {
        $limit = intval($Request->limit ?? 20);
        $offset = intval($Request->offset ?? 0);
        $periodeMap = [];

        foreach ($this->getPeriodeList() as $periode) {
            $periodeMap[intval($periode->id ?? 0)] = $periode->nama_periode ?? ('Periode #' . ($periode->id ?? ''));
        }

        $result = $this->getKinerjaService()->getDaftarAnalisa($limit, $offset);
        $result['data'] = array_map(function ($item) use ($periodeMap) {
            $periodeId = intval($item['periode_id'] ?? 0);
            $item['periode_nama'] = $periodeMap[$periodeId] ?? ($periodeId > 0 ? ('Periode #' . $periodeId) : 'Periode Aktif');
            return $item;
        }, $result['data'] ?? []);

        return [
            'status' => true,
            'data'   => $result
        ];
    }

    /**
     * Get hasil analisa API
     */
    private function getHasilAnalisaApi($Request, $Session)
    {
        $id = trim((string) ($Request->id ?? ''));
        if ($id === '') {
            return ['status' => false, 'message' => 'ID analisa diperlukan'];
        }

        return [
            'status' => true,
            'data'   => $this->getKinerjaService()->getHasilAnalisaPayload($id)
        ];
    }

    /**
     * Get detail perhitungan API
     */
    private function getDetailPerhitunganApi($Request, $Session)
    {
        $id = trim((string) ($Request->id ?? ''));
        if ($id === '') {
            return ['status' => false, 'message' => 'ID hasil klasifikasi diperlukan'];
        }

        return [
            'status' => true,
            'data'   => $this->getKinerjaService()->getDetailPerhitunganPayload($id)
        ];
    }

    /**
     * Hapus analisa API
     */
    private function hapusAnalisaApi($Request, $Session)
    {
        $id = trim((string) ($Request->id ?? ''));
        if ($id === '') {
            return ['status' => false, 'message' => 'ID analisa diperlukan'];
        }

        if (!$this->getKinerjaService()->deleteAnalysis((string) $id)) {
            return ['status' => false, 'message' => 'Analisa tidak ditemukan'];
        }

        $this->logKnnActivity($Session, 'AnalisaKinerja', 'delete', 0, 'Menghapus analisa KNN: ' . $id);

        return ['status' => true, 'message' => 'Analisa berhasil dihapus'];
    }

    /**
     * Get statistik API
     */
    private function getStatistikApi($Request, $Session)
    {
        return [
            'status' => true,
            'data'   => $this->getKinerjaService()->getStatistikAnalisaSummary()
        ];
    }

    private function getRiwayatPenilaianApi($Request, $Session)
    {
        $db = $this->getDB();
        $periodeId = $this->resolveTestingPeriodeId(!empty($Request->periode_id) ? intval($Request->periode_id) : null);
        $knnLookup = $this->getKinerjaService()->getLatestKnnLookup($periodeId);

        $sql = "SELECT p.id, p.karyawan_id, p.periode_id, p.total_nilai, p.klasifikasi, p.is_training, p.updated_at,
                       k.nama, k.jabatan, k.pekerjaan, k.username,
                       pp.nama_periode, pen.nama as nama_penilai
                FROM penilaian p
                JOIN karyawan k ON k.id = p.karyawan_id
                LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
                LEFT JOIN karyawan pen ON pen.id = p.penilai_id
                WHERE p.status = 'selesai'";
        $params = [];

        if ($periodeId) {
            $sql .= " AND p.periode_id = ?";
            $params[] = $periodeId;
        }

        $sql .= " ORDER BY p.updated_at DESC, k.nama ASC";
        $rows = (empty($params) ? $db->run($sql) : $db->safeQuery($sql, $params));
        $rows = array_map(function ($item) use ($knnLookup) {
            $key = intval($item->karyawan_id ?? 0) . '-' . intval($item->periode_id ?? 0);
            $knn = $knnLookup[$key] ?? [];
            $item->klasifikasi_knn = $knn['hasil_klasifikasi'] ?? null;
            $item->knn_confidence = $knn['confidence'] ?? null;
            return $item;
        }, $rows);

        return [
            'status' => true,
            'data' => [
                'data' => $rows
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Initialize statistics counters
     */
    private function initializeStatistik()
    {
        return [
            'total_sangat_baik' => 0,
            'total_baik'        => 0,
            'total_cukup'       => 0,
            'total_kurang'      => 0
        ];
    }

    /**
     * Extract analysis parameters from request
     */
    private function extractAnalysisParams($Request, $Session)
    {
        $periodeId = $this->resolveTestingPeriodeId($Request->periode_id ? intval($Request->periode_id) : null);

        return [
            'nama_analisa' => $Request->nama_analisa ?? 'Analisa KNN ' . date('Y-m-d H:i:s'),
            'nilai_k'      => intval($Request->nilai_k ?? 3),
            'metode_jarak' => $Request->metode_jarak ?? 'euclidean',
            'normalisasi'  => $Request->normalisasi ?? 'min_max',
            'periode_id'   => $periodeId,
            'dibuat_oleh'  => $Session['admin']->id ?? null,
            'kode_analisa' => 'KNN-' . date('YmdHis') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            'analysis_id'  => 'ANL-' . date('YmdHis') . '-' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Get analisa by ID
     */
    private function getAnalisaById($id)
    {
        return $this->getKinerjaService()->getAnalisaById((string) $id);
    }

    /**
     * Get hasil klasifikasi for analisa
     */
    private function getHasilKlasifikasi($analisaId)
    {
        return $this->getKinerjaService()->getHasilKlasifikasiByAnalisa((string) $analisaId);
    }

    /**
     * Get statistik analisa
     */
    private function getStatistikAnalisa($analisaId)
    {
        return $this->getKinerjaService()->getAnalisaStatistikById((string) $analisaId);
    }

    private function getKinerjaService(): KinerjaService
    {
        if ($this->kinerjaService === null) {
            $this->kinerjaService = new KinerjaService();
        }

        return $this->kinerjaService;
    }

    /*
    |--------------------------------------------------------------------------
    | Export Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Cetak hasil analisa
     */
    public function CetakPage($Request, $Session, $blade)
    {
        $id = trim((string) ($Request->id ?? ''));
        
        $data = [
            'judul'             => 'Laporan Hasil Analisa KNN',
            'analisa'           => null,
            'hasil_klasifikasi' => [],
            'statistik'         => null,
            'kriteria'          => $this->getKriteria()
        ];

        if ($id !== '') {
            $data['analisa'] = $this->getAnalisaById($id);
            $data['hasil_klasifikasi'] = $this->getHasilKlasifikasi($id);
            $data['statistik'] = $this->getStatistikAnalisa($id);
        }

        return $blade->run('Pages.AnalisaKinerja.Cetak', [
            'data'    => $data,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }
}
