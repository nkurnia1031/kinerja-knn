@php
    use app\Fungsi;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Klasifikasi Kinerja</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-table .label {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .summary-table .value {
            font-size: 18px;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-primary { background-color: #007bff; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; }
        .badge-dark { background-color: #343a40; }
        .footer {
            margin-top: 30px;
        }
        .ttd-table {
            width: 100%;
            margin-top: 50px;
        }
        .ttd-table td {
            text-align: center;
            padding: 10px;
        }
        .ttd-line {
            border-bottom: 1px solid #000;
            width: 180px;
            margin: 50px auto 5px auto;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KLASIFIKASI KINERJA KARYAWAN</h1>
        <p>Periode: {{ $data['periode'] ?? 'Semua Periode' }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h3>Ringkasan Klasifikasi</h3>
        <table class="summary-table">
            <tr>
                <td class="label">Sangat Baik<br>(>=90)</td>
                <td class="label">Baik<br>(80-89)</td>
                <td class="label">Cukup<br>(70-79)</td>
                <td class="label">Kurang<br>(60-69)</td>
                <td class="label">Sangat Kurang<br>(<60)</td>
                <td class="label">Total</td>
            </tr>
            <tr>
                <td class="value" style="color: #28a745;">{{ $data['statistik']['sangat_baik'] ?? 0 }}</td>
                <td class="value" style="color: #007bff;">{{ $data['statistik']['baik'] ?? 0 }}</td>
                <td class="value" style="color: #ffc107;">{{ $data['statistik']['cukup'] ?? 0 }}</td>
                <td class="value" style="color: #dc3545;">{{ $data['statistik']['kurang'] ?? 0 }}</td>
                <td class="value" style="color: #343a40;">{{ $data['statistik']['sangat_kurang'] ?? 0 }}</td>
                <td class="value">{{ $data['statistik']['total'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <h3>Detail Klasifikasi Karyawan</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">NIK</th>
                <th width="20%">Nama Karyawan</th>
                <th width="15%">Jabatan</th>
                <th width="15%">Departemen</th>
                <th width="10%">Total Nilai</th>
                <th width="12%">Klasifikasi</th>
                <th width="14%">Penilai</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($data['list'] ?? [] as $item)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->nomor_pekerja ?? $item->nip ?? '-' }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->jabatan }}</td>
                <td>{{ $item->pekerjaan ?? '-' }}</td>
                <td class="text-center"><strong>{{ $item->total_nilai }}</strong></td>
                <td class="text-center">
                    @php
                        $kelas = '';
                        if ($item->klasifikasi == 'Sangat Baik') $kelas = 'badge-success';
                        elseif ($item->klasifikasi == 'Baik') $kelas = 'badge-primary';
                        elseif ($item->klasifikasi == 'Cukup') $kelas = 'badge-warning';
                        elseif ($item->klasifikasi == 'Kurang') $kelas = 'badge-danger';
                        else $kelas = 'badge-dark';
                    @endphp
                    <span class="badge {{ $kelas }}">{{ $item->klasifikasi }}</span>
                </td>
                <td>{{ $item->nama_penilai }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="ttd-table">
        <tr>
            <td>
                Dibuat oleh,
                <div class="ttd-line"></div>
                <strong>Admin HRD</strong>
            </td>
            <td>
                Diketahui oleh,
                <div class="ttd-line"></div>
                <strong>HRD Manager</strong>
            </td>
            <td>
                Disetujui oleh,
                <div class="ttd-line"></div>
                <strong>Direktur</strong>
            </td>
        </tr>
    </table>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 30px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>
</body>
</html>
