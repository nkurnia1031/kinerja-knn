@php
    $useCases = [
        [
            'no' => 1,
            'title' => 'Login',
            'actor' => 'Pengguna',
            'summary' => 'Proses autentikasi untuk masuk ke sistem sesuai hak akses.',
            'steps' => [
                ['actor' => 'Admin, atasan, karyawan, atau pimpinan membuka halaman login.', 'system' => 'Sistem menampilkan form login kepada pengguna.'],
                ['actor' => 'Pengguna mengisi username dan password pada form login.', 'system' => 'Sistem menerima input kredensial yang dimasukkan pengguna.'],
                ['actor' => 'Pengguna menekan tombol masuk.', 'system' => 'Sistem memverifikasi kecocokan username dan password.'],
                ['actor' => 'Pengguna menunggu proses autentikasi selesai.', 'system' => 'Sistem membuat sesi login dan mengarahkan pengguna ke dashboard sesuai hak aksesnya.'],
            ],
        ],
        [
            'no' => 2,
            'title' => 'Logout',
            'actor' => 'Pengguna',
            'summary' => 'Proses keluar dari sistem untuk mengakhiri sesi aktif.',
            'steps' => [
                ['actor' => 'Admin, atasan, karyawan, atau pimpinan memilih menu logout.', 'system' => 'Sistem menerima permintaan keluar dari pengguna.'],
                ['actor' => 'Pengguna menunggu proses logout dijalankan.', 'system' => 'Sistem menghapus sesi login yang sedang aktif.'],
                ['actor' => 'Pengguna selesai mengakhiri penggunaan sistem.', 'system' => 'Sistem mengarahkan pengguna kembali ke halaman login.'],
            ],
        ],
        [
            'no' => 3,
            'title' => 'Dashboard',
            'actor' => 'Pengguna',
            'summary' => 'Menampilkan ringkasan informasi utama sesuai peran pengguna.',
            'steps' => [
                ['actor' => 'Pengguna berhasil login dan membuka halaman dashboard.', 'system' => 'Sistem memuat data dashboard sesuai peran pengguna.'],
                ['actor' => 'Pengguna melihat ringkasan informasi pada dashboard.', 'system' => 'Sistem menampilkan statistik, grafik, daftar data terbaru, dan menu cepat sesuai hak akses pengguna.'],
                ['actor' => 'Pengguna memilih informasi atau menu yang tersedia di dashboard.', 'system' => 'Sistem menampilkan data ringkasan yang relevan sebagai pintu masuk ke proses berikutnya.'],
            ],
        ],
        [
            'no' => 4,
            'title' => 'Data Karyawan',
            'actor' => 'Admin',
            'summary' => 'Mengelola data karyawan melalui tambah, ubah, hapus, dan filter data.',
            'steps' => [
                ['actor' => 'Admin membuka menu Data Karyawan.', 'system' => 'Sistem menampilkan daftar data karyawan yang tersedia.'],
                ['actor' => 'Admin menambahkan, mengubah, memfilter, atau menghapus data karyawan.', 'system' => 'Sistem menampilkan form dan fitur pengelolaan data karyawan.'],
                ['actor' => 'Admin mengisi atau memperbarui data karyawan lalu menyimpan perubahan.', 'system' => 'Sistem menyimpan perubahan data karyawan ke dalam basis data.'],
                ['actor' => 'Admin melihat hasil pengelolaan data.', 'system' => 'Sistem memperbarui tabel data karyawan sesuai perubahan terakhir.'],
            ],
        ],
        [
            'no' => 5,
            'title' => 'Atur Relasi Atasan',
            'actor' => 'Admin',
            'summary' => 'Menentukan hubungan atasan dan bawahan pada struktur organisasi.',
            'steps' => [
                ['actor' => 'Admin membuka menu Relasi Atasan.', 'system' => 'Sistem menampilkan daftar karyawan dan relasi atasan yang sudah ada.'],
                ['actor' => 'Admin memilih salah satu karyawan yang akan diatur atasannya.', 'system' => 'Sistem menampilkan detail karyawan dan pilihan atasan yang tersedia.'],
                ['actor' => 'Admin memilih atasan yang sesuai lalu menekan tombol simpan.', 'system' => 'Sistem menyimpan hubungan atasan dan bawahan ke dalam data relasi.'],
                ['actor' => 'Admin melihat hasil pengaturan relasi.', 'system' => 'Sistem memperbarui daftar relasi atasan yang tampil pada halaman.'],
            ],
        ],
        [
            'no' => 6,
            'title' => 'Kriteria Penilaian',
            'actor' => 'Admin',
            'summary' => 'Mengelola kriteria dan bobot penilaian kinerja.',
            'steps' => [
                ['actor' => 'Admin membuka menu Kriteria Penilaian.', 'system' => 'Sistem menampilkan daftar kriteria penilaian beserta bobotnya.'],
                ['actor' => 'Admin menambahkan, mengubah, atau menghapus data kriteria.', 'system' => 'Sistem menampilkan form pengelolaan kriteria.'],
                ['actor' => 'Admin menyimpan data kriteria yang telah diisi.', 'system' => 'Sistem menyimpan data kriteria dan bobot ke dalam basis data.'],
                ['actor' => 'Admin meninjau hasil perubahan data.', 'system' => 'Sistem memperbarui daftar kriteria, total bobot, dan status validasi bobot.'],
            ],
        ],
        [
            'no' => 7,
            'title' => 'Periode Penilaian',
            'actor' => 'Admin',
            'summary' => 'Mengatur periode waktu pelaksanaan penilaian kinerja.',
            'steps' => [
                ['actor' => 'Admin membuka menu Periode Penilaian.', 'system' => 'Sistem menampilkan daftar periode penilaian yang tersedia.'],
                ['actor' => 'Admin menambahkan atau mengubah data periode penilaian.', 'system' => 'Sistem menampilkan form pengelolaan periode penilaian.'],
                ['actor' => 'Admin menyimpan data periode penilaian.', 'system' => 'Sistem menyimpan data periode ke dalam basis data.'],
                ['actor' => 'Admin memilih salah satu periode untuk diaktifkan.', 'system' => 'Sistem mengubah status periode terpilih menjadi aktif dan menonaktifkan periode lain.'],
                ['actor' => 'Admin melihat status periode terbaru.', 'system' => 'Sistem menampilkan progress penilaian dan status periode yang telah diperbarui.'],
            ],
        ],
        [
            'no' => 8,
            'title' => 'Rekap Penilaian',
            'actor' => 'Admin',
            'summary' => 'Menampilkan rekapitulasi hasil penilaian kinerja karyawan.',
            'steps' => [
                ['actor' => 'Admin membuka menu Rekap Penilaian.', 'system' => 'Sistem menampilkan data rekap hasil penilaian kinerja.'],
                ['actor' => 'Admin memilih filter periode penilaian.', 'system' => 'Sistem memuat ulang data rekap berdasarkan periode yang dipilih.'],
                ['actor' => 'Admin memilih salah satu data rekap untuk melihat detail.', 'system' => 'Sistem mengarahkan pengguna ke halaman detail rekap penilaian.'],
                ['actor' => 'Admin meninjau hasil rekap penilaian.', 'system' => 'Sistem menampilkan total nilai, klasifikasi penilaian, klasifikasi KNN, dan informasi penilai.'],
            ],
        ],
        [
            'no' => 9,
            'title' => 'Analisa Kinerja',
            'actor' => 'Admin',
            'summary' => 'Melakukan analisa klasifikasi kinerja menggunakan KNN.',
            'steps' => [
                ['actor' => 'Admin membuka menu Analisa Kinerja.', 'system' => 'Sistem memuat data analisa awal, data training, dan riwayat penilaian.'],
                ['actor' => 'Admin menentukan parameter analisa seperti nilai K, metode jarak, normalisasi, dan periode.', 'system' => 'Sistem menerima parameter analisa yang dipilih pengguna.'],
                ['actor' => 'Admin menjalankan proses analisa.', 'system' => 'Sistem memproses klasifikasi KNN berdasarkan data training dan data penilaian.'],
                ['actor' => 'Admin meninjau hasil analisa yang ditampilkan.', 'system' => 'Sistem menampilkan hasil klasifikasi, statistik distribusi, dan visualisasi analisa kinerja.'],
                ['actor' => 'Admin menyimpan hasil analisa bila diperlukan.', 'system' => 'Sistem menyimpan hasil analisa ke dalam database dan memperbarui daftar analisa.'],
            ],
        ],
        [
            'no' => 10,
            'title' => 'Penilaian Bawahan',
            'actor' => 'Atasan',
            'summary' => 'Melakukan penilaian terhadap karyawan bawahan pada periode aktif.',
            'steps' => [
                ['actor' => 'Atasan membuka menu Penilaian Bawahan.', 'system' => 'Sistem menampilkan informasi periode penilaian aktif dan daftar bawahan yang dapat dinilai.'],
                ['actor' => 'Atasan memilih salah satu karyawan bawahan.', 'system' => 'Sistem menampilkan form penilaian kinerja untuk karyawan yang dipilih.'],
                ['actor' => 'Atasan mengisi nilai pada setiap kriteria penilaian.', 'system' => 'Sistem menghitung total nilai berdasarkan bobot masing-masing kriteria.'],
                ['actor' => 'Atasan menambahkan catatan dan meninjau hasil penilaian.', 'system' => 'Sistem menampilkan klasifikasi otomatis dan klasifikasi akhir hasil penilaian.'],
                ['actor' => 'Atasan menekan tombol simpan penilaian.', 'system' => 'Sistem menyimpan hasil penilaian karyawan pada periode aktif.'],
            ],
        ],
        [
            'no' => 11,
            'title' => 'Review Penilaian',
            'actor' => 'Atasan / Karyawan',
            'summary' => 'Meninjau kembali hasil penilaian yang sudah tersimpan.',
            'steps' => [
                ['actor' => 'Atasan atau karyawan membuka menu Review Penilaian.', 'system' => 'Sistem menampilkan daftar data penilaian yang tersedia.'],
                ['actor' => 'Pengguna memilih salah satu data penilaian untuk ditinjau.', 'system' => 'Sistem memuat detail penilaian yang dipilih.'],
                ['actor' => 'Pengguna melihat rincian hasil penilaian.', 'system' => 'Sistem menampilkan nilai per kriteria, total nilai, klasifikasi, periode, dan catatan penilaian.'],
                ['actor' => 'Pengguna menutup detail setelah selesai meninjau.', 'system' => 'Sistem menampilkan kembali daftar penilaian pada halaman review.'],
            ],
        ],
        [
            'no' => 12,
            'title' => 'Daftar Bawahan',
            'actor' => 'Atasan',
            'summary' => 'Menampilkan daftar bawahan langsung yang berada di bawah tanggung jawab atasan.',
            'steps' => [
                ['actor' => 'Atasan membuka menu Daftar Bawahan.', 'system' => 'Sistem menampilkan daftar seluruh bawahan langsung beserta ringkasan informasinya.'],
                ['actor' => 'Atasan meninjau status, jabatan, pekerjaan, dan nilai terakhir bawahan.', 'system' => 'Sistem menampilkan detail singkat setiap bawahan pada kartu daftar bawahan.'],
                ['actor' => 'Atasan memilih tindakan lanjutan, seperti menilai bawahan atau melihat riwayat penilaian.', 'system' => 'Sistem mengarahkan pengguna ke halaman penilaian bawahan atau riwayat penilaian sesuai pilihan.'],
            ],
        ],
        [
            'no' => 13,
            'title' => 'Laporan',
            'actor' => 'Admin / Pimpinan',
            'summary' => 'Menyajikan laporan terpadu untuk data karyawan dan rekap penilaian.',
            'steps' => [
                ['actor' => 'Admin atau pimpinan membuka menu Laporan.', 'system' => 'Sistem menampilkan halaman laporan yang memuat pilihan Rekap Karyawan dan Rekap Penilaian & Klasifikasi.'],
                ['actor' => 'Pengguna memilih jenis laporan yang ingin digunakan.', 'system' => 'Sistem menyesuaikan widget, filter, dan tabel preview sesuai jenis laporan yang dipilih.'],
                ['actor' => 'Pengguna mengisi filter yang diperlukan lalu menampilkan data laporan.', 'system' => 'Sistem memuat preview laporan berdasarkan filter yang dipilih pengguna.'],
                ['actor' => 'Pengguna mencetak laporan dari halaman tersebut.', 'system' => 'Sistem menampilkan laporan cetak lengkap dengan kop dan tanda tangan.'],
            ],
        ],
        [
            'no' => 14,
            'title' => 'Rekap Karyawan',
            'actor' => 'Admin / Pimpinan',
            'summary' => 'Menampilkan rekap data karyawan berdasarkan filter yang dipilih.',
            'steps' => [
                ['actor' => 'Admin atau pimpinan memilih jenis laporan Rekap Karyawan pada halaman Laporan.', 'system' => 'Sistem menampilkan filter dan preview tabel khusus untuk rekap data karyawan.'],
                ['actor' => 'Pengguna memfilter data berdasarkan status, role, pekerjaan, atau pencarian tertentu.', 'system' => 'Sistem menampilkan daftar karyawan sesuai filter yang dipilih.'],
                ['actor' => 'Pengguna meninjau rekap data karyawan atau mencetaknya.', 'system' => 'Sistem menyajikan rekap data karyawan secara terstruktur sebagai bahan pemantauan dan dokumentasi.'],
            ],
        ],
        [
            'no' => 15,
            'title' => 'Rekap Penilaian & Klasifikasi',
            'actor' => 'Admin / Pimpinan',
            'summary' => 'Menampilkan rekap hasil penilaian dan klasifikasi KNN sebagai dasar keputusan.',
            'steps' => [
                ['actor' => 'Admin atau pimpinan memilih jenis laporan Rekap Penilaian & Klasifikasi pada halaman Laporan.', 'system' => 'Sistem menampilkan widget keputusan, filter, dan tabel preview rekap penilaian serta klasifikasi.'],
                ['actor' => 'Pengguna memilih periode, kata kunci, atau klasifikasi KNN yang ingin ditinjau.', 'system' => 'Sistem menyaring dan menampilkan data rekap sesuai parameter yang dipilih.'],
                ['actor' => 'Pengguna meninjau nilai akhir, klasifikasi penilaian, klasifikasi KNN, dan kategori keputusan.', 'system' => 'Sistem menampilkan data yang mendukung penentuan prioritas apresiasi, penguatan, dan pembinaan karyawan.'],
                ['actor' => 'Pengguna memilih detail data atau mencetak hasil laporan.', 'system' => 'Sistem menampilkan detail rekap dalam modal dan menyediakan keluaran laporan cetak yang terstruktur.'],
            ],
        ],
    ];

    $definisiUseCase = [
        [1, 'Login', 'Admin, Atasan, Karyawan, Pimpinan', 'Proses autentikasi pengguna untuk masuk ke dalam sistem dengan menggunakan kredensial yang valid.'],
        [2, 'Logout', 'Admin, Atasan, Karyawan, Pimpinan', 'Proses keluar dari sistem untuk mengakhiri sesi penggunaan secara aman.'],
        [3, 'Dashboard', 'Admin, Atasan, Karyawan, Pimpinan', 'Menampilkan informasi ringkasan terkait aktivitas dan data dalam sistem sesuai dengan hak akses masing-masing pengguna.'],
        [4, 'Data Karyawan', 'Admin', 'Mengelola data karyawan yang meliputi penambahan, pengubahan, dan penghapusan data.'],
        [5, 'Atur Relasi Atasan', 'Admin', 'Menentukan hubungan antara atasan dan bawahan dalam struktur organisasi.'],
        [6, 'Kriteria Penilaian', 'Admin', 'Mengelola kriteria yang digunakan sebagai dasar dalam proses penilaian kinerja karyawan.'],
        [7, 'Periode Penilaian', 'Admin', 'Mengatur periode waktu pelaksanaan penilaian kinerja.'],
        [8, 'Rekap Penilaian', 'Admin', 'Mengolah dan menampilkan rekapitulasi hasil penilaian kinerja karyawan.'],
        [9, 'Analisa Kinerja', 'Admin', 'Melakukan analisis terhadap hasil penilaian kinerja untuk memperoleh informasi yang mendukung evaluasi.'],
        [10, 'Penilaian Bawahan', 'Atasan', 'Melakukan proses penilaian terhadap karyawan yang menjadi bawahannya berdasarkan kriteria yang telah ditentukan.'],
        [11, 'Review Penilaian', 'Atasan, Karyawan', 'Melakukan peninjauan terhadap hasil penilaian yang telah dilakukan.'],
        [12, 'Daftar Bawahan', 'Atasan', 'Menampilkan daftar karyawan yang berada di bawah tanggung jawab atasan.'],
        [13, 'Laporan', 'Admin, Pimpinan', 'Menyajikan halaman laporan terpadu yang memuat pilihan Rekap Karyawan dan Rekap Penilaian & Klasifikasi.'],
        [14, 'Rekap Karyawan', 'Admin, Pimpinan', 'Menampilkan rekap data karyawan secara keseluruhan melalui pilihan laporan Rekap Karyawan.'],
        [15, 'Rekap Penilaian & Klasifikasi', 'Admin, Pimpinan', 'Menyajikan rekap hasil penilaian beserta klasifikasi KNN sebagai dasar pendukung keputusan apresiasi dan pembinaan karyawan.'],
    ];
@endphp

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.2.1.2. Use Case Berdasarkan Aktor</h5>
        </div>
        <div class="card-body">
            @include('Pages.Dashboard.UseCaseDocumentation.Partials.ActorUseCaseDocumentation')
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.2.1.3. Skenario Use Case</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                Bagian ini dibuat dalam format collapse agar tiap skenario use case bisa dibaca per kasus tanpa membuat halaman terlalu panjang.
            </div>

            @foreach ($useCases as $useCase)
                @include('Pages.Dashboard.UseCaseDocumentation.Partials.UseCaseCollapse', [
                    'useCase' => $useCase,
                ])
            @endforeach
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.2.1.4. Definisi Use Case</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 20%;">Use Case</th>
                            <th style="width: 25%;">Aktor</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($definisiUseCase as $item)
                            <tr>
                                <td>{{ $item[0] }}</td>
                                <td>{{ $item[1] }}</td>
                                <td>{{ $item[2] }}</td>
                                <td>{{ $item[3] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.2.2. Activity Diagram</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary">
                Setiap activity diagram di bawah ini dibuat dengan syntax PlantUML model swimlane, memakai lane aktor dan sistem yang disusun vertikal agar alurnya lebih mudah dibaca, termasuk decision node pada proses yang bercabang.
            </div>

            @foreach ($useCases as $useCase)
                @include('Pages.Dashboard.UseCaseDocumentation.Partials.ActivityDiagram', [
                    'useCase' => $useCase,
                ])
            @endforeach
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.2.3. Sequence Diagram</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary">
                <i>Sequence Diagram</i> berikut menggambarkan interaksi antarkomponen antara pengguna, <i>View</i>, <i>Controller</i>, dan <i>Database</i> pada masing-masing use case sebagaimana ditunjukkan pada gambar di bawah.
            </div>

            @foreach ($useCases as $useCase)
                @include('Pages.Dashboard.UseCaseDocumentation.Partials.SequenceDiagram', [
                    'useCase' => $useCase,
                ])
            @endforeach
        </div>
    </div>
</div>

<div class="col-12 mt-4 mb-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.2.4. Class Diagram</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary">
                <i>Class Diagram</i> merupakan rancangan struktural yang menggambarkan kelas-kelas utama dalam sistem penilaian kinerja beserta atribut, metode, dan relasi antarkelas yang digunakan untuk mendukung proses pengelolaan data, penilaian, dan analisis sebagaimana ditunjukkan pada gambar di bawah.
            </div>

            @include('Pages.Dashboard.UseCaseDocumentation.Partials.ClassDiagram')
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.4. Rancangan Struktur Program</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary">
                Rancangan struktur program merupakan desain atau bentuk gambaran dari sistem yang menunjukkan hubungan antarbagian menu secara hierarkis, mulai dari autentikasi, dashboard, data master, penilaian kinerja, laporan dan analisis, hingga logout sebagaimana ditunjukkan pada gambar di bawah.
            </div>

            @include('Pages.Dashboard.UseCaseDocumentation.Partials.ProgramStructure')
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.5. Rancangan Database</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary">
                Database yang digunakan pada sistem adalah <code>2026_04_kinerja_knn</code>.
            </div>

            @include('Pages.Dashboard.UseCaseDocumentation.Partials.DatabaseDocumentation')
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.6. Rancangan Flowchart Program</h5>
        </div>
        <div class="card-body">
            @include('Pages.Dashboard.UseCaseDocumentation.Partials.FlowchartProgram')
        </div>
    </div>
</div>

<div class="col-12 mt-4 mb-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.7. Rancangan Input &amp; Output</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary">
                Rancangan ini menampilkan mockup visual halaman <i>Login</i> serta halaman-halaman use case lain yang sudah didefinisikan pada sistem, dengan tampilan yang mengikuti struktur antarmuka aplikasi yang telah berjalan. Setiap field menggunakan format <code>X(n)</code> untuk data teks dan <code>9(n)</code> untuk data numerik sesuai batasan pada struktur database.
            </div>

            @include('Pages.Dashboard.UseCaseDocumentation.Partials.InputOutputDocumentation')
        </div>
    </div>
</div>

<div class="col-12 mt-4 mb-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">4.8. Implementasi Sistem</h5>
        </div>
        <div class="card-body">
            @include('Pages.Dashboard.UseCaseDocumentation.Partials.ImplementationSystem')
        </div>
    </div>
</div>

<div class="col-12 mt-4 mb-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold text-primary">BAB V KESIMPULAN DAN SARAN</h5>
        </div>
        <div class="card-body">
            <div class="card mb-4 border-left-primary shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 font-weight-bold text-primary">5.1 Kesimpulan</h6>
                </div>
                <div class="card-body">
                    <p>
                        Berdasarkan hasil perancangan dan implementasi yang telah dilakukan, dapat disimpulkan bahwa penelitian ini berhasil menghasilkan mekanisme penilaian kinerja karyawan yang lebih representatif karena klasifikasi tidak hanya ditentukan berdasarkan nilai rata-rata, tetapi juga mempertimbangkan berbagai kriteria penilaian yang diolah secara terstruktur. Dengan demikian, hasil evaluasi yang diperoleh dapat memberikan gambaran kinerja karyawan yang lebih objektif dan mendukung proses penentuan klasifikasi secara lebih tepat.
                    </p>
                    <p>
                        Penelitian ini juga berhasil membangun sistem berbasis web yang menerapkan metode <i>k-nearest neighbor</i> (KNN) untuk membantu proses klasifikasi kinerja karyawan. Melalui sistem tersebut, pengguna dapat melakukan pengelolaan data karyawan, pengaturan kriteria penilaian, pelaksanaan penilaian, analisa klasifikasi, serta penyajian laporan hasil penilaian dalam satu platform yang terintegrasi. Hal ini menunjukkan bahwa tujuan penelitian untuk membangun sistem klasifikasi kinerja berbasis web telah tercapai.
                    </p>
                    <p class="mb-0">
                        Selain mendukung proses klasifikasi, sistem yang dibangun juga telah mendigitalisasi pengelolaan data penilaian kinerja karyawan sehingga proses administrasi menjadi lebih terstruktur, efisien, dan mudah ditelusuri kembali. Penerapan sistem ini mampu mengurangi ketergantungan terhadap berkas fisik maupun file yang tersebar, sehingga kegiatan pencatatan, penyimpanan, dan pencarian data penilaian dapat dilakukan dengan lebih efektif dan efisien sesuai dengan tujuan penelitian yang telah ditetapkan.
                    </p>
                </div>
            </div>

            <div class="card border-left-success shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 font-weight-bold text-success">5.2 Saran</h6>
                </div>
                <div class="card-body">
                    <p>
                        Pengembangan sistem pada tahap selanjutnya dapat diarahkan pada penyempurnaan proses klasifikasi agar hasil penilaian menjadi semakin akurat dan komprehensif, misalnya melalui penambahan data training, pengujian beberapa nilai parameter K, atau perbandingan dengan metode klasifikasi lain. Upaya tersebut penting dilakukan agar sistem dapat memberikan rekomendasi klasifikasi yang semakin baik dan relevan terhadap kondisi nyata di lingkungan kerja.
                    </p>
                    <p class="mb-0">
                        Di samping itu, sistem berbasis web ini masih dapat dikembangkan dengan menambahkan fitur notifikasi, riwayat aktivitas pengguna, dashboard analitik yang lebih mendalam, serta integrasi dengan sistem kepegawaian lainnya. Pengembangan tersebut diharapkan dapat semakin memperkuat fungsi sistem tidak hanya sebagai media digitalisasi penilaian, tetapi juga sebagai sarana pendukung keputusan yang lebih efektif dalam pengelolaan kinerja karyawan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
