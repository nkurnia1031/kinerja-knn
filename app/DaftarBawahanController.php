<?php

namespace app;


class DaftarBawahanController extends Controller
{
    public function __construct()
    {
        $this->judul = "DaftarBawahan";
        $this->allowedWhere = ['tanggal'];
        $this->selectFilter = [];
        $this->fulltext = '`nama`, `nohp`, `username`';
        $this->mainModel = 'app\Model\DaftarBawahan';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id'];
        $this->exceptExcel = ['password'];
        $this->tambahan = [
            // 'fieldsAnggota' => ['jabatan', 'alamat', 'ttd', 'nama', 'anjuran'],
            // 'fieldsPengurus' => ['tanggapan', 'ttdPengurus'],

        ];
        $this->Icon = 'fa fa-users';
        $this->Link = 'DaftarBawahan';
        $this->files = [];
        $this->SortBy = 'relasi_id';
        $this->selectWhere = [];

        // $this->tambahan['pengawas'] = $this->getDB()->run("SELECT nama FROM anggota WHERE level IN ('Pengawas')  ORDER BY nama ASC");
        // $this->tambahan['pengawas'] = collect($this->tambahan['pengawas'])->map(function ($item) {
        //     return $item = $item->nama;
        // })->toArray();
    }
    public function setModelBefore($x)
    {
        // $x->Addwith('anggota', "nama", ["idAnggota", 'idAnggota'], "app\Model\AnggotaNew");
        // $x->Addwith('pengurus', "nama", ["idPengurus", 'idAnggota'], "app\Model\AnggotaNew");
        return $x;
    }
    public function setModelAfter($x)
    {
        $x->data = $x->data->map(function ($item) {



            return $item;
        });
        return $x;
    }
    public function setModelForExcel($x)
    {
        $x = $x->map(function ($item) {

            // $item->pengawasHadir = implode(',', $item->pengawasHadir);
            return $item;
        });
        return $x;
    }
    public function IndexApi($Request, $Session, $blade)
    {
        $periodeAktif = $this->getPeriodeAktif();
        $atasanId = intval($Session['admin']->id ?? 0);

        $data = $this->getDaftarBawahan($atasanId, $periodeAktif->id ?? null);

        echo json_encode([
            'status' => true,
            'data' => [
                'fields' => [],
                'data' => $data,
                'filter' => [],
                'primary' => 'id',
                'request' => [],
                'tambahan' => [
                    'periode_aktif' => $periodeAktif,
                ],
            ]
        ]);
    }
    private function getPeriodeAktif()
    {
        return $this->getDB()->run(
            "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai, status
             FROM periode_penilaian
             WHERE status = 'aktif'
             ORDER BY tanggal_mulai DESC
             LIMIT 1"
        )[0] ?? null;
    }
    private function getDaftarBawahan(int $atasanId, ?int $periodeAktifId = null): array
    {
        if ($atasanId <= 0) {
            return [];
        }

        $params = [$atasanId];
        $joinAktif = "LEFT JOIN penilaian paktif ON 1 = 0";
        if (!empty($periodeAktifId)) {
            $joinAktif = "LEFT JOIN penilaian paktif 
                ON paktif.karyawan_id = k.id 
                AND paktif.periode_id = ?";
            array_unshift($params, $periodeAktifId);
        }

        $rows = $this->getDB()->run(
            "SELECT
                k.id,
                k.nama,
                k.jabatan,
                k.pekerjaan,
                k.tanggal_bergabung,
                k.status,
                plast.total_nilai AS nilai_terakhir,
                plast.klasifikasi AS klasifikasi_terakhir,
                CASE WHEN paktif.id IS NULL THEN 0 ELSE 1 END AS sudah_dinilai
             FROM relasi_atasan r
             JOIN karyawan k ON k.id = r.id_karyawan
             {$joinAktif}
             LEFT JOIN penilaian plast ON plast.id = (
                SELECT p2.id
                FROM penilaian p2
                WHERE p2.karyawan_id = k.id AND p2.status = 'selesai'
                ORDER BY p2.updated_at DESC
                LIMIT 1
             )
             WHERE r.id_atasan = ? AND k.status = 'aktif'
             ORDER BY k.nama ASC",
            ...$params
        );

        return collect($rows)->map(function ($item) {
            $item->sudah_dinilai = intval($item->sudah_dinilai ?? 0) === 1;
            return $item;
        })->toArray();
    }
    public function View($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Detail Daftar Bawahan',
            'path' => 'Pages.DaftarBawahan.ModalForm',
            'link' => 'DaftarBawahan-View',
            'icon' => 'fa fa-lock',

        ];
        $data['num'] = '';
        if (!empty($Request->num)) {
            $data['num'] = $Request->num;
        }
        if (!empty($Request->key)) {
            /** @var ModelNew $tb */
            $tb = new $this->mainModel;
            $tb->setStatement($tb->getStatement()->with(' id= ?', $Request->key));
            $tb->get();
            if (empty($tb->data)) {
                return $blade->run('Pages.Error.404', [
                    'Request' => $Request,
                    'Session' => $Session,
                ]);
            }
            $data['device'] = [];
            $field = collect($tb->fields)->except([...$this->except, 'password']);
            foreach ($field as $key => $value) {
                $data['device'][] = ['label' => $value->label, 'val' => $tb->data[0]->$key];
            }
            $data['path'] = 'Pages.DaftarBawahan.View';
        }
        return $blade->run($data['path'], [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,

        ]);
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
