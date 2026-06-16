<?php

namespace app;

class ProfilSayaController
{
    public function index($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Profil Saya',
            'induk' => 'Menu',
            'path' => 'Pages.ProfilSaya.Index',
            'link' => 'ProfilSaya',
            'icon' => 'fas fa-user',
        ];

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session
        ]);
    }

    public function indexApi($Request, $Session, $blade)
    {
        $userId = intval($Session['admin']->id ?? 0);
        $db = DB::con();

        $profil = $db->run(
            "SELECT id, nama, jabatan, pekerjaan, tanggal_bergabung, status, role, username
             FROM karyawan
             WHERE id = ?",
            $userId
        )[0] ?? null;

        $atasan = $db->run(
            "SELECT a.id, a.nama, a.jabatan, a.role
             FROM relasi_atasan r
             JOIN karyawan a ON a.id = r.id_atasan
             WHERE r.id_karyawan = ?
             ORDER BY a.nama ASC",
            $userId
        );

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => [
                'profil' => $profil,
                'atasan' => $atasan
            ]
        ]);
    }

    public function updateProfil($Request, $Session, $blade)
    {
        $userId = intval($Session['admin']->id ?? 0);
        $payload = json_decode(file_get_contents('php://input'));
        $payload = is_object($payload) ? $payload : (object) [];

        $db = DB::con();
        $db->run(
            "UPDATE karyawan
             SET nama = ?, jabatan = ?, pekerjaan = ?, updated_at = NOW()
             WHERE id = ?",
            $payload->nama ?? '',
            $payload->jabatan ?? '',
            $payload->pekerjaan ?? '',
            $userId
        );

        if (!empty($_SESSION['admin'])) {
            $_SESSION['admin']->nama = $payload->nama ?? ($_SESSION['admin']->nama ?? '');
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    public function ubahPassword($Request, $Session, $blade)
    {
        $userId = intval($Session['admin']->id ?? 0);
        $payload = json_decode(file_get_contents('php://input'));
        $payload = is_object($payload) ? $payload : (object) [];

        $db = DB::con();
        $user = $db->run("SELECT password FROM karyawan WHERE id = ?", $userId)[0] ?? null;

        if (!$user || !password_verify($payload->password_lama ?? '', $user->password ?? '')) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => false,
                'message' => 'Password lama tidak sesuai'
            ]);
            return;
        }

        if (($payload->password_baru ?? '') !== ($payload->konfirmasi_password ?? '')) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => false,
                'message' => 'Konfirmasi password tidak sesuai'
            ]);
            return;
        }

        $db->run(
            "UPDATE karyawan SET password = ?, updated_at = NOW() WHERE id = ?",
            password_hash($payload->password_baru, PASSWORD_DEFAULT),
            $userId
        );

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}
