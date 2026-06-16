<?php
/**
 * ============================================================================
 * Application Routes Configuration
 * ============================================================================
 * 
 * This file defines all application routes and their access permissions.
 * Each route maps a URL endpoint to a controller method with access control.
 * 
 * Route Structure:
 * - 'class'  : Controller class namespace
 * - '@'      : Method to execute
 * - 'akses'  : Array of roles/IDs allowed, or null for public access
 * 
 * Access Levels:
 * - null        : Public access (no authentication required)
 * - [1]         : Admin only (role ID 1)
 * - ['Admin', 'Atasan', ...] : Multiple roles allowed
 * 
 * @package    KinerjaKNN
 * @version    2.0.0
 */

$route = [

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */
    
    'Login' => [
        'class' => "app\Master",
        '@' => 'Login',
        'akses' => null,
    ],
    'ProsesLogin' => [
        'class' => "app\Master",
        '@' => 'ProsesLogin',
        'akses' => null,
    ],
    'Logout' => [
        'class' => "app\Master",
        '@' => 'Logout',
        'akses' => null,
    ],
    'Daftar' => [
        'class' => "app\Master",
        '@' => 'Daftar',
        'akses' => null,
    ],
    'ResetPass' => [
        'class' => "app\Master",
        '@' => 'ResetPass',
        'akses' => null,
    ],
    'TokenReset' => [
        'class' => "app\Master",
        '@' => 'TokenReset',
        'akses' => null,
    ],
    'BerhasilDaftar' => [
        'class' => "app\Master",
        '@' => 'BerhasilDaftar',
        'akses' => null
    ],
    'Layout' => [
        'class' => "app\Master",
        '@' => 'Layout',
        'akses' => null,
    ],
    'SearchBank' => [
        'class' => "app\Master",
        '@' => 'SearchBank',
        'akses' => null,
    ],
    'Scan' => [
        'class' => "app\Master",
        '@' => 'Scan',
        'akses' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    
    'Dashboard' => [
        'class' => "app\Master",
        '@' => 'Dashboard',
        'akses' => []
    ],
    'Dashboard/admin' => [
        'class' => "app\Master",
        '@' => 'DashboardAdmin',
        'akses' => ['admin']
    ],
    'Dashboard/atasan' => [
        'class' => "app\Master",
        '@' => 'DashboardSupervisor',
        'akses' => ['atasan']
    ],
    'Dashboard/karyawan' => [
        'class' => "app\Master",
        '@' => 'DashboardKaryawan',
        'akses' => ['karyawan']
    ],
    'Dashboard/pimpinan' => [
        'class' => "app\Master",
        '@' => 'DashboardManagement',
        'akses' => ['pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */
    
    'Users' => [
        'class' => "app\UsersController",
        '@' => 'index',
        'akses' => [1]
    ],
    'Users-View' => [
        'class' => "app\UsersController",
        '@' => 'View',
        'akses' => [1]
    ],
    'Users-Api' => [
        'class' => "app\UsersController",
        '@' => 'indexApi',
        'akses' => [1]
    ],
    'Users-CRUD' => [
        'class' => "app\UsersController",
        '@' => 'CRUD',
        'akses' => [1]
    ],
    'Users-XLSX' => [
        'class' => "app\UsersController",
        '@' => 'exportXLSX',
        'akses' => [1]
    ],
    'Users-Cetak' => [
        'class' => "app\UsersController",
        '@' => 'Cetak',
        'akses' => [1]
    ],

    /*
    |--------------------------------------------------------------------------
    | Karyawan (Employee Management)
    |--------------------------------------------------------------------------
    */
    
    'Karyawan' => [
        'class' => "app\KaryawanController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'Karyawan-View' => [
        'class' => "app\KaryawanController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'Karyawan-Api' => [
        'class' => "app\KaryawanController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'Karyawan-CRUD' => [
        'class' => "app\KaryawanController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'Karyawan-XLSX' => [
        'class' => "app\KaryawanController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'Karyawan-Cetak' => [
        'class' => "app\KaryawanController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Master - Relasi Atasan (Supervisor Relation)
    |--------------------------------------------------------------------------
    */
    
    'RelasiAtasan' => [
        'class' => "app\RelasiAtasanController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'RelasiAtasan-View' => [
        'class' => "app\RelasiAtasanController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'RelasiAtasan-Api' => [
        'class' => "app\RelasiAtasanController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'RelasiAtasan-CRUD' => [
        'class' => "app\RelasiAtasanController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'RelasiAtasan-XLSX' => [
        'class' => "app\RelasiAtasanController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'RelasiAtasan-Cetak' => [
        'class' => "app\RelasiAtasanController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Kriteria Penilaian (Assessment Criteria)
    |--------------------------------------------------------------------------
    */
    
    'Kriteria' => [
        'class' => "app\KriteriaController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'Kriteria-View' => [
        'class' => "app\KriteriaController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'Kriteria-Api' => [
        'class' => "app\KriteriaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan','atasan']
    ],
    'Kriteria-CRUD' => [
        'class' => "app\KriteriaController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'Kriteria-XLSX' => [
        'class' => "app\KriteriaController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'Kriteria-Cetak' => [
        'class' => "app\KriteriaController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Periode Penilaian (Assessment Period)
    |--------------------------------------------------------------------------
    */
    
    'PeriodePenilaian' => [
        'class' => "app\PeriodePenilaianController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'PeriodePenilaian-View' => [
        'class' => "app\PeriodePenilaianController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'PeriodePenilaian-Api' => [
        'class' => "app\PeriodePenilaianController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan','atasan']
    ],
    'PeriodePenilaian-CRUD' => [
        'class' => "app\PeriodePenilaianController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'PeriodePenilaian-XLSX' => [
        'class' => "app\PeriodePenilaianController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'PeriodePenilaian-Cetak' => [
        'class' => "app\PeriodePenilaianController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Laporan dan Analisis (Assessment Summary)
    |--------------------------------------------------------------------------
    */

    'Laporan' => [
        'class' => "app\LaporanController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'Laporan-Api' => [
        'class' => "app\LaporanController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'Laporan-Cetak' => [
        'class' => "app\LaporanController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],
    
    'RekapPenilaian' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'RekapPenilaian-View' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'RekapPenilaian-Api' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'RekapPenilaian-Detail' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'DetailApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'RekapPenilaian-Statistik' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'StatistikApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'RekapPenilaian-CRUD' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'RekapPenilaian-XLSX' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'RekapPenilaian-Cetak' => [
        'class' => "app\RekapPenilaianController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Analisa Kinerja KNN (KNN Performance Analysis)
    |--------------------------------------------------------------------------
    | 
    | Routes for KNN-based performance analysis including:
    | - Running KNN analysis (mock and database-backed)
    | - Viewing analysis results and history
    | - Detailed calculation reports
    |
    */
    
    'AnalisaKinerja' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-View' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-Api' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    // KNN Processing Routes
    'AnalisaKinerja-Proses' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-ProsesMock' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    // Analysis Results
    'AnalisaKinerja-Hasil' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-Detail' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-Daftar' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-Statistik' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    // CRUD & Export
    'AnalisaKinerja-CRUD' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'AnalisaKinerja-Hapus' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    'AnalisaKinerja-XLSX' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'AnalisaKinerja-Cetak' => [
        'class' => "app\AnalisaKinerjaController",
        '@' => 'CetakPage',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Klasifikasi Kinerja (Performance Classification & Training Data)
    |--------------------------------------------------------------------------
    |
    | Routes for managing classification labels and training data including:
    | - Training data management (add, remove, bulk operations)
    | - Calibration (manual classification correction)
    | - Validation and statistics
    |
    */
    
    'KlasifikasiKinerja' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-View' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-Api' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    // Training Data Management
    'KlasifikasiKinerja-ListTraining' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-AddTraining' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    'KlasifikasiKinerja-RemoveTraining' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    'KlasifikasiKinerja-BulkAddTraining' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    // Calibration Routes
    'KlasifikasiKinerja-Calibrate' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    'KlasifikasiKinerja-AutoCalibrate' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    // Classification Management
    'KlasifikasiKinerja-UpdateKlasifikasi' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin']
    ],
    'KlasifikasiKinerja-SuggestKlasifikasi' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    // Validation & Statistics
    'KlasifikasiKinerja-Validate' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-Statistik' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-Distribusi' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-Comparison' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    // CRUD & Export
    'KlasifikasiKinerja-CRUD' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'KlasifikasiKinerja-XLSX' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'KlasifikasiKinerja-Cetak' => [
        'class' => "app\KlasifikasiKinerjaController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Laporan Karyawan (Employee Reports)
    |--------------------------------------------------------------------------
    */
    
    'LaporanKaryawan' => [
        'class' => "app\LaporanKaryawanController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan']
    ],
    'LaporanKaryawan-View' => [
        'class' => "app\LaporanKaryawanController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'LaporanKaryawan-Api' => [
        'class' => "app\LaporanKaryawanController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan']
    ],
    'LaporanKaryawan-CRUD' => [
        'class' => "app\LaporanKaryawanController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'LaporanKaryawan-XLSX' => [
        'class' => "app\LaporanKaryawanController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'LaporanKaryawan-Cetak' => [
        'class' => "app\LaporanKaryawanController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Penilaian Bawahan (Subordinate Assessment)
    |--------------------------------------------------------------------------
    */
    
    'PenilaianBawahan' => [
        'class' => "app\PenilaianBawahanController",
        '@' => 'index',
        'akses' => ['admin', 'atasan']
    ],
    'PenilaianBawahan-View' => [
        'class' => "app\PenilaianBawahanController",
        '@' => 'View',
        'akses' => ['admin', 'atasan']
    ],
    'PenilaianBawahan-Api' => [
        'class' => "app\PenilaianBawahanController",
        '@' => 'indexApi',
        'akses' => ['admin', 'atasan']
    ],
    'PenilaianBawahan-CRUD' => [
        'class' => "app\PenilaianBawahanController",
        '@' => 'CRUD',
        'akses' => ['admin', 'atasan']
    ],
    'PenilaianBawahan-XLSX' => [
        'class' => "app\PenilaianBawahanController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'atasan']
    ],
    'PenilaianBawahan-Cetak' => [
        'class' => "app\PenilaianBawahanController",
        '@' => 'Cetak',
        'akses' => ['admin', 'atasan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Review Penilaian (Assessment Review)
    |--------------------------------------------------------------------------
    */
    
    'ReviewPenilaian' => [
        'class' => "app\ReviewPenilaianController",
        '@' => 'index',
        'akses' => ['admin', 'pimpinan', 'atasan']
    ],
    'ReviewPenilaian-View' => [
        'class' => "app\ReviewPenilaianController",
        '@' => 'View',
        'akses' => ['admin', 'pimpinan']
    ],
    'ReviewPenilaian-Api' => [
        'class' => "app\ReviewPenilaianController",
        '@' => 'indexApi',
        'akses' => ['admin', 'pimpinan', 'atasan']
    ],
    'ReviewPenilaian-CRUD' => [
        'class' => "app\ReviewPenilaianController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'ReviewPenilaian-XLSX' => [
        'class' => "app\ReviewPenilaianController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'pimpinan']
    ],
    'ReviewPenilaian-Cetak' => [
        'class' => "app\ReviewPenilaianController",
        '@' => 'Cetak',
        'akses' => ['admin', 'pimpinan']
    ],

    /*
    |--------------------------------------------------------------------------
    | Daftar Bawahan (Subordinate List)
    |--------------------------------------------------------------------------
    */
    
    'DaftarBawahan' => [
        'class' => "app\DaftarBawahanController",
        '@' => 'index',
        'akses' => ['admin', 'atasan', 'pimpinan']
    ],
    'DaftarBawahan-View' => [
        'class' => "app\DaftarBawahanController",
        '@' => 'View',
        'akses' => ['admin', 'atasan', 'pimpinan']
    ],
    'DaftarBawahan-Api' => [
        'class' => "app\DaftarBawahanController",
        '@' => 'indexApi',
        'akses' => ['admin', 'atasan', 'pimpinan']
    ],
    'DaftarBawahan-CRUD' => [
        'class' => "app\DaftarBawahanController",
        '@' => 'CRUD',
        'akses' => ['admin']
    ],
    'DaftarBawahan-XLSX' => [
        'class' => "app\DaftarBawahanController",
        '@' => 'exportXLSX',
        'akses' => ['admin', 'atasan', 'pimpinan']
    ],
    'DaftarBawahan-Cetak' => [
        'class' => "app\DaftarBawahanController",
        '@' => 'Cetak',
        'akses' => ['admin', 'atasan', 'pimpinan']
    ],

    'ProfilSaya' => [
        'class' => "app\ProfilSayaController",
        '@' => 'index',
        'akses' => []
    ],
    'ProfilSaya-Api' => [
        'class' => "app\ProfilSayaController",
        '@' => 'indexApi',
        'akses' => []
    ],
    'ProfilSaya-Update' => [
        'class' => "app\ProfilSayaController",
        '@' => 'updateProfil',
        'akses' => []
    ],
    'ProfilSaya-UbahPassword' => [
        'class' => "app\ProfilSayaController",
        '@' => 'ubahPassword',
        'akses' => []
    ],

    'RiwayatPenilaian' => [
        'class' => "app\RiwayatPenilaianController",
        '@' => 'index',
        'akses' => []
    ],
    'RiwayatPenilaian-Api' => [
        'class' => "app\RiwayatPenilaianController",
        '@' => 'indexApi',
        'akses' => []
    ],
    'RiwayatPenilaian-Detail' => [
        'class' => "app\RiwayatPenilaianController",
        '@' => 'detailApi',
        'akses' => []
    ],
    'RiwayatPenilaian-Cetak' => [
        'class' => "app\RiwayatPenilaianController",
        '@' => 'Cetak',
        'akses' => []
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload & Import
    |--------------------------------------------------------------------------
    */
    
    'Upload-data' => [
        'class' => "app\Upload",
        '@' => 'Index',
        'akses' => ['Admin', 'Sekretaris']
    ],
    'Proses-Upload' => [
        'class' => "app\Upload",
        '@' => 'ProsesUpload',
        'akses' => ['Admin', 'Sekretaris']
    ],
    'Cetak-QR' => [
        'class' => "app\ElmotionController",
        '@' => 'CetakQR',
        'akses' => null
    ],
    'Import-Data' => [
        'class' => "app\ElmotionController",
        '@' => 'ImportData',
        'akses' => null
    ],

];
