@php
    use app\Fungsi;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penilaian Kinerja</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
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
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 3px 0;
        }
        .info-table .label {
            width: 150px;
            font-weight: bold;
        }
        .penilaian-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .penilaian-table th, .penilaian-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .penilaian-table th {
            background-color: #f0f0f0;
        }
        .penilaian-table .kriteria-nama {
            text-align: left;
        }
        .total-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }
        .klasifikasi {
            padding: 5px 15px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .sangat-baik { background-color: #28a745; color: white; }
        .baik { background-color: #007bff; color: white; }
        .cukup { background-color: #ffc107; color: black; }
        .kurang { background-color: #dc3545; color: white; }
        .sangat-kurang { background-color: #343a40; color: white; }
        .ttd-section {
            margin-top: 50px;
        }
        .ttd-table {
            width: 100%;
        }
        .ttd-table td {
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }
        .ttd-line {
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 60px auto 5px auto;
        }
        .catatan-box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 20px;
            min-height: 60px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENILAIAN KINERJA KARYAWAN</h1>
        <h2>Periode: {{ $data['penilaian']->periode ?? '-' }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Karyawan</td>
            <td>: {{ $data['penilaian']->nama_karyawan ?? '-' }}</td>
            <td class="label">Tanggal Penilaian</td>
            <td>: {{ $data['penilaian']->tanggal_penilaian ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Pekerja</td>
            <td>: {{ $data['penilaian']->username ?? '-' }}</td>
            <td class="label">Penilai</td>
            <td>: {{ $data['penilaian']->nama_penilai ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $data['penilaian']->jabatan ?? '-' }}</td>
            <td class="label">Jabatan Penilai</td>
            <td>: {{ $data['penilaian']->jabatan_penilai ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pekerjaan</td>
            <td>: {{ $data['penilaian']->pekerjaan ?? '-' }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="penilaian-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Kriteria Penilaian</th>
                <th width="15%">Bobot (%)</th>
                <th width="15%">Nilai</th>
                <th width="15%">Nilai x Bobot</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($data['detail'] ?? [] as $detail)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="kriteria-nama">{{ $detail->nama_kriteria }}</td>
                <td>{{ $detail->bobot }}%</td>
                <td>{{ $detail->nilai }}</td>
                <td>{{ number_format($detail->nilai_bobot, 2) }}</td>
                <td>
                    @if($detail->nilai >= 90) Sangat Baik
                    @elseif($detail->nilai >= 80) Baik
                    @elseif($detail->nilai >= 70) Cukup
                    @elseif($detail->nilai >= 60) Kurang
                    @else Sangat Kurang
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL NILAI</td>
                <td>{{ $data['penilaian']->total_nilai ?? '-' }}</td>
                <td>
                    @php
                        $klasifikasi = $data['penilaian']->klasifikasi ?? '-';
                        $kelasKlasifikasi = '';
                        if ($klasifikasi == 'Sangat Baik') $kelasKlasifikasi = 'sangat-baik';
                        elseif ($klasifikasi == 'Baik') $kelasKlasifikasi = 'baik';
                        elseif ($klasifikasi == 'Cukup') $kelasKlasifikasi = 'cukup';
                        elseif ($klasifikasi == 'Kurang') $kelasKlasifikasi = 'kurang';
                        else $kelasKlasifikasi = 'sangat-kurang';
                    @endphp
                    <span class="klasifikasi {{ $kelasKlasifikasi }}">{{ $klasifikasi }}</span>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="catatan-box">
        <strong>Catatan Penilai:</strong><br>
        {{ $data['penilaian']->catatan ?? '-' }}
    </div>

    <div class="ttd-section">
        <table class="ttd-table">
            <tr>
                <td>
                    Karyawan yang Dinilai
                    <div class="ttd-line"></div>
                    <strong>{{ $data['penilaian']->nama_karyawan ?? '-' }}</strong><br>
                    <small>{{ $data['penilaian']->jabatan ?? '-' }}</small>
                </td>
                <td>
                    Penilai
                    <div class="ttd-line"></div>
                    <strong>{{ $data['penilaian']->nama_penilai ?? '-' }}</strong><br>
                    <small>{{ $data['penilaian']->jabatan_penilai ?? '-' }}</small>
                </td>
                <td>
                    Mengetahui,<br>HRD Manager
                    <div class="ttd-line"></div>
                    <strong>________________</strong><br>
                    <small>HRD Manager</small>
                </td>
            </tr>
        </table>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 30px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>
</body>
</html>
