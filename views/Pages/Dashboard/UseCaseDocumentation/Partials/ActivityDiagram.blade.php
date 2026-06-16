@php
    $diagramId = 'activity-diagram-' . $useCase['no'];
    $title = $useCase['title'];
    $figureNumber = '4.' . ($useCase['no'] + 4);
    $activityNarratives = [
        1 => '<i>Activity Diagram</i> <i>Login</i> menyajikan alur autentikasi pengguna secara sistematis yang dimulai dari pengisian <i>username</i> dan <i>password</i>, dilanjutkan dengan proses verifikasi kredensial oleh sistem, kemudian menghasilkan keputusan berupa penolakan akses disertai pesan kegagalan login atau pembentukan sesi dan pengalihan pengguna ke <i>dashboard</i> sesuai hak akses.',
        2 => '<i>Activity Diagram</i> <i>Logout</i> menjelaskan prosedur pengakhiran sesi penggunaan sistem yang diawali ketika pengguna memilih menu <i>logout</i>, kemudian sistem menghapus sesi login yang masih aktif dan mengarahkan pengguna kembali ke halaman login sebagai bentuk pengamanan akses.',
        3 => '<i>Activity Diagram</i> <i>Dashboard</i> menggambarkan alur ketika pengguna membuka halaman utama sistem, di mana sistem memuat data dan komponen tampilan sesuai hak akses, menampilkan ringkasan informasi, tabel, dan grafik, kemudian memfasilitasi pengguna untuk meninjau informasi awal dan memilih menu lanjutan yang diperlukan.',
        4 => '<i>Activity Diagram</i> <i>Data Karyawan</i> menguraikan proses pengelolaan data karyawan yang dimulai dari pembukaan menu terkait, penampilan daftar data oleh sistem, pemilihan aksi oleh pengguna berupa tambah, ubah, hapus, atau filter, hingga respons sistem dalam menampilkan formulir, memproses konfirmasi, menyimpan perubahan, atau menampilkan hasil penyaringan data.',
        5 => '<i>Activity Diagram</i> <i>Relasi Atasan</i> menjelaskan alur penetapan hubungan struktural antara karyawan dan atasan, dimulai dari penampilan daftar karyawan beserta relasi yang telah tersedia, dilanjutkan dengan pemilihan karyawan oleh pengguna, penampilan opsi atasan oleh sistem, dan diakhiri dengan penyimpanan relasi yang telah ditetapkan.',
        6 => '<i>Activity Diagram</i> <i>Kriteria Penilaian</i> memaparkan proses pengelolaan kriteria yang digunakan dalam penilaian kinerja, yaitu ketika pengguna membuka menu terkait, sistem menampilkan daftar kriteria beserta formulir pengelolaan, pengguna melakukan tambah, ubah, atau hapus data, lalu sistem memvalidasi masukan, menyimpan perubahan, dan memperbarui total bobot penilaian.',
        7 => '<i>Activity Diagram</i> <i>Periode Penilaian</i> menggambarkan mekanisme pengelolaan periode penilaian yang melibatkan penampilan daftar periode oleh sistem, penambahan atau perubahan data oleh pengguna, serta keputusan sistem untuk hanya menyimpan perubahan atau sekaligus menonaktifkan periode lain dan mengaktifkan periode yang dipilih.',
        8 => '<i>Activity Diagram</i> <i>Rekap Penilaian</i> menjelaskan alur peninjauan data rekap penilaian yang dimulai dari pembukaan menu rekap, penampilan data oleh sistem, pemilihan filter periode oleh pengguna, dan keputusan sistem untuk memuat ulang data berdasarkan periode tertentu atau menampilkan seluruh data rekap apabila tidak ada filter yang dipilih.',
        9 => '<i>Activity Diagram</i> <i>Analisa Kinerja</i> menerangkan alur analisis berbasis metode K-Nearest Neighbor, dimulai ketika sistem memuat data <i>training</i> dan riwayat penilaian, kemudian pengguna menentukan parameter analisis seperti nilai K, metode jarak, dan normalisasi, sehingga sistem dapat memvalidasi parameter dan menampilkan hasil klasifikasi atau pesan kesalahan yang sesuai.',
        10 => '<i>Activity Diagram</i> <i>Penilaian Bawahan</i> menguraikan proses pemberian nilai kepada bawahan yang dimulai dari penampilan periode aktif dan daftar bawahan oleh sistem, dilanjutkan dengan pemilihan bawahan serta pengisian nilai kriteria oleh pengguna, kemudian sistem memeriksa kelengkapan data untuk menghitung total nilai dan menampilkan klasifikasi akhir atau memberikan pesan validasi.',
        11 => '<i>Activity Diagram</i> <i>Review Penilaian</i> menjelaskan alur pemeriksaan kembali data penilaian, dimulai ketika sistem menampilkan daftar penilaian, pengguna memilih salah satu data yang ingin ditinjau, lalu sistem memutuskan apakah rincian data dapat ditampilkan atau harus memberikan informasi bahwa data yang dimaksud tidak ditemukan.',
        12 => '<i>Activity Diagram</i> <i>Daftar Bawahan</i> menggambarkan proses peninjauan bawahan yang berada dalam tanggung jawab pengguna, ketika sistem menampilkan kartu daftar bawahan beserta ringkasan informasinya, pengguna menelaah data tersebut dan memilih aksi lanjutan, kemudian sistem mengarahkan ke halaman penilaian atau riwayat sesuai kebutuhan.',
        13 => '<i>Activity Diagram</i> <i>Laporan</i> memaparkan alur umum pengelolaan laporan, dimulai dari penampilan pilihan jenis laporan oleh sistem, pemilihan jenis laporan oleh pengguna, penyesuaian tampilan filter berdasarkan jenis laporan yang dipilih, hingga pengisian filter dan penampilan hasil laporan beserta opsi cetak.',
        14 => '<i>Activity Diagram</i> <i>Rekap Karyawan</i> menjelaskan proses penyusunan laporan rekap karyawan yang diawali dengan penampilan filter oleh sistem, dilanjutkan dengan pengisian status, <i>role</i>, pekerjaan, atau kata kunci oleh pengguna, kemudian sistem menyaring data sesuai kriteria yang diberikan atau menampilkan seluruh data karyawan apabila filter tidak diisi.',
        15 => '<i>Activity Diagram</i> <i>Rekap Penilaian &amp; Klasifikasi</i> menggambarkan alur penyajian laporan hasil penilaian dan klasifikasi, ketika sistem menampilkan filter laporan, pengguna menentukan periode atau klasifikasi KNN, lalu sistem menampilkan rekap pada halaman atau menghasilkan keluaran yang siap dicetak sesuai kebutuhan pengguna.',
    ];
    $activityDescription = ($activityNarratives[$useCase['no']] ?? ('<i>Activity Diagram</i> ' . $title . ' menjelaskan alur proses secara terstruktur dengan menunjukkan interaksi antara pengguna dan sistem pada fitur ' . strtolower($title) . '.')) . ' Alur tersebut ditunjukkan pada Gambar ' . $figureNumber . '.';
    $pumlLines = [
        '@startuml',
        "title Activity Diagram - {$title}",
        'skinparam SwimlaneBorderColor black',
        'skinparam SwimlaneBorderThickness 2',
        'skinparam SwimlaneTitleBackgroundColor #2C3E50',
        'skinparam SwimlaneTitleFontColor white',
        'skinparam SwimlaneTitleFontStyle bold',
        'skinparam ActivityDiamondBorderColor black',
        'skinparam ActivityDiamondBackgroundColor #F9E79F',
        'skinparam ArrowColor #34495E',
        '',
        '|Pengguna|',
        'start',
    ];

    switch ($useCase['no']) {
        case 1:
            $pumlLines = array_merge($pumlLines, [
                'repeat',
                '  :Mengisi username dan password;',
                '  |Sistem|',
                '  :Memverifikasi kredensial;',
                '  if (Autentikasi berhasil?) then (Tidak)',
                '    :Menampilkan pesan gagal login;',
                '  else (Ya)',
                '    :Membuat sesi login;',
                '    :Mengarahkan ke dashboard\\nsesuai hak akses;',
                '    stop',
                '  endif',
                '  |Pengguna|',
                'repeat while (password atau username anda salah)',
            ]);
            break;

        case 2:
            $pumlLines = array_merge($pumlLines, [
                ':Memilih menu logout;',
                '|Sistem|',
                ':Menghapus sesi login aktif;',
                ':Mengarahkan ke halaman login;',
                'stop',
            ]);
            break;

        case 3:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka halaman dashboard;',
                '|Sistem|',
                ':Memuat data dashboard sesuai hak akses pengguna;',
                ':Menampilkan widget ringkasan, tabel terbaru, dan grafik;',
                '|Pengguna|',
                ':Melihat dashboard dan memilih menu yang tersedia;',
                '|Sistem|',
                ':Membuka halaman atau detail sesuai pilihan pengguna;',
                'stop',
            ]);
            break;

        case 4:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Data Karyawan;',
                '|Sistem|',
                ':Menampilkan daftar data karyawan;',
                '|Pengguna|',
                ':Memilih aksi tambah, ubah, hapus, atau filter;',
                '|Sistem|',
                'if (Aksi yang dipilih?) then (Tambah/Ubah)',
                '  :Menampilkan form data karyawan;',
                '  |Pengguna|',
                '  :Mengisi data dan menekan simpan;',
                '  |Sistem|',
                '  :Menyimpan perubahan ke database;',
                'elseif (Hapus)',
                '  :Menampilkan konfirmasi hapus;',
                '  |Pengguna|',
                '  :Menyetujui penghapusan;',
                '  |Sistem|',
                '  :Menghapus data karyawan;',
                'else (Filter)',
                '  :Menerapkan filter pencarian;',
                '  :Menampilkan hasil filter;',
                'endif',
                'stop',
            ]);
            break;

        case 5:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Relasi Atasan;',
                '|Sistem|',
                ':Menampilkan daftar karyawan dan relasi yang ada;',
                '|Pengguna|',
                ':Memilih karyawan yang akan diatur relasinya;',
                '|Sistem|',
                ':Menampilkan detail karyawan dan daftar atasan yang tersedia;',
                '|Pengguna|',
                ':Menetapkan atasan lalu menekan simpan;',
                '|Sistem|',
                ':Menyimpan relasi dan memperbarui daftar relasi;',
                'stop',
            ]);
            break;

        case 6:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Kriteria Penilaian;',
                '|Sistem|',
                ':Menampilkan daftar kriteria dan form pengelolaan;',
                '|Pengguna|',
                ':Menambah, mengubah, atau menghapus kriteria;',
                '|Sistem|',
                ':Memvalidasi data dan menyimpan perubahan;',
                ':Memperbarui daftar kriteria dan total bobot;',
                'stop',
            ]);
            break;

        case 7:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Periode Penilaian;',
                '|Sistem|',
                ':Menampilkan daftar periode;',
                '|Pengguna|',
                ':Menambahkan atau mengubah periode;',
                '|Sistem|',
                ':Menyimpan data periode;',
                'if (Periode diaktifkan?) then (Ya)',
                '  :Menonaktifkan periode lain;',
                '  :Mengaktifkan periode terpilih;',
                'else (Tidak)',
                '  :Hanya menyimpan perubahan data;',
                'endif',
                'stop',
            ]);
            break;

        case 8:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Rekap Penilaian;',
                '|Sistem|',
                ':Menampilkan data rekap;',
                '|Pengguna|',
                ':Memilih filter periode;',
                '|Sistem|',
                'if (Periode dipilih?) then (Ya)',
                '  :Memuat ulang data berdasarkan periode;',
                'else (Tidak)',
                '  :Menampilkan seluruh data rekap;',
                'endif',
                'stop',
            ]);
            break;

        case 9:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Analisa Kinerja;',
                '|Sistem|',
                ':Memuat data training dan riwayat penilaian;',
                '|Pengguna|',
                ':Menentukan nilai K, metode jarak, dan normalisasi;',
                '|Sistem|',
                'if (Parameter valid?) then (Ya)',
                '  :Memproses klasifikasi KNN;',
                '  :Menampilkan hasil analisa;',
                'else (Tidak)',
                '  :Menampilkan pesan validasi parameter;',
                'endif',
                'stop',
            ]);
            break;

        case 10:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Penilaian Bawahan;',
                '|Sistem|',
                ':Menampilkan periode aktif dan daftar bawahan;',
                '|Pengguna|',
                ':Memilih bawahan dan mengisi nilai kriteria;',
                '|Sistem|',
                'if (Nilai lengkap?) then (Ya)',
                '  :Menghitung total nilai;',
                '  :Menampilkan klasifikasi akhir;',
                'else (Tidak)',
                '  :Menampilkan pesan validasi data;',
                'endif',
                'stop',
            ]);
            break;

        case 11:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Review Penilaian;',
                '|Sistem|',
                ':Menampilkan daftar penilaian;',
                '|Pengguna|',
                ':Memilih salah satu data penilaian;',
                '|Sistem|',
                'if (Data detail ditemukan?) then (Ya)',
                '  :Menampilkan rincian penilaian;',
                'else (Tidak)',
                '  :Menampilkan pesan data tidak ditemukan;',
                'endif',
                'stop',
            ]);
            break;

        case 12:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Daftar Bawahan;',
                '|Sistem|',
                ':Menampilkan kartu daftar bawahan dan ringkasan data;',
                '|Pengguna|',
                ':Meninjau daftar bawahan yang berada di bawah tanggung jawabnya;',
                '|Sistem|',
                ':Menampilkan detail singkat dan aksi penilaian atau riwayat;',
                '|Pengguna|',
                ':Memilih aksi lanjutan;',
                '|Sistem|',
                ':Mengarahkan ke halaman penilaian bawahan atau riwayat penilaian;',
                'stop',
            ]);
            break;

        case 13:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka menu Laporan;',
                '|Sistem|',
                ':Menampilkan pilihan jenis laporan;',
                '|Pengguna|',
                ':Memilih jenis laporan;',
                '|Sistem|',
                'if (Jenis laporan?) then (Rekap Karyawan)',
                '  :Menampilkan filter rekap karyawan;',
                'elseif (Rekap Penilaian & Klasifikasi)',
                '  :Menampilkan filter rekap penilaian;',
                'endif',
                '|Pengguna|',
                ':Mengisi filter dan menampilkan data;',
                '|Sistem|',
                ':Menampilkan hasil laporan dan opsi cetak;',
                'stop',
            ]);
            break;

        case 14:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka laporan Rekap Karyawan;',
                '|Sistem|',
                ':Menampilkan filter rekap karyawan;',
                '|Pengguna|',
                ':Mengisi status, role, pekerjaan, atau kata kunci;',
                '|Sistem|',
                'if (Filter diisi?) then (Ya)',
                '  :Menyaring data karyawan;',
                'else (Tidak)',
                '  :Menampilkan seluruh data karyawan;',
                'endif',
                'stop',
            ]);
            break;

        case 15:
            $pumlLines = array_merge($pumlLines, [
                ':Membuka laporan Rekap Penilaian & Klasifikasi;',
                '|Sistem|',
                ':Menampilkan filter laporan;',
                '|Pengguna|',
                ':Memilih periode atau klasifikasi KNN;',
                '|Sistem|',
                'if (Laporan ingin dicetak?) then (Ya)',
                '  :Menampilkan detail rekap dan keluaran cetak;',
                'else (Tidak)',
                '  :Menampilkan rekap pada halaman;',
                'endif',
                'stop',
            ]);
            break;

        default:
            foreach ($useCase['steps'] as $step) {
                $pumlLines[] = ':Langkah use case;';
            }
            $pumlLines[] = 'stop';
            break;
    }

    $pumlLines[] = '@enduml';
@endphp
<div class="card mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <strong>4.2.2.{{ $useCase['no'] }} Activity Diagram {{ $useCase['title'] }}</strong>
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $diagramId }}" aria-expanded="false" aria-controls="{{ $diagramId }}">
            Show / Hide
        </button>
    </div>
    <div id="{{ $diagramId }}" class="collapse">
        <div class="card-body">
            <div class="alert alert-info">
                {!! $activityDescription !!}
            </div>
            <pre class="bg-dark text-light p-3 rounded mb-0"><code>{{ implode("\n", $pumlLines) }}</code></pre>
        </div>
    </div>
</div>
