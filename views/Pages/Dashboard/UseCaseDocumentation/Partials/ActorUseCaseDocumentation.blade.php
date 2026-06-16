@php
    $actorUseCases = [
        [
            'no' => 1,
            'title' => 'Use Case Pimpinan',
            'actor' => 'Pimpinan',
            'description' => 'Use case pimpinan menggambarkan akses pengguna pimpinan setelah melakukan login, terutama untuk melihat dashboard, membuka laporan, memilih rekap karyawan, serta meninjau rekap penilaian dan klasifikasi.',
            'puml' => [
                '@startuml',
                'left to right direction',
                'actor Pimpinan',
                '',
                'rectangle "Sistem Penilaian Kinerja - Pimpinan" {',
                '  (Logout)',
                '  (Login)',
                '  (Dashboard)',
                '  (Laporan)',
                '  (Rekap Karyawan)',
                '  (Rekap Penilaian & Klasifikasi)',
                '}',
                '',
                'Pimpinan --> (Login)',
                '(Login) --> (Dashboard) : <<include>>',
                '(Login) --> (Laporan) : <<include>>',
                '(Login) --> (Logout) : <<include>>',
                '(Laporan) --> (Rekap Karyawan) : <<include>>',
                '(Laporan) --> (Rekap Penilaian & Klasifikasi) : <<include>>',
                '@enduml',
            ],
        ],
        [
            'no' => 2,
            'title' => 'Use Case Admin',
            'actor' => 'Admin',
            'description' => 'Use case admin menjelaskan hak akses pengelolaan sistem yang lebih luas, mulai dari data karyawan, kriteria, periode, rekap penilaian, analisa kinerja, laporan, hingga pengaturan relasi atasan.',
            'puml' => [
                '@startuml',
                'left to right direction',
                'actor Admin',
                '',
                'rectangle "Sistem Penilaian Kinerja - Admin" {',
                '  (Login)',
                '  (Logout)',
                '  (Laporan)',
                '  (Analisa Kinerja)',
                '  (Rekap Penilaian)',
                '  (Periode Penilaian)',
                '  (Kriteria Penilaian)',
                '  (Data Karyawan)',
                '  (Dashboard)',
                '  (Atur Relasi Atasan)',
                '  (Rekap Karyawan)',
                '  (Rekap Penilaian & Klasifikasi)',
                '}',
                '',
                'Admin --> (Login)',
                '(Login) --> (Logout) : <<include>>',
                '(Login) --> (Dashboard) : <<include>>',
                '(Login) --> (Data Karyawan) : <<include>>',
                '(Login) --> (Kriteria Penilaian) : <<include>>',
                '(Login) --> (Periode Penilaian) : <<include>>',
                '(Login) --> (Rekap Penilaian) : <<include>>',
                '(Login) --> (Analisa Kinerja) : <<include>>',
                '(Login) --> (Laporan) : <<include>>',
                '(Data Karyawan) --> (Atur Relasi Atasan) : <<include>>',
                '(Laporan) --> (Rekap Karyawan) : <<include>>',
                '(Laporan) --> (Rekap Penilaian & Klasifikasi) : <<include>>',
                '@enduml',
            ],
        ],
        [
            'no' => 3,
            'title' => 'Use Case Atasan',
            'actor' => 'Atasan',
            'description' => 'Use case atasan berfokus pada proses penilaian bawahan, peninjauan hasil penilaian, pemantauan daftar bawahan, serta akses dashboard dan logout setelah pengguna berhasil masuk.',
            'puml' => [
                '@startuml',
                'left to right direction',
                'actor Atasan',
                '',
                'rectangle "Sistem Penilaian Kinerja - Atasan" {',
                '  (Login)',
                '  (Logout)',
                '  (Daftar Bawahan)',
                '  (Review Penilaian)',
                '  (Penilaian Bawahan)',
                '  (Dashboard)',
                '}',
                '',
                'Atasan --> (Login)',
                '(Login) --> (Penilaian Bawahan) : <<include>>',
                '(Login) --> (Review Penilaian) : <<include>>',
                '(Login) --> (Daftar Bawahan) : <<include>>',
                '(Login) --> (Dashboard) : <<include>>',
                '(Login) --> (Logout) : <<include>>',
                '@enduml',
            ],
        ],
        [
            'no' => 4,
            'title' => 'Use Case Karyawan',
            'actor' => 'Karyawan',
            'description' => 'Use case karyawan menggambarkan akses pengguna karyawan untuk melihat dashboard, meninjau rekap penilaian pribadi, serta keluar dari sistem setelah proses penggunaan selesai.',
            'puml' => [
                '@startuml',
                'left to right direction',
                'actor Karyawan',
                '',
                'rectangle "Sistem Penilaian Kinerja - Karyawan" {',
                '  (Login)',
                '  (Logout)',
                '  (Rekap Penilaian)',
                '  (Dashboard)',
                '}',
                '',
                'Karyawan --> (Login)',
                '(Login) --> (Rekap Penilaian) : <<include>>',
                '(Login) --> (Dashboard) : <<include>>',
                '(Login) --> (Logout) : <<include>>',
                '@enduml',
            ],
        ],
    ];
@endphp

<div class="alert alert-secondary">
    Empat rancangan use case berikut disusun berdasarkan peran utama pengguna dalam sistem. Pembagian ini membantu memperjelas batas akses setiap aktor sebelum skenario use case dijabarkan secara lebih rinci.
</div>

@foreach ($actorUseCases as $item)
    @php
        $collapseId = 'actor-usecase-' . $item['no'];
    @endphp
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <strong>4.2.1.2.{{ $item['no'] }}. {{ $item['title'] }}</strong>
                <div class="small text-muted">Aktor: {{ $item['actor'] }}</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                Show / Hide
            </button>
        </div>
        <div id="{{ $collapseId }}" class="collapse">
            <div class="card-body">
                <p>{{ $item['description'] }}</p>
                <pre class="bg-dark text-light p-3 rounded mb-0"><code>{{ implode("\n", $item['puml']) }}</code></pre>
            </div>
        </div>
    </div>
@endforeach
