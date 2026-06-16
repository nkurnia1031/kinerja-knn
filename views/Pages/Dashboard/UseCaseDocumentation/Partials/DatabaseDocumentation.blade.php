@php
    $tables = [
        [
            'no' => 1,
            'nama' => 'Users',
            'database' => '2026_04_kinerja_knn',
            'tabel' => 'karyawan',
            'primary' => 'id',
            'keterangan' => 'Tabel pengguna sistem yang menyimpan data autentikasi dan informasi dasar karyawan.',
            'fields' => [
                ['id', 'int unsigned', '10', 'Primary key'],
                ['nama', 'varchar', '100', 'Nama pengguna'],
                ['jabatan', 'varchar', '100', 'Jabatan pengguna'],
                ['pekerjaan', 'text', '-', 'Deskripsi pekerjaan'],
                ['tanggal_bergabung', 'date', '-', 'Tanggal bergabung'],
                ['status', 'enum', '-', 'Status aktif/nonaktif/cuti/resign'],
                ['role', 'enum', '-', 'Hak akses pengguna'],
                ['username', 'varchar', '50', 'Username login'],
                ['password', 'varchar', '255', 'Password login'],
                ['created_at', 'timestamp', '-', 'Waktu dibuat'],
                ['updated_at', 'timestamp', '-', 'Waktu diubah'],
            ],
        ],
        [
            'no' => 2,
            'nama' => 'Kriteria Penilaian',
            'database' => '2026_04_kinerja_knn',
            'tabel' => 'kriteria',
            'primary' => 'id',
            'keterangan' => 'Tabel master kriteria dan bobot penilaian kinerja.',
            'fields' => [
                ['id', 'int unsigned', '10', 'Primary key'],
                ['kode', 'varchar', '10', 'Kode kriteria'],
                ['nama', 'varchar', '150', 'Nama kriteria'],
                ['nama_en', 'varchar', '150', 'Nama Inggris'],
                ['deskripsi', 'text', '-', 'Deskripsi kriteria'],
                ['bobot', 'decimal', '5,2', 'Bobot penilaian'],
                ['status', 'enum', '-', 'Status aktif/nonaktif'],
                ['urutan', 'int', '11', 'Urutan tampil'],
                ['created_at', 'timestamp', '-', 'Waktu dibuat'],
                ['updated_at', 'timestamp', '-', 'Waktu diubah'],
            ],
        ],
        [
            'no' => 3,
            'nama' => 'Periode Penilaian',
            'database' => '2026_04_kinerja_knn',
            'tabel' => 'periode_penilaian',
            'primary' => 'id',
            'keterangan' => 'Tabel periode waktu pelaksanaan penilaian kinerja.',
            'fields' => [
                ['id', 'int unsigned', '10', 'Primary key'],
                ['nama_periode', 'varchar', '100', 'Nama periode'],
                ['tanggal_mulai', 'date', '-', 'Tanggal mulai'],
                ['tanggal_selesai', 'date', '-', 'Tanggal selesai'],
                ['status', 'enum', '-', 'Status periode'],
                ['keterangan', 'text', '-', 'Catatan tambahan'],
                ['created_at', 'timestamp', '-', 'Waktu dibuat'],
                ['updated_at', 'timestamp', '-', 'Waktu diubah'],
            ],
        ],
        [
            'no' => 4,
            'nama' => 'Relasi Atasan',
            'database' => '2026_04_kinerja_knn',
            'tabel' => 'relasi_atasan',
            'primary' => 'id',
            'keterangan' => 'Tabel hubungan atasan dan bawahan dalam struktur organisasi.',
            'fields' => [
                ['id', 'int unsigned', '10', 'Primary key'],
                ['id_karyawan', 'int unsigned', '10', 'ID karyawan'],
                ['id_atasan', 'int unsigned', '10', 'ID atasan'],
                ['created_at', 'timestamp', '-', 'Waktu dibuat'],
            ],
        ],
        [
            'no' => 5,
            'nama' => 'Penilaian',
            'database' => '2026_04_kinerja_knn',
            'tabel' => 'penilaian',
            'primary' => 'id',
            'keterangan' => 'Tabel transaksi hasil penilaian kinerja per periode.',
            'fields' => [
                ['id', 'int unsigned', '10', 'Primary key'],
                ['periode_id', 'int unsigned', '10', 'Relasi ke periode_penilaian'],
                ['karyawan_id', 'int unsigned', '10', 'Relasi ke karyawan'],
                ['penilai_id', 'int unsigned', '10', 'Relasi ke karyawan penilai'],
                ['nilai_kriteria', 'json', '-', 'Nilai tiap kriteria'],
                ['total_nilai', 'decimal', '5,2', 'Total nilai akhir'],
                ['klasifikasi', 'varchar', '50', 'Hasil klasifikasi'],
                ['catatan', 'text', '-', 'Catatan penilaian'],
                ['status', 'enum', '-', 'Status penilaian'],
                ['is_training', 'tinyint', '1', 'Penanda data training'],
                ['created_at', 'timestamp', '-', 'Waktu dibuat'],
                ['updated_at', 'timestamp', '-', 'Waktu diubah'],
            ],
        ],
    ];
@endphp

@foreach ($tables as $table)
    <div class="mb-4 border rounded p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
            <div>
                <h6 class="font-weight-bold mb-1">{{ $table['no'] }}. {{ $table['nama'] }}</h6>
                <div>Nama Database : <strong>{{ $table['database'] }}</strong></div>
                <div>Nama Tabel : <strong>{{ $table['tabel'] }}</strong></div>
                <div>Primary Key : <strong>{{ $table['primary'] }}</strong></div>
            </div>
        </div>
        <p class="mb-3">{{ $table['keterangan'] }}</p>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 22%;">Field</th>
                        <th style="width: 18%;">Tipe</th>
                        <th style="width: 12%;">Panjang</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table['fields'] as $index => $field)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $field[0] }}</td>
                            <td>{{ $field[1] }}</td>
                            <td>{{ $field[2] }}</td>
                            <td>{{ $field[3] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
