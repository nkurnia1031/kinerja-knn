@php
    use app\Fungsi;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Penilaian Kinerja</title>
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
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
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
            font-weight: bold;
        }
        .penilaian-table .kriteria-nama {
            text-align: left;
        }
        .penilaian-table .text-left {
            text-align: left;
        }
        .total-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }
        .klasifikasi {
            padding: 4px 12px;
            border-radius: 4px;
            display: inline-block;
            font-weight: bold;
            font-size: 11px;
        }
        .sangat-baik { background-color: #28a745; color: white; }
        .baik { background-color: #007bff; color: white; }
        .cukup { background-color: #ffc107; color: black; }
        .kurang { background-color: #dc3545; color: white; }
        .sangat-kurang { background-color: #343a40; color: white; }
        .ttd-section {
            margin-top: 40px;
            page-break-inside: avoid;
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
            width: 180px;
            margin: 50px auto 5px auto;
        }
        .catatan-box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 15px;
            min-height: 50px;
            background-color: #fafafa;
        }
        .section-title {
            background-color: #4e73df;
            color: white;
            padding: 8px 15px;
            margin: 20px 0 15px 0;
            font-weight: bold;
        }
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .rekap-table th, .rekap-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .rekap-table th {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
        }
        .rekap-table tbody tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        .summary-box {
            border: 2px solid #4e73df;
            padding: 15px;
            margin: 20px 0;
            background-color: #f8f9fc;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            color: #4e73df;
        }
        .summary-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
        }
        .summary-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
        }
        .summary-item .label {
            font-size: 11px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    @if(isset($data['mode']) && $data['mode'] === 'single')
        {{-- Single Penilaian Detail Print --}}
        <div class="header">
            <h1>Laporan Penilaian Kinerja Karyawan</h1>
            <h2>{{ $data['penilaian']->nama_periode ?? '-' }}</h2>
            <p>Dicetak pada: {{ $data['tglExport'] ?? date('d/m/Y H:i:s') }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Karyawan</td>
                <td>: <strong>{{ $data['penilaian']->nama_karyawan ?? '-' }}</strong></td>
                <td class="label">Tanggal Penilaian</td>
                <td>: {{ isset($data['penilaian']->updated_at) ? date('d/m/Y H:i', strtotime($data['penilaian']->updated_at)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">ID Karyawan</td>
                <td>: {{ $data['penilaian']->karyawan_id ?? '-' }}</td>
                <td class="label">Penilai</td>
                <td>: {{ $data['penilaian']->nama_penilai ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td>: {{ $data['penilaian']->jabatan_karyawan ?? '-' }}</td>
                <td class="label">Jabatan Penilai</td>
                <td>: {{ $data['penilaian']->jabatan_penilai ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Pekerjaan</td>
                <td>: {{ ucfirst($data['penilaian']->pekerjaan ?? '-') }}</td>
                <td class="label">Status</td>
                <td>: {{ ucfirst($data['penilaian']->status ?? '-') }}</td>
            </tr>
        </table>

        <table class="penilaian-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Kode</th>
                    <th width="30%">Kriteria Penilaian</th>
                    <th width="12%">Bobot (%)</th>
                    <th width="12%">Nilai</th>
                    <th width="15%">Nilai x Bobot</th>
                    <th width="16%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; $totalBobot = 0; $totalNilaiBobot = 0; @endphp
                @foreach($data['detail_nilai'] ?? [] as $detail)
                @php 
                    $totalBobot += $detail['bobot'];
                    $totalNilaiBobot += $detail['nilai_bobot'];
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $detail['kode'] }}</td>
                    <td class="kriteria-nama">{{ $detail['nama'] }}</td>
                    <td>{{ $detail['bobot'] }}%</td>
                    <td>{{ $detail['nilai'] }}</td>
                    <td>{{ number_format($detail['nilai_bobot'], 2) }}</td>
                    <td>
                        @php
                            $nilai = $detail['nilai'];
                            if ($nilai >= 90) $ket = 'Sangat Baik';
                            elseif ($nilai >= 80) $ket = 'Baik';
                            elseif ($nilai >= 70) $ket = 'Cukup';
                            elseif ($nilai >= 60) $ket = 'Kurang';
                            else $ket = 'Sangat Kurang';
                        @endphp
                        {{ $ket }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">TOTAL</td>
                    <td>{{ number_format($totalBobot, 0) }}%</td>
                    <td>-</td>
                    <td>{{ $data['penilaian']->total_nilai ?? number_format($totalNilaiBobot, 2) }}</td>
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

        @if(!empty($data['penilaian']->catatan))
        <div class="catatan-box">
            <strong>Catatan Penilai:</strong><br>
            {{ $data['penilaian']->catatan }}
        </div>
        @endif

        <div class="ttd-section">
            <table class="ttd-table">
                <tr>
                    <td>
                        Karyawan yang Dinilai
                        <div class="ttd-line"></div>
                        <strong>{{ $data['penilaian']->nama_karyawan ?? '-' }}</strong><br>
                        <small>{{ $data['penilaian']->jabatan_karyawan ?? '-' }}</small>
                    </td>
                    <td>
                        Penilai / Atasan
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

    @else
        {{-- Multi Penilaian Rekap Print --}}
        <div class="header">
            <h1>Rekap Penilaian Kinerja Karyawan</h1>
            <h2>{{ $data['judul'] ?? 'Rekap Penilaian' }}</h2>
            <p>Dicetak pada: {{ $data['tglExport'] ?? date('d/m/Y H:i:s') }}</p>
        </div>

        {{-- Filter Information --}}
        @if(!empty($data['filter']))
        <div style="margin-bottom: 15px;">
            <strong>Filter Data:</strong>
            <table class="info-table" style="font-size: 11px;">
                @foreach($data['filter'] as $filter)
                <tr>
                    <td class="label">{{ $filter->label ?? $filter->name }}</td>
                    <td>: {{ $filter->val ?? '-' }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        {{-- Data Table --}}
        <table class="rekap-table">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="6%">ID</th>
                    <th width="18%">Nama Karyawan</th>
                    <th width="14%">Jabatan</th>
                    <th width="14%">Periode</th>
                    <th width="14%">Penilai</th>
                    <th width="8%">Total Nilai</th>
                    <th width="12%">Klasifikasi</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($data['data'] ?? [] as $item)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td style="text-align: center;">{{ $item->id ?? '-' }}</td>
                    <td>{{ $item->nama_karyawan ?? $item->nama ?? '-' }}</td>
                    <td>{{ $item->jabatan_karyawan ?? $item->jabatan ?? '-' }}</td>
                    <td>{{ $item->nama_periode ?? $item->periode ?? '-' }}</td>
                    <td>{{ $item->nama_penilai ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $item->total_nilai ?? '-' }}</td>
                    <td style="text-align: center;">
                        @php
                            $klasifikasi = $item->klasifikasi ?? '-';
                            $kelasKlasifikasi = '';
                            if ($klasifikasi == 'Sangat Baik') $kelasKlasifikasi = 'sangat-baik';
                            elseif ($klasifikasi == 'Baik') $kelasKlasifikasi = 'baik';
                            elseif ($klasifikasi == 'Cukup') $kelasKlasifikasi = 'cukup';
                            elseif ($klasifikasi == 'Kurang') $kelasKlasifikasi = 'kurang';
                            else $kelasKlasifikasi = 'sangat-kurang';
                        @endphp
                        <span class="klasifikasi {{ $kelasKlasifikasi }}">{{ $klasifikasi }}</span>
                    </td>
                    <td style="text-align: center;">{{ ucfirst($item->status ?? '-') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($data['data'] ?? []) == 0)
        <div style="text-align: center; padding: 30px; color: #666;">
            <em>Tidak ada data yang ditemukan</em>
        </div>
        @endif

        {{-- Summary Statistics --}}
        @if(count($data['data'] ?? []) > 0)
        <div class="summary-box">
            <h3>Ringkasan Statistik</h3>
            @php
                $totalData = count($data['data']);
                $sangat_baik = 0;
                $baik = 0;
                $cukup = 0;
                $kurang = 0;
                $sangat_kurang = 0;
                $totalNilai = 0;
                
                foreach($data['data'] as $item) {
                    $totalNilai += floatval($item->total_nilai ?? 0);
                    $klas = $item->klasifikasi ?? '';
                    if ($klas == 'Sangat Baik') $sangat_baik++;
                    elseif ($klas == 'Baik') $baik++;
                    elseif ($klas == 'Cukup') $cukup++;
                    elseif ($klas == 'Kurang') $kurang++;
                    else $sangat_kurang++;
                }
                $rataRata = $totalData > 0 ? $totalNilai / $totalData : 0;
            @endphp
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: center; padding: 10px;">
                        <div style="font-size: 24px; font-weight: bold; color: #4e73df;">{{ $totalData }}</div>
                        <div style="font-size: 11px; color: #666;">Total Data</div>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $sangat_baik }}</div>
                        <div style="font-size: 11px; color: #666;">Sangat Baik</div>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        <div style="font-size: 24px; font-weight: bold; color: #007bff;">{{ $baik }}</div>
                        <div style="font-size: 11px; color: #666;">Baik</div>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        <div style="font-size: 24px; font-weight: bold; color: #ffc107;">{{ $cukup }}</div>
                        <div style="font-size: 11px; color: #666;">Cukup</div>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        <div style="font-size: 24px; font-weight: bold; color: #dc3545;">{{ $kurang + $sangat_kurang }}</div>
                        <div style="font-size: 11px; color: #666;">Kurang</div>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        <div style="font-size: 24px; font-weight: bold; color: #36b9cc;">{{ number_format($rataRata, 2) }}</div>
                        <div style="font-size: 11px; color: #666;">Rata-rata Nilai</div>
                    </td>
                </tr>
            </table>
        </div>
        @endif

        <div class="ttd-section">
            <table class="ttd-table">
                <tr>
                    <td width="50%"></td>
                    <td width="50%">
                        Mengetahui,<br>HRD Manager
                        <div class="ttd-line"></div>
                        <strong>________________</strong><br>
                        <small>HRD Manager</small>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 30px; cursor: pointer; margin-left: 10px; background-color: #858796; color: white; border: none; border-radius: 4px;">
            Tutup
        </button>
    </div>
</body>
</html>
