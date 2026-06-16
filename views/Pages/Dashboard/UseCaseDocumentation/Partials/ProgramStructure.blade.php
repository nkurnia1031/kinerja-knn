<pre class="bg-dark text-light p-3 rounded mb-0"><code class="language-mermaid">flowchart TD
    A["Sistem Penilaian Kinerja"] --> B["Login"]
    A --> C["Dashboard"]
    A --> D["Data Master"]
    A --> E["Penilaian Kinerja"]
    A --> F["Laporan dan Analisis"]
    A --> H["Logout"]

    D --> D1["Data Karyawan"]
    D --> D2["Kriteria Penilaian"]
    D --> D3["Periode Penilaian"]
    D --> D4["Relasi Atasan"]

    E --> E1["Penilaian Bawahan"]
    E --> E2["Review Penilaian"]
    E --> E3["Daftar Bawahan"]

    F --> F1["Laporan"]
    F --> F2["Rekap Penilaian"]
    F --> F3["Analisa Kinerja"]

    classDef root fill:#0f172a,color:#ffffff,stroke:#0f172a,stroke-width:1px;
    classDef group fill:#f8fafc,color:#111827,stroke:#94a3b8,stroke-width:1px;
    classDef leaf fill:#ffffff,color:#111827,stroke:#cbd5e1,stroke-width:1px;

    class A root;
    class B,C,D,E,F,H group;
    class D1,D2,D3,D4,E1,E2,E3,F1,F2,F3 leaf;
</code></pre>
