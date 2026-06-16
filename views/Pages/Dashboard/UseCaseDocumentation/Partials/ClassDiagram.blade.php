<pre class="bg-dark text-light p-3 rounded mb-0"><code class="language-plantuml">{{ '@startuml' }}
skinparam classAttributeIconSize 0
title Class Diagram Sistem Penilaian Kinerja

class Karyawan {
    +id : int
    +nama : varchar
    +jabatan : varchar
    +pekerjaan : text
    +tanggal_bergabung : date
    +status : enum
    +role : enum
    +username : varchar
    +password : varchar
    +getRelasiAtasan() : array
    +getDaftarBawahan() : array
}

class Kriteria {
    +id : int
    +kode : varchar
    +nama : varchar
    +nama_en : varchar
    +deskripsi : text
    +bobot : decimal
    +status : enum
    +urutan : int
    +hitungTotalBobot() : decimal
    +validasiBobot() : bool
}

class PeriodePenilaian {
    +id : int
    +nama_periode : varchar
    +tanggal_mulai : date
    +tanggal_selesai : date
    +status : enum
    +aktifkanPeriode() : bool
    +nonaktifkanPeriodeLain() : void
}

class RelasiAtasan {
    +id : int
    +id_karyawan : int
    +id_atasan : int
    +simpanRelasi() : bool
    +hapusRelasi() : bool
}

class Penilaian {
    +id : int
    +periode_id : int
    +karyawan_id : int
    +penilai_id : int
    +nilai_kriteria : json
    +total_nilai : decimal
    +klasifikasi : varchar
    +catatan : text
    +status : enum
    +is_training : bool
    +hitungTotalNilai() : decimal
    +hitungKlasifikasi() : string
    +simpanDetailKriteria() : void
}

Karyawan "1" <-- "0..*" RelasiAtasan : karyawan
Karyawan "1" <-- "0..*" RelasiAtasan : atasan
Karyawan "1" <-- "0..*" Penilaian : karyawan
Karyawan "1" <-- "0..*" Penilaian : penilai
PeriodePenilaian "1" <-- "0..*" Penilaian
Kriteria ..> Penilaian : nilai_kriteria JSON
{{ '@enduml' }}</code></pre>
