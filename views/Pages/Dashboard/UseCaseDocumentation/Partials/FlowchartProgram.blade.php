@php
    $flowcharts = [
        [
            'no' => 1,
            'title' => 'Flowchart Program Pimpinan',
            'description' => 'Flowchart program pimpinan menggambarkan alur penggunaan sistem oleh pimpinan, dimulai dari proses login, akses dashboard, pembukaan menu laporan, pemilihan jenis rekap, hingga proses logout.',
            'puml' => [
                '@startuml',
                'title Flowchart Program Pimpinan',
                'start',
                ':Pimpinan membuka halaman login;',
                ':Mengisi username dan password;',
                'if (Login valid?) then (Ya)',
                '  :Menampilkan dashboard pimpinan;',
                '  :Membuka menu laporan;',
                '  if (Jenis laporan?) then (Rekap Karyawan)',
                '    :Menampilkan rekap karyawan;',
                '  else (Rekap Penilaian & Klasifikasi)',
                '    :Menampilkan rekap penilaian dan klasifikasi;',
                '  endif',
                '  :Pimpinan memilih logout;',
                '  :Sistem mengakhiri sesi;',
                'else (Tidak)',
                '  :Menampilkan pesan gagal login;',
                'endif',
                'stop',
                '@enduml',
            ],
        ],
        [
            'no' => 2,
            'title' => 'Flowchart Program Admin',
            'description' => 'Flowchart program admin menjelaskan alur kerja pengelolaan sistem, mulai dari login, pemilihan menu utama, pengelolaan data master, analisa kinerja, rekap penilaian, laporan, sampai logout.',
            'puml' => [
                '@startuml',
                'title Flowchart Program Admin',
                'start',
                ':Admin membuka halaman login;',
                ':Mengisi username dan password;',
                'if (Login valid?) then (Ya)',
                '  :Menampilkan dashboard admin;',
                '  if (Menu dipilih?) then (Data Karyawan)',
                '    :Mengelola data karyawan;',
                '    :Mengatur relasi atasan bila diperlukan;',
                '  elseif (Kriteria Penilaian)',
                '    :Mengelola kriteria dan bobot;',
                '  elseif (Periode Penilaian)',
                '    :Mengatur periode penilaian aktif;',
                '  elseif (Rekap Penilaian)',
                '    :Menampilkan rekap hasil penilaian;',
                '  elseif (Analisa Kinerja)',
                '    :Memproses analisa KNN;',
                '  else (Laporan)',
                '    :Menampilkan rekap karyawan atau rekap penilaian dan klasifikasi;',
                '  endif',
                '  :Admin memilih logout;',
                '  :Sistem mengakhiri sesi;',
                'else (Tidak)',
                '  :Menampilkan pesan gagal login;',
                'endif',
                'stop',
                '@enduml',
            ],
        ],
        [
            'no' => 3,
            'title' => 'Flowchart Program Atasan',
            'description' => 'Flowchart program atasan memaparkan alur penilaian kinerja bawahan, mulai dari login, melihat daftar bawahan, mengisi penilaian, melakukan review, hingga keluar dari sistem.',
            'puml' => [
                '@startuml',
                'title Flowchart Program Atasan',
                'start',
                ':Atasan membuka halaman login;',
                ':Mengisi username dan password;',
                'if (Login valid?) then (Ya)',
                '  :Menampilkan dashboard atasan;',
                '  if (Menu dipilih?) then (Penilaian Bawahan)',
                '    :Memilih bawahan yang akan dinilai;',
                '    :Mengisi nilai berdasarkan kriteria;',
                '    :Menyimpan hasil penilaian;',
                '  elseif (Review Penilaian)',
                '    :Menampilkan daftar penilaian yang telah dibuat;',
                '    :Melihat detail hasil penilaian;',
                '  else (Daftar Bawahan)',
                '    :Menampilkan daftar bawahan langsung;',
                '  endif',
                '  :Atasan memilih logout;',
                '  :Sistem mengakhiri sesi;',
                'else (Tidak)',
                '  :Menampilkan pesan gagal login;',
                'endif',
                'stop',
                '@enduml',
            ],
        ],
        [
            'no' => 4,
            'title' => 'Flowchart Program Karyawan',
            'description' => 'Flowchart program karyawan menunjukkan alur akses karyawan dalam melihat dashboard dan rekap penilaian pribadi setelah berhasil login ke sistem.',
            'puml' => [
                '@startuml',
                'title Flowchart Program Karyawan',
                'start',
                ':Karyawan membuka halaman login;',
                ':Mengisi username dan password;',
                'if (Login valid?) then (Ya)',
                '  :Menampilkan dashboard karyawan;',
                '  :Membuka menu rekap penilaian;',
                '  :Menampilkan hasil penilaian pribadi;',
                '  :Karyawan memilih logout;',
                '  :Sistem mengakhiri sesi;',
                'else (Tidak)',
                '  :Menampilkan pesan gagal login;',
                'endif',
                'stop',
                '@enduml',
            ],
        ],
    ];
@endphp

<div class="alert alert-secondary">
    Rancangan flowchart program digunakan untuk memperlihatkan urutan proses utama yang berjalan pada sistem secara ringkas. Melalui flowchart, alur kerja setiap aktor dapat dipahami mulai dari proses login, pemilihan menu, pengolahan data, penyajian informasi, sampai pengguna keluar dari sistem.
</div>

@foreach ($flowcharts as $flowchart)
    @php
        $collapseId = 'flowchart-program-' . $flowchart['no'];
    @endphp
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>4.6.{{ $flowchart['no'] }}. {{ $flowchart['title'] }}</strong>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                Show / Hide
            </button>
        </div>
        <div id="{{ $collapseId }}" class="collapse">
            <div class="card-body">
                <p>{{ $flowchart['description'] }}</p>
                <pre class="bg-dark text-light p-3 rounded mb-0"><code>{{ implode("\n", $flowchart['puml']) }}</code></pre>
            </div>
        </div>
    </div>
@endforeach
