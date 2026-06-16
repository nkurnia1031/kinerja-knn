<?php

/**
 *
 */

namespace app;

use app\Crud as Crud;
use app\Model\Anggota as Anggotas;
use app\Model\Rekening as Rekenings;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Master
{
    public $models;
    public function __construct()
    {
        $this->models = (object) [];

        // $this->models->Pegawai = new Pegawais;
    }


    public function Login($Request, $Session, $blade)
    {
        // dd(password_hash('admin', PASSWORD_DEFAULT));
        $data = [
            'judul' => 'Login',
            'path' => 'Pages.Login',
            'link' => 'Login',
            'icon' => 'fa fa-lock',

        ];
        $link = 'Dashboard';


        if (isset($Session['admin'])) {
            echo "<script>location.href = '$link';</script>";
            die();
        }
        // dd($_SERVER);

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,

        ]);
    }

    public function ProsesLogin($Request, $Session, $blade)
    {
        if (!is_object($Request)) {
            $Request = (object) $Request;
        }
        // if (!empty($Request->token)) {
        //     $cek = Fungsi::turnstileVerify($Request->token);
        //     $cek = json_decode($cek);
        // } else {
        //     $cek = (object) ['success' => false];
        // }

        $out = [];

        // if (!$cek->success) {
        //     $status = false;
        //     $out['msg'] = 'Token cloudflare tidak valid';
        //     // DB::con()->insert('log', [
        //     //     'tipe' => 'Error',
        //     //     'entitas ' => 'Anggota',
        //     //     'aksi' => "Login",
        //     //     'tambahan' => json_encode(
        //     //         [
        //     //             'msg' => 'Token cloudflare tidak valid, ada kemungkinan brute force attack',
        //     //             'request' => $Request->username,
        //     //         ]
        //     //     ),
        //     //     'idAnggota ' => 0,
        //     //     'kunci ' => 0,
        //     //     'created_at ' => date('Y-m-d H:i:s'),

        //     // ]);
        //     return json_encode(
        //         ['status' => $status, 'data' => $out]
        //     );
        // }

        $data['admin'] = collect(Crud::table('karyawan')->select()->where('username', $Request->username)->get());
        if ($data['admin']->isEmpty()) {
            $status = false;
            $out['msg'] = 'Nomor Pekerja anda tidak terdaftar';
            // DB::con()->insert('log', [
            //     'tipe' => 'Error',
            //     'entitas ' => 'Anggota',
            //     'aksi' => "Login",
            //     'tambahan' => json_encode(
            //         [
            //             'msg' => 'Nomor Pekerja anda tidak terdaftar',
            //             'request' => $Request->username,
            //         ]
            //     ),
            //     'idAnggota ' => 0,
            //     'kunci ' => 0,

            //     'created_at ' => date('Y-m-d H:i:s'),

            // ]);
        } else {

            $cek = (password_verify($Request->password, $data['admin']->first()->password));
            if ($cek) {
                $status = true;
                // informasi user
                $x = $data['admin']->first();


                $user = array(
                    "id" => $x->id,
                    "nama" => $x->nama,
                    "role"=>$x->role,
                );

                // waktu kadaluarsa token
                $exp_time = time() + 604800; // 1 jam

                // buat token
                $token = array(
                    "iss" => "example.com",
                    "aud" => "example.com",
                    "iat" => time(),
                    "exp" => $exp_time,
                    "sub" => $user,
                );
                $jwt = JWT::encode($token, $GLOBALS['jwtKey'], 'HS256');

                $out['msg'] = 'Selamat Datang Kembali :)';
                // DB::con()->insert('log', [
                //     'tipe' => 'Normal',
                //     'entitas ' => 'Anggota',
                //     'aksi' => "Login",
                //     'tambahan' =>
                //     json_encode(
                //         [
                //             'msg' => 'Selamat Datang Kembali :)',
                //             'request' => $Request->username,
                //         ]
                //     ),
                //     'idAnggota ' => $x->id,
                //     'kunci ' => $x->id,
                //     'created_at ' => date('Y-m-d H:i:s'),

                // ]);
                $out['jwt'] = $jwt;
                $_SESSION['admin'] = (object) $user;
                // dd($_SESSION['admin']);
            } else {
                $status = false;
                $out['msg'] = 'Password anda Salah';
                // DB::con()->insert('log', [
                //     'tipe' => 'Error',
                //     'entitas ' => 'Anggota',
                //     'aksi' => "Login",
                //     'tambahan' =>
                //     json_encode(
                //         [
                //             'msg' => 'Password anda Salah',
                //             'request' => $Request->username,
                //         ]
                //     ),
                //     'idAnggota ' => 0,
                //     'kunci ' => 0,

                //     'created_at ' => date('Y-m-d H:i:s'),

                // ]);
            }
        }
        return json_encode(
            ['status' => $status, 'data' => $out]
        );
    }

    public static function cekToken($jwt)
    {
        // verifikasi token
        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key($GLOBALS['jwtKey'], 'HS256'));

                $user = $decoded->sub;
                $_SESSION['admin'] = $user;
                // user berhasil diotentikasi

            } catch (\UnexpectedValueException $e) {
                // token tidak valid
                $response = [
                    'success' => false,
                    'message' => 'Token tidak valid',
                ];
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode($response);
                die();
            }
        } else {
            // token tidak ditemukan di header
            $response = [
                'success' => false,
                'message' => 'Token tidak ditemukan di header',
            ];
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($response);
            die();
        }
    }


    public function Logout()
    {
        // error_reporting(0);
        session_destroy();
        session_start();
        $msg = new \Plasticbrain\FlashMessages\FlashMessages();
        $msg->setCssClassMap([

            $msg::INFO => 'alert alert-info text-white  alert-dismissible fade show mb-4',
            $msg::SUCCESS => 'alert alert-success text-white alert-dismissible fade show mb-4',
            $msg::WARNING => 'alert alert-warning text-white alert-dismissible fade show  mb-4',
            $msg::ERROR => 'alert alert-danger  text-white alert-dismissible fade show mb-4',
        ]);
        $GLOBALS['msg'] = $msg;
        $GLOBALS['msg']->success('Berhasil');

        echo "<script>location.href = 'Login';</script>";
        die();
    }

    public function Layout($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Home',
            'path' => 'Layout.html',
            'induk' => 'Master',
            'link' => 'Home',
            'icon' => 'fa-home',

        ];

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,

        ]);

        return $data;
    }

    public function Dashboard($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Dashboard',
            'induk' => 'Menu',
            'path' => 'Pages.Dashboard.Index',
            'link' => 'Dashboard',
            'icon' => 'fas fa-fw fa-home',

        ];
        // dd($Session);

        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,

        ]);
    }

    public function DashboardAdmin($Request, $Session, $blade)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => $this->buildAdminDashboardData()
        ]);
    }

    public function DashboardSupervisor($Request, $Session, $blade)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => $this->buildSupervisorDashboardData($Session)
        ]);
    }

    public function DashboardKaryawan($Request, $Session, $blade)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => $this->buildKaryawanDashboardData($Session)
        ]);
    }

    public function DashboardManagement($Request, $Session, $blade)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => $this->buildManagementDashboardData()
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

    private function buildAdminDashboardData(): array
    {
        $db = DB::con();
        $periodeAktif = $db->run(
            "SELECT id, nama_periode
             FROM periode_penilaian
             WHERE status = 'aktif'
             ORDER BY tanggal_mulai DESC, id DESC
             LIMIT 1"
        )[0] ?? null;

        $periodeAktifId = $periodeAktif->id ?? null;
        $knnRows = $this->getLatestKnnRows($periodeAktifId ? intval($periodeAktifId) : null);

        $klasifikasi = [
            'sangat_baik' => 0,
            'baik' => 0,
            'cukup' => 0,
            'kurang' => 0,
            'sangat_kurang' => 0,
        ];

        foreach ($knnRows as $row) {
            $key = strtolower(str_replace(' ', '_', $row->hasil_klasifikasi ?? ''));
            if (isset($klasifikasi[$key])) {
                $klasifikasi[$key]++;
            }
        }

        $recentSql = "SELECT p.id, p.total_nilai, p.klasifikasi, p.updated_at,
                             p.karyawan_id, p.periode_id, pp.nama_periode as periode,
                             k.nama, k.jabatan
                      FROM penilaian p
                      JOIN karyawan k ON k.id = p.karyawan_id
                      LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
                      WHERE p.status = 'selesai'";

        $recentParams = [];
        if ($periodeAktifId) {
            $recentSql .= " AND p.periode_id = ?";
            $recentParams[] = $periodeAktifId;
        }

        $recentSql .= " ORDER BY p.updated_at DESC, k.nama ASC LIMIT 10";
        $recent = empty($recentParams) ? $db->run($recentSql) : $db->safeQuery($recentSql, $recentParams);
        $knnMap = [];
        foreach ($knnRows as $row) {
            $knnMap[$row->karyawan_id . '-' . $row->periode_id] = $row;
        }

        foreach ($recent as $item) {
            $key = ($item->karyawan_id ?? '') . '-' . ($item->periode_id ?? '');
            $knn = $knnMap[$key] ?? null;
            $item->klasifikasi_knn = $knn->hasil_klasifikasi ?? null;
            $item->knn_confidence = $knn->confidence ?? null;
        }

        return [
            'stats' => [
                'total_karyawan' => $db->run("SELECT COUNT(*) as total FROM karyawan WHERE status = 'aktif'")[0]->total ?? 0,
                'total_kriteria' => $db->run("SELECT COUNT(*) as total FROM kriteria WHERE status = 'aktif'")[0]->total ?? 0,
                'total_penilaian' => $periodeAktifId
                    ? ($db->safeQuery("SELECT COUNT(*) as total FROM penilaian WHERE status = 'selesai' AND periode_id = ?", [$periodeAktifId])[0]->total ?? 0)
                    : ($db->run("SELECT COUNT(*) as total FROM penilaian WHERE status = 'selesai'")[0]->total ?? 0),
                'periode_aktif' => $periodeAktif->nama_periode ?? null,
            ],
            'recent' => $recent,
            'klasifikasi' => $klasifikasi,
        ];
    }

    private function buildManagementDashboardData(): array
    {
        $data = $this->buildAdminDashboardData();
        $data['stats']['total_baik'] = ($data['klasifikasi']['sangat_baik'] ?? 0) + ($data['klasifikasi']['baik'] ?? 0);
        $data['stats']['total_perlu_perhatian'] = ($data['klasifikasi']['cukup'] ?? 0) + ($data['klasifikasi']['kurang'] ?? 0);
        return $data;
    }

    private function buildSupervisorDashboardData($Session): array
    {
        $db = DB::con();
        $userId = $Session['admin']->id ?? 0;
        $periodeAktif = $db->run("SELECT id, nama_periode FROM periode_penilaian WHERE status = 'aktif' LIMIT 1")[0] ?? null;
        $periodeAktifId = $periodeAktif->id ?? 0;

        $totalBawahan = $db->safeQuery("SELECT COUNT(*) as total FROM relasi_atasan WHERE id_atasan = ?", [$userId])[0]->total ?? 0;
        $sudahDinilai = $periodeAktifId
            ? ($db->run(
                "SELECT COUNT(*) as total
                 FROM penilaian p
                 JOIN relasi_atasan r ON r.id_karyawan = p.karyawan_id
                 WHERE r.id_atasan = ? AND p.periode_id = ? AND p.status = 'selesai'",
                $userId, $periodeAktifId
            )[0]->total ?? 0)
            : 0;

        $recent = $periodeAktifId
            ? $db->run(
                "SELECT p.id, p.total_nilai, p.klasifikasi, p.updated_at, k.nama, pp.nama_periode as periode
                 FROM penilaian p
                 JOIN relasi_atasan r ON r.id_karyawan = p.karyawan_id
                 JOIN karyawan k ON k.id = p.karyawan_id
                 LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
                 WHERE r.id_atasan = ? AND p.periode_id = ? AND p.status = 'selesai'
                 ORDER BY p.updated_at DESC LIMIT 10",
                $userId, $periodeAktifId
            )
            : [];

        return [
            'stats' => [
                'total_bawahan' => intval($totalBawahan),
                'sudah_dinilai' => intval($sudahDinilai),
                'belum_dinilai' => max(0, intval($totalBawahan) - intval($sudahDinilai)),
            ],
            'recent' => $recent,
        ];
    }

    private function buildKaryawanDashboardData($Session): array
    {
        $db = DB::con();
        $userId = $Session['admin']->id ?? 0;

        $rows = $db->run(
            "SELECT p.id, p.total_nilai, p.klasifikasi, p.updated_at,
                    p.periode_id, pp.nama_periode as periode
             FROM penilaian p
             LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
             WHERE p.karyawan_id = ? AND p.status = 'selesai'
             ORDER BY p.periode_id DESC
             LIMIT 5",
            $userId
        );

        $knnMap = [];
        foreach ($this->getLatestKnnRows() as $row) {
            $knnMap[$row->karyawan_id . '-' . $row->periode_id] = $row;
        }

        foreach ($rows as $row) {
            $row->tanggal_penilaian = !empty($row->updated_at) ? date('d/m/Y H:i', strtotime($row->updated_at)) : '-';
            $key = $userId . '-' . ($row->periode_id ?? '');
            $knn = $knnMap[$key] ?? null;
            $row->klasifikasi_knn = $knn->hasil_klasifikasi ?? null;
        }

        return ['penilaian' => $rows];
    }
}

/*
$period = new DatePeriod(
new DateTime('2010-10-01'),
new DateInterval('P1D'),
new DateTime('2010-10-05')
);
foreach ($period as $key => $value) {
$tgl = $value->format('Y-m-d');
$x = [
'tgl' => $tgl,
'tgl' => $tgl,
];
}
 */
