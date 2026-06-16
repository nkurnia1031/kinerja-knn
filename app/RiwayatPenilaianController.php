<?php

namespace app;

class RiwayatPenilaianController
{
    public function index($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Rekap Penilaian',
            'induk' => 'Menu',
            'path' => 'Pages.RiwayatPenilaian.Index',
            'link' => 'RiwayatPenilaian',
            'icon' => 'fas fa-history',
        ];

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session
        ]);
    }
    private function getLatestKnnRows(?int $periodeId = null): array
    {
        $service = new KinerjaService();
        $lookup = $service->getLatestKnnLookup($periodeId);

        return array_map(function ($item) {
            return (object) $item;
        }, array_values($lookup));
    }

    public function indexApi($Request, $Session, $blade)
    {
        $db = DB::con();
        $role = $Session['admin']->role ?? null;
        $requestedKaryawanId = intval($Request->karyawan_id ?? 0);
        $karyawanId = $this->resolveTargetKaryawanId($Session, $requestedKaryawanId);

        if (empty($karyawanId)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'data' => ['msg' => 'Karyawan tidak ditemukan']]);
            return;
        }

        if (in_array($role, ['atasan', 'karyawan'], true) && $requestedKaryawanId > 0 && $requestedKaryawanId !== $karyawanId) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'data' => ['msg' => 'Akses ditolak']]);
            return;
        }

        $rows = $db->run(
            "SELECT p.id, p.karyawan_id, p.total_nilai, p.klasifikasi, p.catatan, p.updated_at,
                    pp.nama_periode AS periode, pen.nama AS nama_penilai
             FROM penilaian p
             LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
             LEFT JOIN karyawan pen ON pen.id = p.penilai_id
             WHERE p.karyawan_id = ? AND p.status = 'selesai'
             ORDER BY p.periode_id DESC",
            $karyawanId
        );
        $knnMap = [];
        foreach ($this->getLatestKnnRows() as $row) {
            $knnMap[$row->karyawan_id . '-' . $row->periode_id] = $row;
        }

        foreach ($rows as $row) {
            $userId = $row->karyawan_id ?? 0;
            $row->tanggal_penilaian = !empty($row->updated_at) ? date('d/m/Y H:i', strtotime($row->updated_at)) : '-';
            $key = $userId . '-' . ($row->periode_id ?? '');
            $knn = $knnMap[$key] ?? null;
            $row->klasifikasi_knn = $knn->hasil_klasifikasi ?? null;
        }

        // foreach ($rows as $row) {
        //     $row->tanggal_penilaian = !empty($row->updated_at) ? date('d/m/Y H:i', strtotime($row->updated_at)) : '-';
        // }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => [
                'fields' => [],
                'data' => $rows,
                'filter' => [],
                'primary' => 'id',
                'request' => [],
                'tambahan' => []
            ]
        ]);
    }

    public function detailApi($Request, $Session, $blade)
    {
        $db = DB::con();
        $id = intval($Request->id ?? 0);
        $penilaian = $this->getPenilaianDetail($id);

        if (!$penilaian || !$this->canAccessPenilaian($Session, $penilaian)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }
        $penilaian->detail_kriteria=array_map(function ($item) {
            $item['progress']=($item['nilai'] / 4) * 100;
            return $item;
        }, $penilaian->detail_kriteria);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => $penilaian
        ]);
    }

    public function Cetak($Request, $Session, $blade)
    {
        $id = intval($Request->id ?? 0);
        $penilaian = $this->getPenilaianDetail($id);

        if (!$penilaian || !$this->canAccessPenilaian($Session, $penilaian)) {
            http_response_code(404);
            echo 'Data tidak ditemukan';
            return;
        }

        $detail = [];
        foreach ($penilaian->detail_kriteria as $item) {
            $detail[] = (object) $item;
        }

        echo $blade->run('Pages.RiwayatPenilaian.Cetak', [
            'data' => [
                'penilaian' => $penilaian,
                'detail' => $detail
            ]
        ]);
    }

    private function resolveTargetKaryawanId($Session, int $requestedKaryawanId): int
    {
        $role = $Session['admin']->role ?? null;
        if (in_array($role, ['admin', 'pimpinan'], true) && $requestedKaryawanId > 0) {
            return $requestedKaryawanId;
        }

        return intval($Session['admin']->id ?? 0);
    }

    private function canAccessPenilaian($Session, $penilaian): bool
    {
        $role = $Session['admin']->role ?? null;
        if (in_array($role, ['admin', 'pimpinan'], true)) {
            return true;
        }

        return intval($Session['admin']->id ?? 0) === intval($penilaian->karyawan_id ?? 0);
    }

    private function getPenilaianDetail(int $id)
    {
        $db = DB::con();
        $penilaian = $db->run(
            "SELECT p.id, p.karyawan_id, p.nilai_kriteria, p.total_nilai, p.klasifikasi, p.catatan, p.updated_at,
                    p.periode_id, k.nama AS nama_karyawan, k.jabatan, k.username, k.pekerjaan,
                    pp.nama_periode AS periode, pen.nama AS nama_penilai, pen.jabatan AS jabatan_penilai
             FROM penilaian p
             JOIN karyawan k ON k.id = p.karyawan_id
             LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
             LEFT JOIN karyawan pen ON pen.id = p.penilai_id
             WHERE p.id = ? AND p.status = 'selesai'",
            $id
        )[0] ?? null;

        if (!$penilaian) {
            return null;
        }

        $nilaiKriteria = json_decode($penilaian->nilai_kriteria ?? '[]', true) ?? [];
        $nilaiByKriteriaId = [];
        foreach ($nilaiKriteria as $key => $val) {
            if (is_array($val) && isset($val['kriteria_id'])) {
                $nilaiByKriteriaId[(int) $val['kriteria_id']] = floatval($val['nilai'] ?? 0);
            } elseif (is_numeric($key) && is_numeric($val)) {
                $nilaiByKriteriaId[(int) $key] = floatval($val);
            }
        }

        $kriteria = $db->run("SELECT id, nama, bobot FROM kriteria WHERE status = 'aktif' ORDER BY urutan ASC");
        $detail = [];
        foreach ($kriteria as $kr) {
            $nilai = floatval($nilaiByKriteriaId[(int) $kr->id] ?? 0);
            $detail[] = [
                'nama_kriteria' => $kr->nama,
                'bobot' => floatval($kr->bobot),
                'nilai' => $nilai,
                'nilai_bobot' => round(($nilai * floatval($kr->bobot)) / 100, 2),
            ];
        }

        $penilaian->tanggal_penilaian = !empty($penilaian->updated_at) ? date('d/m/Y H:i', strtotime($penilaian->updated_at)) : '-';
        $penilaian->detail_kriteria = $detail;

        return $penilaian;
    }
}
