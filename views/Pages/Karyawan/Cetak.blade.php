@php
    use app\Fungsi;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Karyawan</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DATA KARYAWAN</h1>
        <p>Sistem Penilaian Kinerja Karyawan</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">NIK</th>
                <th width="20%">Nama</th>
                <th width="15%">Jabatan</th>
                <th width="15%">Departemen</th>
                <th width="8%">Jenis Kelamin</th>
                <th width="10%">Tanggal Masuk</th>
                <th width="8%">Status</th>
                <th width="8%">Atasan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($data['list'] ?? [] as $item)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->nik }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->jabatan }}</td>
                <td>{{ $item->departemen }}</td>
                <td class="text-center">{{ $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td class="text-center">{{ $item->tanggal_masuk }}</td>
                <td class="text-center">
                    @if($item->status_kerja == 'tetap')
                        <span class="badge badge-success">Tetap</span>
                    @elseif($item->status_kerja == 'kontrak')
                        <span class="badge badge-warning">Kontrak</span>
                    @else
                        <span class="badge badge-secondary">{{ $item->status_kerja }}</span>
                    @endif
                </td>
                <td>{{ $item->nama_atasan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Total: {{ count($data['list'] ?? []) }} Karyawan
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 30px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>
</body>
</html>
