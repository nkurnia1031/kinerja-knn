@php
    $diagramId = 'sequence-diagram-' . $useCase['no'];
    $figureNumber = '4.' . ($useCase['no'] + 19);
    $sequenceNarratives = [
        1 => '<i>Sequence Diagram</i> <i>Login</i> menggambarkan urutan interaksi antara pengguna, halaman utama, modul login, dan basis data dalam proses autentikasi, dimulai dari penyampaian kredensial hingga sistem memberikan hasil verifikasi dan mengarahkan pengguna sesuai hak aksesnya.',
        2 => '<i>Sequence Diagram</i> <i>Logout</i> menjelaskan urutan interaksi pada proses pengakhiran sesi penggunaan sistem, yaitu ketika pengguna menyampaikan permintaan keluar, sistem memproses penghentian sesi, lalu menampilkan kembali halaman login sebagai akhir dari akses pengguna.',
        3 => '<i>Sequence Diagram</i> <i>Dashboard</i> memperlihatkan urutan interaksi pada saat pengguna membuka halaman utama sistem, kemudian sistem mengambil data ringkasan yang relevan dan menyajikannya dalam bentuk informasi awal sesuai peran pengguna.',
        4 => '<i>Sequence Diagram</i> <i>Data Karyawan</i> menunjukkan urutan interaksi dalam pengelolaan data karyawan, mulai dari penampilan daftar data, pelaksanaan aksi tambah, ubah, hapus, atau filter, hingga sistem memperbarui informasi yang ditampilkan kepada pengguna.',
        5 => '<i>Sequence Diagram</i> <i>Atur Relasi Atasan</i> menggambarkan urutan interaksi pada proses penetapan hubungan atasan dan bawahan, dimulai dari peninjauan data relasi yang tersedia, pemilihan atasan untuk karyawan tertentu, sampai penyimpanan relasi yang telah ditetapkan.',
        6 => '<i>Sequence Diagram</i> <i>Kriteria Penilaian</i> menjelaskan urutan interaksi pada proses pengelolaan kriteria penilaian, yaitu ketika pengguna meninjau daftar kriteria, melakukan perubahan data, kemudian sistem menyimpan dan menampilkan kembali data kriteria yang telah diperbarui.',
        7 => '<i>Sequence Diagram</i> <i>Periode Penilaian</i> memperlihatkan urutan interaksi dalam pengaturan periode penilaian, meliputi peninjauan data periode, penyimpanan perubahan, serta penetapan periode aktif yang akan digunakan dalam proses penilaian kinerja.',
        8 => '<i>Sequence Diagram</i> <i>Rekap Penilaian</i> menggambarkan urutan interaksi ketika pengguna meninjau rekap hasil penilaian, memilih filter atau rincian data tertentu, dan sistem menampilkan informasi rekap yang sesuai dengan kebutuhan peninjauan.',
        9 => '<i>Sequence Diagram</i> <i>Analisa Kinerja</i> menunjukkan urutan interaksi pada proses analisis kinerja berbasis metode K-Nearest Neighbor, dimulai dari penentuan parameter analisis oleh pengguna hingga sistem menghasilkan dan menampilkan hasil klasifikasi kinerja.',
        10 => '<i>Sequence Diagram</i> <i>Penilaian Bawahan</i> menjelaskan urutan interaksi pada proses penilaian bawahan, mulai dari pemilihan data karyawan yang akan dinilai, pengisian nilai kriteria, sampai sistem mengolah serta menyimpan hasil penilaian.',
        11 => '<i>Sequence Diagram</i> <i>Review Penilaian</i> memperlihatkan urutan interaksi ketika pengguna meninjau hasil penilaian yang telah tersimpan, memilih data tertentu, dan sistem menampilkan rincian penilaian untuk kebutuhan evaluasi.',
        12 => '<i>Sequence Diagram</i> <i>Daftar Bawahan</i> menggambarkan urutan interaksi pada saat atasan meninjau daftar bawahan, memeriksa ringkasan informasi yang tersedia, lalu memilih tindakan lanjutan sesuai kebutuhan pengelolaan atau penilaian.',
        13 => '<i>Sequence Diagram</i> <i>Laporan</i> menunjukkan urutan interaksi pada proses penyajian laporan terpadu, dimulai dari pemilihan jenis laporan, pengisian filter, penampilan hasil, hingga penyediaan keluaran laporan yang siap ditinjau atau dicetak.',
        14 => '<i>Sequence Diagram</i> <i>Rekap Karyawan</i> menjelaskan urutan interaksi dalam penyusunan rekap data karyawan, yaitu ketika pengguna menentukan filter, sistem mengambil data yang relevan, dan hasil rekap ditampilkan sebagai bahan pemantauan maupun dokumentasi.',
        15 => '<i>Sequence Diagram</i> <i>Rekap Penilaian &amp; Klasifikasi</i> memperlihatkan urutan interaksi pada penyajian rekap hasil penilaian dan klasifikasi KNN, mulai dari pemilihan parameter peninjauan, penampilan data hasil evaluasi, hingga penyediaan detail atau keluaran cetak sebagai pendukung pengambilan keputusan.',
    ];
    $sequenceDescription = ($sequenceNarratives[$useCase['no']] ?? ('<i>Sequence Diagram</i> ' . $useCase['title'] . ' menjelaskan urutan interaksi antara pengguna, halaman utama, modul proses, dan basis data pada fitur yang sedang dijalankan.')) . ' Alur tersebut ditunjukkan pada Gambar ' . $figureNumber . '.';
    $participantLines = [
        'actor "' . $useCase['actor'] . '" as User',
        'participant "Main" as Main',
        'participant "' . $useCase['title'] . '" as Feature',
        'database "Basis Data" as DB',
    ];

    if ($useCase['no'] === 4) {
        $participantLines = [
            'actor "' . $useCase['actor'] . '" as User',
            'participant "Main" as Main',
            'participant "Data Karyawan" as Feature',
            'participant "Koneksi Data\\nBase" as Connection',
            'participant "Tabel Data\\nKaryawan" as EntityTable',
        ];
    }

    $pumlLines = array_merge([
        '@startuml',
        "title Sequence Diagram {$useCase['title']}",
    ], $participantLines, [
        '',
    ]);

    switch ($useCase['no']) {
        case 1:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Mengisi username dan password',
                'activate Main',
                'Main -> Feature: Menyampaikan data login',
                'activate Feature',
                'Feature -> DB: Memeriksa kredensial pengguna',
                'activate DB',
                'DB --> Feature: Hasil verifikasi login',
                'deactivate DB',
                'alt Kredensial valid',
                '  Feature --> Main: Menyampaikan status login berhasil',
                '  Main --> User: Menampilkan dashboard sesuai hak akses',
                'else Kredensial tidak valid',
                '  Feature --> Main: Menyampaikan pesan kegagalan login',
                '  Main --> User: Menampilkan notifikasi kesalahan',
                'end',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 2:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu logout',
                'activate Main',
                'Main -> Feature: Meneruskan permintaan logout',
                'activate Feature',
                'Feature -> DB: Mengakhiri sesi pengguna',
                'activate DB',
                'DB --> Feature: Status pengakhiran sesi',
                'deactivate DB',
                'Feature --> Main: Menyampaikan hasil logout',
                'Main --> User: Menampilkan halaman login',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 3:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Membuka halaman dashboard',
                'activate Main',
                'Main -> Feature: Meminta data dashboard',
                'activate Feature',
                'Feature -> DB: Mengambil data ringkasan sesuai hak akses',
                'activate DB',
                'DB --> Feature: Data dashboard',
                'deactivate DB',
                'Feature --> Main: Menyediakan informasi dashboard',
                'Main --> User: Menampilkan ringkasan dashboard',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 4:
            $pumlLines = array_merge($pumlLines, [
                'autonumber 1',
                'User -> Main: Pilih Data Karyawan',
                'activate Main',
                'Main -> Feature: Menampilkan Data Karyawan',
                'activate Feature',
                'Feature --> User: Menampilkan Data Karyawan',
                'User -> Feature: Memilih aksi Tambah Data, Hapus, Edit, atau Filter',
                'User -> Feature: Menambah, menghapus, mengedit, atau memfilter data karyawan',
                'create Connection',
                'Feature -> Connection: <<create>>',
                'activate Connection',
                'Connection -> EntityTable: <<create>>',
                'activate EntityTable',
                'Feature -> EntityTable: Membuka Koneksi',
                'Feature -> EntityTable: Eksekusi Proses Data Karyawan',
                'Feature -> EntityTable: Tutup Koneksi',
                'destroy EntityTable',
                'Feature -> EntityTable: <<destroy>>',
                'destroy Connection',
                'Feature -> Connection: <<destroy>>',
                'Feature --> User: Menyampaikan pesan data karyawan telah berhasil diperbarui',
                'deactivate EntityTable',
                'deactivate Connection',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 5:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Relasi Atasan',
                'activate Main',
                'Main -> Feature: Meminta data relasi atasan',
                'activate Feature',
                'Feature -> DB: Mengambil data relasi dan daftar atasan',
                'activate DB',
                'DB --> Feature: Data relasi atasan',
                'deactivate DB',
                'Feature --> Main: Menyajikan data relasi atasan',
                'Main --> User: Menampilkan daftar relasi atasan',
                'User -> Main: Menetapkan atasan untuk karyawan',
                'Main -> Feature: Meneruskan data relasi baru',
                'Feature -> DB: Menyimpan relasi atasan',
                'activate DB',
                'DB --> Feature: Status penyimpanan relasi',
                'deactivate DB',
                'Feature --> Main: Menyampaikan hasil penyimpanan',
                'Main --> User: Menampilkan relasi atasan yang telah diperbarui',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 6:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Kriteria Penilaian',
                'activate Main',
                'Main -> Feature: Meminta daftar kriteria penilaian',
                'activate Feature',
                'Feature -> DB: Mengambil data kriteria',
                'activate DB',
                'DB --> Feature: Daftar kriteria penilaian',
                'deactivate DB',
                'Feature --> Main: Menyajikan data kriteria',
                'Main --> User: Menampilkan daftar kriteria penilaian',
                'User -> Main: Menambahkan, mengubah, atau menghapus kriteria',
                'Main -> Feature: Meneruskan perubahan data kriteria',
                'Feature -> DB: Menyimpan perubahan data kriteria',
                'activate DB',
                'DB --> Feature: Status penyimpanan kriteria',
                'deactivate DB',
                'Feature --> Main: Menyampaikan hasil pengelolaan kriteria',
                'Main --> User: Menampilkan daftar kriteria yang telah diperbarui',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 7:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Periode Penilaian',
                'activate Main',
                'Main -> Feature: Meminta daftar periode penilaian',
                'activate Feature',
                'Feature -> DB: Mengambil data periode penilaian',
                'activate DB',
                'DB --> Feature: Daftar periode penilaian',
                'deactivate DB',
                'Feature --> Main: Menyajikan data periode penilaian',
                'Main --> User: Menampilkan daftar periode penilaian',
                'User -> Main: Menambahkan atau memperbarui data periode',
                'Main -> Feature: Meneruskan data periode penilaian',
                'Feature -> DB: Menyimpan data periode penilaian',
                'activate DB',
                'DB --> Feature: Status penyimpanan periode',
                'deactivate DB',
                'alt Periode diaktifkan',
                '  Feature -> DB: Memperbarui status periode aktif',
                '  activate DB',
                '  DB --> Feature: Status perubahan periode aktif',
                '  deactivate DB',
                'end',
                'Feature --> Main: Menyampaikan hasil pengelolaan periode',
                'Main --> User: Menampilkan daftar periode yang telah diperbarui',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 8:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Rekap Penilaian',
                'activate Main',
                'Main -> Feature: Meminta data rekap penilaian',
                'activate Feature',
                'Feature -> DB: Mengambil data rekap penilaian',
                'activate DB',
                'DB --> Feature: Data rekap penilaian',
                'deactivate DB',
                'Feature --> Main: Menyajikan data rekap penilaian',
                'Main --> User: Menampilkan rekap penilaian',
                'User -> Main: Memilih periode atau rincian data',
                'Main -> Feature: Meneruskan permintaan peninjauan',
                'Feature -> DB: Mengambil data sesuai pilihan pengguna',
                'activate DB',
                'DB --> Feature: Hasil penyaringan atau rincian data',
                'deactivate DB',
                'Feature --> Main: Menyediakan hasil peninjauan',
                'Main --> User: Menampilkan rekap atau rincian penilaian',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 9:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Analisa Kinerja',
                'activate Main',
                'Main -> Feature: Meminta data awal analisa',
                'activate Feature',
                'Feature -> DB: Mengambil data analisa kinerja',
                'activate DB',
                'DB --> Feature: Data analisa awal',
                'deactivate DB',
                'Feature --> Main: Menyediakan parameter analisa',
                'Main --> User: Menampilkan form analisa',
                'User -> Main: Menentukan parameter dan menjalankan analisa',
                'Main -> Feature: Meneruskan parameter analisa',
                'Feature -> DB: Memproses analisa dan klasifikasi KNN',
                'activate DB',
                'DB --> Feature: Hasil analisa kinerja',
                'deactivate DB',
                'Feature --> Main: Menyajikan hasil analisa',
                'Main --> User: Menampilkan hasil klasifikasi kinerja',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 10:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Penilaian Bawahan',
                'activate Main',
                'Main -> Feature: Meminta data periode aktif dan bawahan',
                'activate Feature',
                'Feature -> DB: Mengambil data penilaian bawahan',
                'activate DB',
                'DB --> Feature: Data periode dan daftar bawahan',
                'deactivate DB',
                'Feature --> Main: Menyediakan form penilaian',
                'Main --> User: Menampilkan data bawahan yang dapat dinilai',
                'User -> Main: Mengisi nilai dan catatan penilaian',
                'Main -> Feature: Meneruskan data penilaian',
                'Feature -> DB: Mengolah dan menyimpan hasil penilaian',
                'activate DB',
                'DB --> Feature: Total nilai dan status penyimpanan',
                'deactivate DB',
                'Feature --> Main: Menyajikan hasil penilaian',
                'Main --> User: Menampilkan hasil penilaian bawahan',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 11:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Review Penilaian',
                'activate Main',
                'Main -> Feature: Meminta daftar data penilaian',
                'activate Feature',
                'Feature -> DB: Mengambil data penilaian',
                'activate DB',
                'DB --> Feature: Daftar data penilaian',
                'deactivate DB',
                'Feature --> Main: Menyajikan daftar penilaian',
                'Main --> User: Menampilkan daftar penilaian',
                'User -> Main: Memilih data untuk ditinjau',
                'Main -> Feature: Meneruskan permintaan rincian data',
                'Feature -> DB: Mengambil rincian penilaian',
                'activate DB',
                'DB --> Feature: Rincian penilaian',
                'deactivate DB',
                'Feature --> Main: Menyajikan rincian penilaian',
                'Main --> User: Menampilkan detail hasil penilaian',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 12:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Daftar Bawahan',
                'activate Main',
                'Main -> Feature: Meminta data bawahan langsung',
                'activate Feature',
                'Feature -> DB: Mengambil data bawahan',
                'activate DB',
                'DB --> Feature: Daftar bawahan langsung',
                'deactivate DB',
                'Feature --> Main: Menyajikan daftar bawahan',
                'Main --> User: Menampilkan ringkasan data bawahan',
                'User -> Main: Memilih tindakan lanjutan',
                'Main -> Feature: Meneruskan pilihan tindakan',
                'Feature --> Main: Menyediakan halaman lanjutan',
                'Main --> User: Menampilkan halaman sesuai pilihan',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 13:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih menu Laporan',
                'activate Main',
                'Main -> Feature: Meminta pilihan laporan dan filter',
                'activate Feature',
                'Feature -> DB: Mengambil data pendukung laporan',
                'activate DB',
                'DB --> Feature: Pilihan laporan dan filter',
                'deactivate DB',
                'Feature --> Main: Menyajikan pilihan laporan',
                'Main --> User: Menampilkan halaman laporan',
                'User -> Main: Memilih jenis laporan dan filter',
                'Main -> Feature: Meneruskan permintaan laporan',
                'alt Rekap Karyawan',
                '  Feature -> DB: Mengambil data rekap karyawan',
                '  activate DB',
                '  DB --> Feature: Data rekap karyawan',
                '  deactivate DB',
                '  Feature --> Main: Menyajikan rekap karyawan',
                'else Rekap Penilaian & Klasifikasi',
                '  Feature -> DB: Mengambil data rekap penilaian dan klasifikasi',
                '  activate DB',
                '  DB --> Feature: Data rekap penilaian dan klasifikasi',
                '  deactivate DB',
                '  Feature --> Main: Menyajikan rekap penilaian dan klasifikasi',
                'end',
                'Main --> User: Menampilkan hasil laporan',
                'User -> Main: Memilih cetak laporan',
                'Main -> Feature: Meneruskan permintaan cetak',
                'Feature --> Main: Menyediakan keluaran laporan',
                'Main --> User: Menampilkan laporan yang siap dicetak',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 14:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih laporan Rekap Karyawan',
                'activate Main',
                'Main -> Feature: Meminta data rekap karyawan',
                'activate Feature',
                'Feature -> DB: Mengambil data rekap karyawan',
                'activate DB',
                'DB --> Feature: Data rekap karyawan',
                'deactivate DB',
                'Feature --> Main: Menyajikan rekap karyawan',
                'Main --> User: Menampilkan rekap karyawan',
                'User -> Main: Menentukan filter atau mencetak laporan',
                'Main -> Feature: Meneruskan kebutuhan peninjauan',
                'Feature -> DB: Mengambil data sesuai kebutuhan pengguna',
                'activate DB',
                'DB --> Feature: Data rekap yang telah disesuaikan',
                'deactivate DB',
                'Feature --> Main: Menyediakan hasil peninjauan',
                'Main --> User: Menampilkan rekap karyawan yang siap ditinjau',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        case 15:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Memilih laporan Rekap Penilaian & Klasifikasi',
                'activate Main',
                'Main -> Feature: Meminta data rekap penilaian dan klasifikasi',
                'activate Feature',
                'Feature -> DB: Mengambil data rekap penilaian dan klasifikasi',
                'activate DB',
                'DB --> Feature: Data rekap penilaian dan klasifikasi',
                'deactivate DB',
                'Feature --> Main: Menyajikan rekap penilaian dan klasifikasi',
                'Main --> User: Menampilkan hasil rekap dan klasifikasi',
                'User -> Main: Memilih rincian data atau mencetak laporan',
                'Main -> Feature: Meneruskan permintaan peninjauan',
                'Feature -> DB: Mengambil rincian atau data cetak',
                'activate DB',
                'DB --> Feature: Rincian data atau keluaran laporan',
                'deactivate DB',
                'Feature --> Main: Menyediakan hasil peninjauan lanjutan',
                'Main --> User: Menampilkan rincian atau laporan siap cetak',
                'deactivate Feature',
                'deactivate Main',
            ]);
            break;

        default:
            $pumlLines = array_merge($pumlLines, [
                'User -> Main: Melakukan aksi pada halaman',
                'activate Main',
                'Main -> Feature: Meneruskan permintaan proses',
                'activate Feature',
                'Feature -> DB: Mengambil atau menyimpan data',
                'activate DB',
                'DB --> Feature: Hasil proses data',
                'deactivate DB',
                'Feature --> Main: Menyampaikan hasil proses',
                'Main --> User: Menampilkan hasil pada halaman',
                'deactivate Feature',
                'deactivate Main',
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
