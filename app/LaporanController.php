<?php

namespace app;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->judul = 'Laporan';
        $this->Link = 'Laporan';
        $this->Icon = 'fas fa-print';
    }

    public function index($Request, $Session, $blade)
    {
        $data = [
            'judul' => $this->judul,
            'induk' => 'Master',
            'path' => 'Pages.Laporan.Index',
            'link' => $this->Link,
            'icon' => $this->Icon,
            'periodeList' => $this->getPeriodeList(),
            'filterOptions' => [
                'status' => $this->getStatusList(),
                'role' => $this->getRoleList(),
                'pekerjaan' => $this->getPekerjaanList(),
                'klasifikasi' => $this->getKlasifikasiList(),
            ],
        ];

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }

    public function indexApi($Request, $Session, $blade)
    {
        header('Content-Type: application/json');

        $jenisLaporan = $Request->jenis_laporan ?? 'rekap_karyawan';
        $payload = $jenisLaporan === 'rekap_penilaian_klasifikasi'
            ? $this->getRekapPenilaianKlasifikasiData($Request)
            : $this->getRekapKaryawanData($Request);

        echo json_encode([
            'status' => true,
            'data' => $payload,
        ]);
    }

    public function Cetak($data, $blade)
    {
        $args = func_get_args();
        $Request = $args[0] ?? null;
        $Session = $args[1] ?? [];
        $blade = $args[2] ?? $blade;

        $jenisLaporan = $Request->jenis_laporan ?? 'rekap_karyawan';
        $payload = $jenisLaporan === 'rekap_penilaian_klasifikasi'
            ? $this->getRekapPenilaianKlasifikasiData($Request)
            : $this->getRekapKaryawanData($Request);

        $payload['ttd'] = $this->getTtdData($Session);
        $payload['jenis_laporan'] = $jenisLaporan;
        $payload['tgl_cetak'] = date('d/m/Y H:i:s');

        echo $blade->run('Pages.Laporan.Cetak', [
            'data' => $payload,
            'Request' => $Request,
            'Session' => $Session,
        ]);
    }

    private function getPeriodeList()
    {
        return $this->getDB()->run(
            "SELECT id, nama_periode, status
             FROM periode_penilaian
             ORDER BY tanggal_mulai DESC, id DESC"
        );
    }

    private function getStatusList()
    {
        return ['aktif', 'nonaktif', 'cuti', 'resign'];
    }

    private function getRoleList()
    {
        return ['admin', 'pimpinan', 'atasan', 'karyawan'];
    }

    private function getPekerjaanList()
    {
        $rows = $this->getDB()->run(
            "SELECT DISTINCT pekerjaan
             FROM karyawan
             WHERE pekerjaan IS NOT NULL AND pekerjaan <> ''
             ORDER BY pekerjaan ASC"
        );

        return array_values(array_map(function ($item) {
            return $item->pekerjaan;
        }, $rows));
    }

    private function getKlasifikasiList()
    {
        return ['Sangat Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat Kurang'];
    }

    private function getRekapKaryawanData($Request)
    {
        $filters = [
            'q' => trim($Request->q ?? ''),
            'status' => trim($Request->status ?? ''),
            'role' => trim($Request->role ?? ''),
            'pekerjaan' => trim($Request->pekerjaan ?? ''),
        ];

        $params = [];
        $where = ["1 = 1"];

        if ($filters['q'] !== '') {
            $where[] = "(nama LIKE ? OR username LIKE ? OR jabatan LIKE ?)";
            $keyword = '%' . $filters['q'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if ($filters['status'] !== '') {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['role'] !== '') {
            $where[] = "role = ?";
            $params[] = $filters['role'];
        }

        if ($filters['pekerjaan'] !== '') {
            $where[] = "pekerjaan = ?";
            $params[] = $filters['pekerjaan'];
        }

        $sql = "SELECT id, username, nama, jabatan, role, status, pekerjaan, tanggal_bergabung
                FROM karyawan
                WHERE " . implode(' AND ', $where) . "
                ORDER BY nama ASC";

        $rows = $this->getDB()->run($sql, ...$params);

        $summary = [
            'total' => count($rows),
            'aktif' => 0,
            'atasan' => 0,
            'pimpinan' => 0,
            'karyawan' => 0,
        ];

        foreach ($rows as $row) {
            if (($row->status ?? '') === 'aktif') {
                $summary['aktif']++;
            }
            if (($row->role ?? '') === 'atasan') {
                $summary['atasan']++;
            }
            if (($row->role ?? '') === 'pimpinan') {
                $summary['pimpinan']++;
            }
            if (($row->role ?? '') === 'karyawan') {
                $summary['karyawan']++;
            }
        }

        return [
            'judul_laporan' => 'Laporan Rekap Karyawan',
            'filter_label' => $this->buildFilterLabel([
                'Pencarian' => $filters['q'],
                'Status' => $filters['status'],
                'Role' => $filters['role'],
                'Pekerjaan' => $filters['pekerjaan'],
            ]),
            'summary' => $summary,
            'rows' => $rows,
            'filters' => $filters,
        ];
    }

    private function getRekapPenilaianKlasifikasiData($Request)
    {
        $filters = [
            'q' => trim($Request->q ?? ''),
            'periode_id' => trim($Request->periode_id ?? ''),
            'klasifikasi' => trim($Request->klasifikasi ?? ''),
        ];

        $params = [];
        $where = ["p.status = 'selesai'"];

        if ($filters['q'] !== '') {
            $where[] = "(k.nama LIKE ? OR k.username LIKE ? OR k.jabatan LIKE ?)";
            $keyword = '%' . $filters['q'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if ($filters['periode_id'] !== '') {
            $where[] = "p.periode_id = ?";
            $params[] = $filters['periode_id'];
        }

        $sql = "SELECT
                    p.id,
                    p.karyawan_id,
                    p.periode_id,
                    p.total_nilai,
                    p.klasifikasi,
                    p.updated_at,
                    k.username AS nomor_pekerja,
                    k.nama,
                    k.jabatan,
                    k.pekerjaan,
                    pen.nama AS nama_penilai,
                    pp.nama_periode
                FROM penilaian p
                LEFT JOIN karyawan k ON k.id = p.karyawan_id
                LEFT JOIN karyawan pen ON pen.id = p.penilai_id
                LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pp.tanggal_mulai DESC, k.nama ASC";

        $rows = $this->getDB()->run($sql, ...$params);
        $knnLookup = (new KinerjaService())->getLatestKnnLookup();

        $filteredRows = [];
        $summary = [
            'total' => 0,
            'rata_rata' => 0,
            'sangat_baik' => 0,
            'baik' => 0,
            'cukup' => 0,
            'kurang' => 0,
            'sangat_kurang' => 0,
            'kandidat_reward' => 0,
            'perlu_pemantauan' => 0,
            'perlu_bimbingan' => 0,
        ];

        $totalNilai = 0;
        foreach ($rows as $row) {
            $key = ($row->karyawan_id ?? '') . '-' . ($row->periode_id ?? '');
            $row->klasifikasi_knn = $knnLookup[$key]['hasil_klasifikasi'] ?? '-';
            $row->tanggal_penilaian = !empty($row->updated_at) ? date('d/m/Y H:i', strtotime($row->updated_at)) : '-';

            if ($filters['klasifikasi'] !== '' && $row->klasifikasi_knn !== $filters['klasifikasi']) {
                continue;
            }

            $filteredRows[] = $row;

            $totalNilai += floatval($row->total_nilai ?? 0);
            $label = strtolower(str_replace(' ', '_', $row->klasifikasi_knn ?? ''));
            if (isset($summary[$label])) {
                $summary[$label]++;
            }
        }

        $summary['total'] = count($filteredRows);
        if ($summary['total'] > 0) {
            $summary['rata_rata'] = round($totalNilai / $summary['total'], 2);
        }
        $summary['kandidat_reward'] = $summary['sangat_baik'] + $summary['baik'];
        $summary['perlu_pemantauan'] = $summary['cukup'];
        $summary['perlu_bimbingan'] = $summary['kurang'] + $summary['sangat_kurang'];

        $periode = null;
        if ($filters['periode_id'] !== '') {
            $periodeRows = $this->getDB()->run(
                "SELECT nama_periode FROM periode_penilaian WHERE id = ?",
                $filters['periode_id']
            );
            $periode = $periodeRows[0] ?? null;
        }

        return [
            'judul_laporan' => 'Laporan Rekap Penilaian & Klasifikasi',
            'filter_label' => $this->buildFilterLabel([
                'Pencarian' => $filters['q'],
                'Periode' => $periode->nama_periode ?? '',
                'Klasifikasi' => $filters['klasifikasi'],
            ]),
            'summary' => $summary,
            'rows' => $filteredRows,
            'filters' => $filters,
        ];
    }

    private function buildFilterLabel(array $items)
    {
        $labels = [];
        foreach ($items as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $labels[] = $label . ': ' . $value;
        }

        return empty($labels) ? 'Semua data' : implode(' | ', $labels);
    }

    private function getTtdData($Session)
    {
        $admin = $Session['admin'] ?? null;

        return (object) [
            'ket' => 'Mengetahui,',
            'jabatan' => $admin->jabatan ?? ucfirst($admin->role ?? 'Pimpinan'),
            'nama' => $admin->nama ?? '................................',
            'tambahan' => '',
        ];
    }
}
