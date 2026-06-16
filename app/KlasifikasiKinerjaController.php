<?php

namespace app;

use app\Traits\KnnHelperTrait;

/**
 * ============================================================================
 * KlasifikasiKinerjaController
 * ============================================================================
 * Controller untuk mengelola klasifikasi kinerja dan data training KNN.
 * Menyediakan fungsi untuk:
 * - Mengelola data training (tambah, edit, hapus)
 * - Kalibrasi hasil klasifikasi ke data training
 * - Manajemen label klasifikasi
 * 
 * @package    app
 * @author     System
 * @version    3.0.0
 */
class KlasifikasiKinerjaController extends Controller
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
        $this->judul = "Klasifikasi Kinerja & Data Training";
        $this->allowedWhere = ['klasifikasi', 'is_training', 'status'];
        $this->selectFilter = [
            'klasifikasi' => $this->getKlasifikasiLabels(),
            'is_training' => ['Ya' => 1, 'Tidak' => 0]
        ];
        $this->fulltext = '`nama`, `jabatan`';
        $this->mainModel = 'app\Model\Penilaian';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id'];
        $this->exceptExcel = ['nilai_kriteria'];
        $this->tambahan = [
            'klasifikasi_labels' => $this->getKlasifikasiLabels(),
            'threshold'          => $this->getKlasifikasiThreshold()
        ];
        $this->Icon = 'fa fa-tags';
        $this->Link = 'KlasifikasiKinerja';
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
            $item->klasifikasi_badge = $this->getKlasifikasiBadge($item->klasifikasi ?? '');
            $item->is_training_label = ($item->is_training ?? 0) ? 'Ya' : 'Tidak';
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
     * Halaman utama klasifikasi kinerja
     */
    public function index($Request, $Session, $blade)
    {
        if (!$this->getKinerjaService()->hasStoredAnalysis()) {
            return $this->redirectToAnalisaKinerja('File hasil analisa belum tersedia. Silakan lakukan proses analisa terlebih dahulu.');
        }

        $latestAnalysis = $this->getKinerjaService()->getLatestAnalysis(
            !empty($Request->periode_id) ? intval($Request->periode_id) : null
        );

        if (empty($latestAnalysis)) {
            return $this->redirectToAnalisaKinerja('Belum ada hasil analisa untuk periode yang dipilih. Silakan lakukan analisa terlebih dahulu.');
        }

        $data = [
            'judul'              => $this->judul,
            'link'               => $this->Link,
            'icon'               => $this->Icon,
            'kriteria'           => $this->getKriteria(),
            'periodeList'        => $this->getPeriodeList(),
            'klasifikasi_labels' => $this->getKlasifikasiLabels(),
            'threshold'          => $this->getKlasifikasiThreshold(),
            'statistik'          => $latestAnalysis['statistik'] ?? $this->getStatistikTraining(),
            'latestAnalysisId'   => $latestAnalysis['id'] ?? null,
        ];

        return $blade->run('Pages.KlasifikasiKinerja.Index', [
            'data'    => $data,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }

    /**
     * View detail penilaian
     */
    public function View($Request, $Session, $blade)
    {
        if (!$this->getKinerjaService()->hasStoredAnalysis()) {
            return $this->redirectToAnalisaKinerja('File hasil analisa belum tersedia. Silakan lakukan proses analisa terlebih dahulu.');
        }

        $data = [
            'judul'              => 'Detail Penilaian',
            'path'               => 'Pages.KlasifikasiKinerja.View',
            'link'               => 'KlasifikasiKinerja-View',
            'icon'               => $this->Icon,
            'kriteria'           => $this->getKriteria(),
            'klasifikasi_labels' => $this->getKlasifikasiLabels()
        ];

        if (!empty($Request->id)) {
            $penilaian = $this->getPenilaianById($Request->id);
            if (empty($penilaian)) {
                return $blade->run('Pages.Error.404', [
                    'Request' => $Request,
                    'Session' => $Session,
                ]);
            }
            $data['penilaian'] = $penilaian;
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
        $action = $Request->action ?? 'list';
        
        $handlers = [
            // Training Data Management
            'list-training'       => 'getDataTrainingApi',
            'preview'             => 'previewKlasifikasiApi',
            'add-to-training'     => 'addToTrainingApi',
            'remove-from-training'=> 'removeFromTrainingApi',
            'bulk-add-training'   => 'bulkAddTrainingApi',
            
            // Calibration
            'calibrate'           => 'calibrateClassificationApi',
            'auto-calibrate'      => 'autoCalibrateBatchApi',
            
            // Classification Management
            'update-klasifikasi'  => 'updateKlasifikasiApi',
            'suggest-klasifikasi' => 'suggestKlasifikasiApi',
            
            // Statistics & Reports
            'statistik'           => 'getStatistikApi',
            'distribusi'          => 'getDistribusiApi',
            'comparison'          => 'getComparisonApi',
            
            // Validation
            'validate-training'   => 'validateTrainingDataApi',
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
    | Training Data Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get data training API
     */
    private function getDataTrainingApi($Request, $Session)
    {
        $periodeId = $Request->periode_id ?: null;
        $klasifikasi = $Request->klasifikasi ?: null;
        $testingPeriodeId = $this->resolveTestingPeriodeId($periodeId ? intval($periodeId) : null);
        $data = $this->getKinerjaService()->getTrainingDataList($testingPeriodeId, $klasifikasi);

        return [
            'status' => true,
            'data'   => [
                'data'      => $data,
                'total'     => count($data),
                'statistik' => $this->getStatistikByKlasifikasi($data)
            ]
        ];
    }

    private function previewKlasifikasiApi($Request, $Session)
    {
        $periodeId = $this->resolveTestingPeriodeId(!empty($Request->periode_id) ? intval($Request->periode_id) : null);

        if (!$this->getKinerjaService()->hasStoredAnalysis()) {
            return [
                'status' => false,
                'message' => 'File hasil analisa belum tersedia. Silakan lakukan proses analisa terlebih dahulu.',
                'redirect' => 'AnalisaKinerja',
            ];
        }

        $analysis = $this->getKinerjaService()->getLatestAnalysis($periodeId);
        if (!$analysis) {
            return [
                'status' => false,
                'message' => 'Belum ada hasil analisa untuk periode yang dipilih.',
                'redirect' => 'AnalisaKinerja',
            ];
        }

        return [
            'status' => true,
            'data' => $this->getKinerjaService()->previewKlasifikasi(
                $periodeId,
                intval($analysis['params']['k'] ?? 3),
                $analysis['params']['metode_jarak'] ?? 'euclidean'
            )
        ];
    }

    /**
     * Add penilaian to training data
     */
    private function addToTrainingApi($Request, $Session)
    {
        $penilaianId = intval($Request->penilaian_id ?? 0);
        $klasifikasi = $Request->klasifikasi ?? null;

        if (!$penilaianId) {
            return ['status' => false, 'message' => 'ID penilaian diperlukan'];
        }

        $labels = $this->getKlasifikasiLabels();
        if (!$klasifikasi || !in_array($klasifikasi, $labels)) {
            return ['status' => false, 'message' => 'Klasifikasi tidak valid'];
        }

        $this->getKinerjaService()->addToTraining($penilaianId, $klasifikasi, $this->getActivePeriodeId());

        $this->logKnnActivity($Session, 'KlasifikasiKinerja', 'add_training', $penilaianId, 
            "Menambahkan ke data training dengan klasifikasi: {$klasifikasi}");

        return [
            'status'  => true,
            'message' => 'Berhasil ditambahkan ke data training'
        ];
    }

    /**
     * Remove from training data
     */
    private function removeFromTrainingApi($Request, $Session)
    {
        $penilaianId = intval($Request->penilaian_id ?? 0);

        if (!$penilaianId) {
            return ['status' => false, 'message' => 'ID penilaian diperlukan'];
        }

        $this->getKinerjaService()->removeFromTraining($penilaianId);

        $this->logKnnActivity($Session, 'KlasifikasiKinerja', 'remove_training', $penilaianId, 
            "Menghapus dari data training");

        return [
            'status'  => true,
            'message' => 'Berhasil dihapus dari data training'
        ];
    }

    /**
     * Bulk add to training data
     */
    private function bulkAddTrainingApi($Request, $Session)
    {
        $penilaianIds = $Request->penilaian_ids ?? [];
        $klasifikasi = $Request->klasifikasi ?? null;
        $autoClassify = $Request->auto_classify ?? false;

        if (empty($penilaianIds)) {
            return ['status' => false, 'message' => 'Pilih minimal satu penilaian'];
        }

        $summary = $this->getKinerjaService()->bulkAddToTraining(
            $penilaianIds,
            $klasifikasi,
            (bool) $autoClassify,
            $this->getActivePeriodeId()
        );

        return [
            'status'  => true,
            'message' => "Berhasil menambahkan {$summary['success']} data ke training. Gagal: {$summary['failed']}",
            'data'    => $summary
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Calibration Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Calibrate classification result and add to training
     * This allows admin/management to manually correct KNN results
     */
    private function calibrateClassificationApi($Request, $Session)
    {
        $penilaianId = intval($Request->penilaian_id ?? 0);
        $knnKlasifikasi = $Request->knn_klasifikasi ?? null;
        $manualKlasifikasi = $Request->manual_klasifikasi ?? null;
        $catatanKalibrasi = $Request->catatan_kalibrasi ?? '';
        $addToTraining = $Request->add_to_training ?? true;

        if (!$penilaianId) {
            return ['status' => false, 'message' => 'ID penilaian diperlukan'];
        }

        $labels = $this->getKlasifikasiLabels();
        if (!$manualKlasifikasi || !in_array($manualKlasifikasi, $labels)) {
            return ['status' => false, 'message' => 'Klasifikasi manual tidak valid'];
        }

        return [
            'status'  => true,
            'message' => 'Kalibrasi berhasil disimpan' . ($addToTraining ? ' dan ditambahkan ke data training' : ''),
            'data'    => $this->getKinerjaService()->calibrateClassification(
                $penilaianId,
                $knnKlasifikasi,
                $manualKlasifikasi,
                $catatanKalibrasi,
                (bool) $addToTraining,
                $this->getActivePeriodeId(),
                $Session
            )
        ];
    }

    /**
     * Auto-calibrate batch based on threshold rules
     */
    private function autoCalibrateBatchApi($Request, $Session)
    {
        $periodeId = $Request->periode_id ?: null;
        $onlyUnclassified = $Request->only_unclassified ?? true;
        $addToTraining = $Request->add_to_training ?? false;

        $updated = $this->getKinerjaService()->autoCalibrateBatch(
            $periodeId ? intval($periodeId) : null,
            (bool) $onlyUnclassified,
            (bool) $addToTraining,
            $this->getActivePeriodeId()
        );

        return [
            'status'  => true,
            'message' => "Berhasil mengkalibrasi {$updated} data",
            'data'    => ['updated' => $updated]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Classification Management
    |--------------------------------------------------------------------------
    */

    /**
     * Update klasifikasi manual
     */
    private function updateKlasifikasiApi($Request, $Session)
    {
        $penilaianId = intval($Request->penilaian_id ?? 0);
        $klasifikasi = $Request->klasifikasi ?? null;

        if (!$penilaianId) {
            return ['status' => false, 'message' => 'ID penilaian diperlukan'];
        }

        $labels = $this->getKlasifikasiLabels();
        if (!in_array($klasifikasi, $labels)) {
            return ['status' => false, 'message' => 'Klasifikasi tidak valid'];
        }

        $this->getKinerjaService()->updateKlasifikasi($penilaianId, $klasifikasi);

        $this->logKnnActivity($Session, 'KlasifikasiKinerja', 'update_klasifikasi', $penilaianId, 
            "Mengubah klasifikasi menjadi: {$klasifikasi}");

        return [
            'status'  => true,
            'message' => 'Klasifikasi berhasil diperbarui'
        ];
    }

    /**
     * Suggest klasifikasi based on nilai
     */
    private function suggestKlasifikasiApi($Request, $Session)
    {
        $nilai = $Request->nilai ?? [];
        
        if (empty($nilai)) {
            return ['status' => false, 'message' => 'Data nilai diperlukan'];
        }

        $rata = $this->hitungRataRata($nilai);
        $suggested = $this->determineKlasifikasi($rata);

        return [
            'status' => true,
            'data'   => [
                'rata_rata'              => $rata,
                'suggested_klasifikasi'  => $suggested,
                'threshold'              => $this->getKlasifikasiThreshold()
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics & Reports
    |--------------------------------------------------------------------------
    */

    /**
     * Get statistik API
     */
    private function getStatistikApi($Request, $Session)
    {
        return [
            'status' => true,
            'data'   => $this->getStatistikTraining()
        ];
    }

    /**
     * Get distribusi klasifikasi
     */
    private function getDistribusiApi($Request, $Session)
    {
        return [
            'status' => true,
            'data'   => $this->getKinerjaService()->getDistribusi($Request->periode_id ? intval($Request->periode_id) : null)
        ];
    }

    /**
     * Get comparison between KNN result and manual classification
     */
    private function getComparisonApi($Request, $Session)
    {
        $analisaId = trim((string) ($Request->analisa_id ?? ''));

        if ($analisaId === '') {
            return ['status' => false, 'message' => 'ID analisa diperlukan'];
        }

        return [
            'status' => true,
            'data'   => $this->getKinerjaService()->getComparison($analisaId)
        ];
    }

    /**
     * Validate training data quality
     */
    private function validateTrainingDataApi($Request, $Session)
    {
        return [
            'status' => true,
            'data'   => $this->validateTrainingData()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD Override
    |--------------------------------------------------------------------------
    */

    /**
     * Override CRUD untuk handling khusus
     */
    // public function CRUD($Request, $Session, $blade)
    // {
    //     // Handle special calibration action
    //     if ($Request->action === 'calibrate') {
    //         return $this->calibrateClassificationApi($Request, $Session);
    //     }

    //     return parent::CRUD($Request, $Session, $blade);
    // }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get penilaian by ID
     */
    private function getPenilaianById($id)
    {
        return $this->getKinerjaService()->getPenilaianDetail(intval($id));
    }

    private function getKinerjaService(): KinerjaService
    {
        if ($this->kinerjaService === null) {
            $this->kinerjaService = new KinerjaService();
        }

        return $this->kinerjaService;
    }

    private function redirectToAnalisaKinerja(string $message)
    {
        if (isset($GLOBALS['msg'])) {
            $GLOBALS['msg']->warning($message);
        }

        echo "<script>location.href = 'AnalisaKinerja';</script>";
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Export Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Cetak data training
     */
    // public function Cetak($Request, $Session, $blade)
    // {
    //     $periodeId = $Request->periode_id ?: null;

    //     $data = [
    //         'judul'              => 'Laporan Data Training KNN',
    //         'kriteria'           => $this->getKriteria(),
    //         'klasifikasi_labels' => $this->getKlasifikasiLabels(),
    //         'statistik'          => $this->getStatistikTraining()
    //     ];

    //     // Get training data
    //     $db = $this->getDB();

    //     $sql = "SELECT p.*, k.nama, k.jabatan, pp.nama_periode
    //             FROM penilaian p
    //             JOIN karyawan k ON p.karyawan_id = k.id
    //             LEFT JOIN periode_penilaian pp ON p.periode_id = pp.id
    //             WHERE p.is_training = 1";
    //     $params = [];

    //     if ($periodeId) {
    //         $sql .= " AND p.periode_id = ?";
    //         $params[] = $periodeId;
    //     }

    //     $sql .= " ORDER BY p.klasifikasi, k.nama";

    //     $data['training_data'] = $db->run($sql, $params);

    //     return $blade->run('Pages.KlasifikasiKinerja.Cetak', [
    //         'data'    => $data,
    //         'Request' => $Request,
    //         'Session' => $Session,
    //     ]);
    // }
}
