<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $data['judul_laporan'] ?? 'Laporan' }}</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        .title {
            text-align: center;
            margin-bottom: 18px;
        }

        .title h2 {
            margin: 0 0 6px 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .meta {
            margin-bottom: 16px;
            font-size: 11px;
        }

        .meta div {
            margin-bottom: 4px;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary-grid td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .summary-label {
            font-weight: bold;
            background: #f1f1f1;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .data-table th {
            background: #f1f1f1;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .footer-actions {
            margin-top: 30px;
            text-align: center;
        }

        .footer-actions button {
            padding: 10px 24px;
            cursor: pointer;
            margin: 0 4px;
        }

        @media print {
            .footer-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    @include('Komponen.Kop')

    <div class="title">
        <h2>{{ $data['judul_laporan'] ?? 'Laporan' }}</h2>
    </div>

    <div class="meta">
        <div><strong>Filter:</strong> {{ $data['filter_label'] ?? 'Semua data' }}</div>
        <div><strong>Dicetak pada:</strong> {{ $data['tgl_cetak'] ?? date('d/m/Y H:i:s') }}</div>
    </div>

    @if (($data['jenis_laporan'] ?? '') === 'rekap_penilaian_klasifikasi')
        <table class="summary-grid">
            <tr>
                <td class="summary-label">Total Penilaian</td>
                <td class="summary-label">Rata-rata</td>
                <td class="summary-label">Prioritas Apresiasi</td>
                <td class="summary-label">Perlu Penguatan</td>
                <td class="summary-label">Prioritas Pembinaan</td>
                <td class="summary-label">Sangat Baik (KNN)</td>
                <td class="summary-label">Baik (KNN)</td>
            </tr>
            <tr>
                <td>{{ $data['summary']['total'] ?? 0 }}</td>
                <td>{{ $data['summary']['rata_rata'] ?? 0 }}</td>
                <td>{{ $data['summary']['kandidat_reward'] ?? 0 }}</td>
                <td>{{ $data['summary']['perlu_pemantauan'] ?? 0 }}</td>
                <td>{{ $data['summary']['perlu_bimbingan'] ?? 0 }}</td>
                <td>{{ $data['summary']['sangat_baik'] ?? 0 }}</td>
                <td>{{ $data['summary']['baik'] ?? 0 }}</td>
            </tr>
        </table>

        <div style="margin-bottom: 14px; font-size: 11px;">
            Rekap ini menggunakan <strong>klasifikasi KNN</strong> sebagai dasar pendukung keputusan untuk menentukan prioritas apresiasi bagi karyawan dengan performa unggul dan prioritas pembinaan bagi karyawan yang masih memerlukan peningkatan kinerja.
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Pekerja</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Periode</th>
                    <th>Penilai</th>
                    <th>Total Nilai</th>
                    <th>Klasifikasi</th>
                    <th>Klasifikasi KNN</th>
                    <th>Kategori Keputusan</th>
                    <th>Tanggal Penilaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($data['rows'] ?? []) as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->nomor_pekerja ?? '-' }}</td>
                        <td>{{ $item->nama ?? '-' }}</td>
                        <td>{{ $item->jabatan ?? '-' }}</td>
                        <td>{{ $item->nama_periode ?? '-' }}</td>
                        <td>{{ $item->nama_penilai ?? '-' }}</td>
                        <td class="text-center">{{ $item->total_nilai ?? 0 }}</td>
                        <td class="text-center">{{ $item->klasifikasi ?? '-' }}</td>
                        <td class="text-center">{{ $item->klasifikasi_knn ?? '-' }}</td>
                        <td class="text-center">
                            @if (in_array($item->klasifikasi_knn ?? '', ['Sangat Baik', 'Baik']))
                                Prioritas Apresiasi
                            @elseif (($item->klasifikasi_knn ?? '') === 'Cukup')
                                Perlu Penguatan Kinerja
                            @elseif (in_array($item->klasifikasi_knn ?? '', ['Kurang', 'Sangat Kurang']))
                                Prioritas Pembinaan
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item->tanggal_penilaian ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="summary-grid">
            <tr>
                <td class="summary-label">Total Karyawan</td>
                <td class="summary-label">Status Aktif</td>
                <td class="summary-label">Total Atasan</td>
                <td class="summary-label">Total Pimpinan</td>
                <td class="summary-label">Total Role Karyawan</td>
            </tr>
            <tr>
                <td>{{ $data['summary']['total'] ?? 0 }}</td>
                <td>{{ $data['summary']['aktif'] ?? 0 }}</td>
                <td>{{ $data['summary']['atasan'] ?? 0 }}</td>
                <td>{{ $data['summary']['pimpinan'] ?? 0 }}</td>
                <td>{{ $data['summary']['karyawan'] ?? 0 }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Pekerja</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Pekerjaan</th>
                    <th>Tanggal Bergabung</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($data['rows'] ?? []) as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->username ?? '-' }}</td>
                        <td>{{ $item->nama ?? '-' }}</td>
                        <td>{{ $item->jabatan ?? '-' }}</td>
                        <td class="text-center">{{ $item->role ?? '-' }}</td>
                        <td class="text-center">{{ $item->status ?? '-' }}</td>
                        <td>{{ $item->pekerjaan ?? '-' }}</td>
                        <td class="text-center">{{ $item->tanggal_bergabung ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @include('Komponen.Ttd')

    <div class="footer-actions">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</body>
</html>
