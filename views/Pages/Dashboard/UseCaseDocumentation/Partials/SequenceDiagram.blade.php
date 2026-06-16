@php
    $diagramId = 'sequence-diagram-' . $useCase['no'];
    $sequenceDescriptions = [
        1 => '<i>Sequence Diagram</i> <i>Login</i> merupakan rancangan proses autentikasi pengguna untuk memperoleh akses ke dalam sistem sebagaimana ditunjukkan pada gambar di bawah.',
        2 => '<i>Sequence Diagram</i> <i>Logout</i> merupakan rancangan proses pengakhiran sesi aktif pengguna untuk keluar dari sistem sebagaimana ditunjukkan pada gambar di bawah.',
        3 => '<i>Sequence Diagram</i> <i>Dashboard</i> merupakan rancangan proses pemuatan informasi ringkasan dan komponen utama pada halaman utama sistem sebagaimana ditunjukkan pada gambar di bawah.',
        4 => '<i>Sequence Diagram</i> <i>Data Karyawan</i> merupakan rancangan proses pengelolaan data karyawan yang meliputi penambahan, pengubahan, penghapusan, dan penyaringan data sebagaimana ditunjukkan pada gambar di bawah.',
        5 => '<i>Sequence Diagram</i> <i>Relasi Atasan</i> merupakan rancangan proses penetapan hubungan antara karyawan dan atasan dalam struktur organisasi sebagaimana ditunjukkan pada gambar di bawah.',
        6 => '<i>Sequence Diagram</i> <i>Kriteria Penilaian</i> merupakan rancangan proses pengelolaan kriteria yang digunakan sebagai dasar penilaian kinerja sebagaimana ditunjukkan pada gambar di bawah.',
        7 => '<i>Sequence Diagram</i> <i>Periode Penilaian</i> merupakan rancangan proses pengaturan periode pelaksanaan penilaian kinerja sebagaimana ditunjukkan pada gambar di bawah.',
        8 => '<i>Sequence Diagram</i> <i>Rekap Penilaian</i> merupakan rancangan proses penampilan rekapitulasi hasil penilaian berdasarkan periode yang dipilih sebagaimana ditunjukkan pada gambar di bawah.',
        9 => '<i>Sequence Diagram</i> <i>Analisa Kinerja</i> merupakan rancangan proses analisis data penilaian menggunakan metode <i>k-nearest neighbor</i> sebagaimana ditunjukkan pada gambar di bawah.',
        10 => '<i>Sequence Diagram</i> <i>Penilaian Bawahan</i> merupakan rancangan proses penilaian bawahan berdasarkan kriteria yang telah ditetapkan sebagaimana ditunjukkan pada gambar di bawah.',
        11 => '<i>Sequence Diagram</i> <i>Review Penilaian</i> merupakan rancangan proses peninjauan kembali hasil penilaian yang telah tersimpan di dalam sistem sebagaimana ditunjukkan pada gambar di bawah.',
        12 => '<i>Sequence Diagram</i> <i>Daftar Bawahan</i> merupakan rancangan proses penampilan data bawahan langsung beserta informasi pendukungnya sebagaimana ditunjukkan pada gambar di bawah.',
        13 => '<i>Sequence Diagram</i> <i>Laporan</i> merupakan rancangan proses penyajian laporan terpadu yang memuat pilihan rekapitulasi data sebagaimana ditunjukkan pada gambar di bawah.',
        14 => '<i>Sequence Diagram</i> <i>Rekap Karyawan</i> merupakan rancangan proses penyajian rekap data karyawan sesuai parameter filter yang dipilih sebagaimana ditunjukkan pada gambar di bawah.',
        15 => '<i>Sequence Diagram</i> <i>Rekap Penilaian</i> dan <i>Klasifikasi</i> merupakan rancangan proses penyajian hasil rekap penilaian beserta klasifikasi <i>KNN</i> sebagai bahan pendukung pengambilan keputusan sebagaimana ditunjukkan pada gambar di bawah.',
    ];
    $sequenceDescription = $sequenceDescriptions[$useCase['no']] ?? ('<i>Sequence Diagram</i> ' . $useCase['title'] . ' merupakan rancangan proses interaksi antara pengguna, <i>View</i>, <i>Controller</i>, dan <i>Database</i> sebagaimana ditunjukkan pada gambar di bawah.');
    $pumlLines = [
        '@startuml',
        "title Sequence Diagram {$useCase['title']}",
        'actor "' . $useCase['actor'] . '" as User',
        'boundary "View" as View',
        'control "Controller" as Controller',
        'database "Database" as DB',
        '',
    ];

    switch ($useCase['no']) {
        case 1:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Isi username dan password',
                'activate View',
                'View -> Controller: POST /ProsesLogin(username, password, token)',
                'activate Controller',
                'Controller -> DB: SELECT karyawan WHERE username = ?',
                'activate DB',
                'DB --> Controller: Data karyawan / kosong',
                'deactivate DB',
                'alt Data ditemukan dan password valid',
                '  create "Session/JWT" as Session',
                '  Controller -> Session: set id',
                '  Controller -> Session: set nama',
                '  Controller -> Session: set role',
                '  Controller -> Session: set jwt',
                '  Controller --> View: status sukses, pesan, jwt',
                '  View --> User: Toast sukses + redirect Dashboard',
                '  destroy Session',
                'else User tidak ditemukan / password salah',
                '  Controller --> View: status gagal, pesan error',
                '  View --> User: Toast gagal',
                'end',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 2:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Klik Logout',
                'activate View',
                'View -> Controller: GET /Logout',
                'activate Controller',
                'Controller -> DB: Hapus session aktif / validasi logout',
                'activate DB',
                'DB --> Controller: Status logout',
                'deactivate DB',
                'destroy Session',
                'Controller --> View: Redirect ke Login',
                'View --> User: Tampilkan halaman login',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 3:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Dashboard',
                'activate View',
                'View -> Controller: GET /Dashboard',
                'activate Controller',
                'Controller -> DB: Ambil ringkasan data',
                'activate DB',
                'DB --> Controller: Data statistik dan ringkasan',
                'deactivate DB',
                'Controller --> View: Data dashboard',
                'View --> User: Tampilkan dashboard',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 4:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Data Karyawan',
                'activate View',
                'View -> Controller: GET /Karyawan',
                'activate Controller',
                'Controller -> DB: SELECT data karyawan',
                'activate DB',
                'DB --> Controller: Daftar karyawan',
                'deactivate DB',
                'Controller --> View: Data daftar karyawan',
                'View --> User: Tampilkan tabel karyawan',
                'alt Tambah / Ubah data',
                '  User -> View: Isi form karyawan',
                '  View -> Controller: POST/PUT /Karyawan',
                '  create "KaryawanRecord" as KaryawanRecord',
                '  Controller -> KaryawanRecord: set id',
                '  Controller -> KaryawanRecord: set nama',
                '  Controller -> KaryawanRecord: set jabatan',
                '  Controller -> KaryawanRecord: set pekerjaan',
                '  Controller -> KaryawanRecord: set tanggal_bergabung',
                '  Controller -> KaryawanRecord: set status',
                '  Controller -> KaryawanRecord: set role',
                '  Controller -> KaryawanRecord: set username',
                '  Controller -> KaryawanRecord: set password',
                '  KaryawanRecord -> DB: insert / update',
                '  DB --> KaryawanRecord: status simpan',
                '  destroy KaryawanRecord',
                '  Controller --> View: response sukses',
                '  View --> User: Refresh data',
                'else Hapus data',
                '  User -> View: Konfirmasi hapus',
                '  View -> Controller: DELETE /Karyawan/{id}',
                '  Controller -> DB: DELETE karyawan',
                '  DB --> Controller: status hapus',
                '  Controller --> View: response sukses',
                '  View --> User: Refresh data',
                'else Filter data',
                '  User -> View: Isi filter',
                '  View -> Controller: GET /Karyawan?filter',
                '  Controller -> DB: Query filter',
                '  DB --> Controller: Data tersaring',
                '  Controller --> View: Data hasil filter',
                '  View --> User: Tampilkan hasil filter',
                'end',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 5:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Relasi Atasan',
                'activate View',
                'View -> Controller: GET /RelasiAtasan',
                'activate Controller',
                'Controller -> DB: Ambil relasi dan daftar atasan',
                'activate DB',
                'DB --> Controller: Data relasi',
                'deactivate DB',
                'Controller --> View: Data relasi',
                'View --> User: Tampilkan relasi',
                'User -> View: Simpan relasi baru',
                'View -> Controller: POST /RelasiAtasan',
                'create "RelasiAtasanRecord" as RelasiRecord',
                'Controller -> RelasiRecord: set id_karyawan',
                'Controller -> RelasiRecord: set id_atasan',
                'RelasiRecord -> DB: insert',
                'DB --> RelasiRecord: status simpan',
                'destroy RelasiRecord',
                'Controller --> View: response sukses',
                'View --> User: Refresh relasi',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 6:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Kriteria Penilaian',
                'activate View',
                'View -> Controller: GET /Kriteria',
                'activate Controller',
                'Controller -> DB: Ambil kriteria aktif',
                'activate DB',
                'DB --> Controller: Daftar kriteria',
                'deactivate DB',
                'Controller --> View: Data kriteria',
                'View --> User: Tampilkan tabel kriteria',
                'User -> View: Simpan kriteria',
                'View -> Controller: POST/PUT /Kriteria',
                'create "KriteriaRecord" as KriteriaRecord',
                'Controller -> KriteriaRecord: set id',
                'Controller -> KriteriaRecord: set kode',
                'Controller -> KriteriaRecord: set nama',
                'Controller -> KriteriaRecord: set nama_en',
                'Controller -> KriteriaRecord: set deskripsi',
                'Controller -> KriteriaRecord: set bobot',
                'Controller -> KriteriaRecord: set status',
                'Controller -> KriteriaRecord: set urutan',
                'KriteriaRecord -> DB: insert / update',
                'DB --> KriteriaRecord: status simpan',
                'destroy KriteriaRecord',
                'Controller --> View: response sukses',
                'View --> User: Refresh data',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 7:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Periode Penilaian',
                'activate View',
                'View -> Controller: GET /PeriodePenilaian',
                'activate Controller',
                'Controller -> DB: Ambil daftar periode',
                'activate DB',
                'DB --> Controller: Daftar periode',
                'deactivate DB',
                'Controller --> View: Data periode',
                'View --> User: Tampilkan periode',
                'User -> View: Simpan / aktifkan periode',
                'View -> Controller: POST/PUT /PeriodePenilaian',
                'create "PeriodeRecord" as PeriodeRecord',
                'Controller -> PeriodeRecord: set id',
                'Controller -> PeriodeRecord: set nama_periode',
                'Controller -> PeriodeRecord: set tanggal_mulai',
                'Controller -> PeriodeRecord: set tanggal_selesai',
                'Controller -> PeriodeRecord: set status',
                'Controller -> PeriodeRecord: set keterangan',
                'PeriodeRecord -> DB: insert / update',
                'DB --> PeriodeRecord: status simpan',
                'destroy PeriodeRecord',
                'alt Mengaktifkan periode terpilih',
                '  Controller -> DB: Nonaktifkan periode lain',
                '  Controller -> DB: Aktifkan periode terpilih',
                'end',
                'Controller --> View: response sukses',
                'View --> User: Refresh periode',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 8:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Rekap Penilaian',
                'activate View',
                'View -> Controller: GET /RekapPenilaian',
                'activate Controller',
                'Controller -> DB: Ambil rekap penilaian',
                'activate DB',
                'DB --> Controller: Data rekap',
                'deactivate DB',
                'Controller --> View: Data rekap',
                'View --> User: Tampilkan rekap',
                'User -> View: Pilih periode',
                'View -> Controller: GET /RekapPenilaian?periode_id=...',
                'Controller -> DB: Filter rekap per periode',
                'activate DB',
                'DB --> Controller: Data tersaring',
                'deactivate DB',
                'Controller --> View: Data rekap tersaring',
                'View --> User: Tampilkan hasil filter',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 9:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Analisa Kinerja',
                'activate View',
                'View -> Controller: GET /AnalisaKinerja',
                'activate Controller',
                'Controller -> DB: Ambil data training dan testing',
                'activate DB',
                'DB --> Controller: Dataset analisa',
                'deactivate DB',
                'Controller --> View: Form analisa',
                'View --> User: Tampilkan parameter analisa',
                'User -> View: Jalankan analisa',
                'View -> Controller: POST /AnalisaKinerja',
                'create "AnalisaResult" as AnalisaResult',
                'Controller -> AnalisaResult: set k',
                'Controller -> AnalisaResult: set metode_jarak',
                'Controller -> AnalisaResult: set normalisasi',
                'Controller -> AnalisaResult: set periode_id',
                'AnalisaResult -> DB: proses KNN',
                'DB --> AnalisaResult: hasil klasifikasi',
                'destroy AnalisaResult',
                'Controller --> View: hasil analisa',
                'View --> User: Tampilkan hasil klasifikasi',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 10:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Penilaian Bawahan',
                'activate View',
                'View -> Controller: GET /PenilaianBawahan',
                'activate Controller',
                'Controller -> DB: Ambil periode aktif dan daftar bawahan',
                'activate DB',
                'DB --> Controller: Data bawahan',
                'deactivate DB',
                'Controller --> View: Form penilaian',
                'View --> User: Tampilkan form',
                'User -> View: Simpan penilaian',
                'View -> Controller: POST /PenilaianBawahan',
                'create "PenilaianRecord" as PenilaianRecord',
                'Controller -> PenilaianRecord: set periode_id',
                'Controller -> PenilaianRecord: set karyawan_id',
                'Controller -> PenilaianRecord: set penilai_id',
                'Controller -> PenilaianRecord: set nilai_kriteria',
                'Controller -> PenilaianRecord: set total_nilai',
                'Controller -> PenilaianRecord: set klasifikasi',
                'Controller -> PenilaianRecord: set catatan',
                'Controller -> PenilaianRecord: set status',
                'PenilaianRecord -> DB: insert / update',
                'DB --> PenilaianRecord: status simpan',
                'destroy PenilaianRecord',
                'Controller --> View: response sukses',
                'View --> User: Tampilkan hasil simpan',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 11:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Review Penilaian',
                'activate View',
                'View -> Controller: GET /ReviewPenilaian',
                'activate Controller',
                'Controller -> DB: Ambil daftar penilaian',
                'activate DB',
                'DB --> Controller: Data penilaian',
                'deactivate DB',
                'Controller --> View: Daftar penilaian',
                'View --> User: Tampilkan daftar',
                'User -> View: Pilih detail penilaian',
                'View -> Controller: GET /ReviewPenilaian/{id}',
                'Controller -> DB: Ambil detail penilaian',
                'activate DB',
                'DB --> Controller: Detail penilaian',
                'deactivate DB',
                'Controller --> View: Detail penilaian',
                'View --> User: Tampilkan modal detail',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 12:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Daftar Bawahan',
                'activate View',
                'View -> Controller: GET /DaftarBawahan',
                'activate Controller',
                'Controller -> DB: Ambil bawahan langsung',
                'activate DB',
                'DB --> Controller: Data bawahan',
                'deactivate DB',
                'Controller --> View: Daftar bawahan',
                'View --> User: Tampilkan kartu bawahan',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 13:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Laporan',
                'activate View',
                'View -> Controller: GET /Laporan',
                'activate Controller',
                'Controller -> DB: Ambil daftar periode dan filter',
                'activate DB',
                'DB --> Controller: Opsi laporan',
                'deactivate DB',
                'Controller --> View: Form pilihan laporan',
                'View --> User: Tampilkan pilihan laporan',
                'alt Rekap Karyawan',
                '  User -> View: Pilih Rekap Karyawan',
                '  View -> Controller: GET /Laporan?jenis_laporan=rekap_karyawan',
                '  Controller -> DB: Ambil data karyawan',
                '  activate DB',
                '  DB --> Controller: Data karyawan',
                '  deactivate DB',
                '  Controller --> View: Preview rekap karyawan',
                'else Rekap Penilaian & Klasifikasi',
                '  User -> View: Pilih Rekap Penilaian & Klasifikasi',
                '  View -> Controller: GET /Laporan?jenis_laporan=rekap_penilaian_klasifikasi',
                '  Controller -> DB: Ambil data penilaian',
                '  activate DB',
                '  DB --> Controller: Data penilaian',
                '  deactivate DB',
                '  Controller --> View: Preview rekap penilaian',
                'end',
                'View --> User: Tampilkan hasil laporan',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 14:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Rekap Karyawan',
                'activate View',
                'View -> Controller: GET /Laporan?jenis_laporan=rekap_karyawan',
                'activate Controller',
                'Controller -> DB: Ambil rekap karyawan',
                'activate DB',
                'DB --> Controller: Data karyawan',
                'deactivate DB',
                'Controller --> View: Preview rekap',
                'View --> User: Tampilkan rekap karyawan',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        case 15:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Buka Rekap Penilaian & Klasifikasi',
                'activate View',
                'View -> Controller: GET /Laporan?jenis_laporan=rekap_penilaian_klasifikasi',
                'activate Controller',
                'Controller -> DB: Ambil rekap penilaian',
                'activate DB',
                'DB --> Controller: Data penilaian',
                'deactivate DB',
                'Controller --> View: Preview rekap penilaian',
                'View --> User: Tampilkan rekap dan klasifikasi',
                'User -> View: Cetak laporan',
                'View -> Controller: GET /Laporan/Cetak',
                'Controller -> DB: Ambil data cetak',
                'activate DB',
                'DB --> Controller: Data laporan cetak',
                'deactivate DB',
                'Controller --> View: Dokumen cetak',
                'View --> User: Laporan siap dicetak',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;

        default:
            $pumlLines = array_merge($pumlLines, [
                'User -> View: Aksi pada halaman',
                'activate View',
                'View -> Controller: Request data',
                'activate Controller',
                'Controller -> DB: Query data',
                'activate DB',
                'DB --> Controller: Hasil query',
                'deactivate DB',
                'Controller --> View: Response',
                'View --> User: Output halaman',
                'deactivate Controller',
                'deactivate View',
            ]);
            break;
    }

    $pumlLines[] = '@enduml';
@endphp
<div class="card mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <strong>4.2.3.{{ $useCase['no'] }} Sequence Diagram {{ $useCase['title'] }}</strong>
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $diagramId }}" aria-expanded="false" aria-controls="{{ $diagramId }}">
            Show / Hide
        </button>
    </div>
    <div id="{{ $diagramId }}" class="collapse">
        <div class="card-body">
            <div class="alert alert-info">
                {!! $sequenceDescription !!}
            </div>
            <pre class="bg-dark text-light p-3 rounded mb-0"><code class="language-plantuml">{{ implode("\n", $pumlLines) }}</code></pre>
        </div>
    </div>
</div>
