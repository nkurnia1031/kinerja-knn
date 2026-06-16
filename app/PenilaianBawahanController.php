<?php

namespace app;

use \ParagonIE\EasyDB\EasyStatement;

class PenilaianBawahanController extends Controller
{
    public function __construct()
    {
        $this->judul = "PenilaianBawahan";
        $this->allowedWhere = ['tanggal'];
        $this->selectFilter = [];
        $this->fulltext = '`nama`, `nohp`, `username`';
        $this->mainModel = 'app\Model\PenilaianBawahan';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id'];
        $this->exceptExcel = ['password'];
        $this->tambahan = [
            // 'fieldsAnggota' => ['jabatan', 'alamat', 'ttd', 'nama', 'anjuran'],
            // 'fieldsPengurus' => ['tanggapan', 'ttdPengurus'],

        ];
        $this->Icon = 'fa fa-users';
        $this->Link = 'PenilaianBawahan';
        $this->files = [];
        $this->SortBy = 'id';
        $this->selectWhere = [];

        // $this->tambahan['pengawas'] = $this->getDB()->run("SELECT nama FROM anggota WHERE level IN ('Pengawas')  ORDER BY nama ASC");
        // $this->tambahan['pengawas'] = collect($this->tambahan['pengawas'])->map(function ($item) {
        //     return $item = $item->nama;
        // })->toArray();
    }
    public function setModelBefore($x)
    {
        // $x->Addwith('anggota', "nama", ["idAnggota", 'idAnggota'], "app\Model\AnggotaNew");
        $x->setJoin("join karyawan ON karyawan.id = {$x->getTable()}.karyawan_id ");
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
        $periodeId = intval($Request->periode_id ?? 0);
        $karyawanId = intval($Request->karyawan_id ?? 0);

        if ($periodeId > 0 && $karyawanId > 0) {
            $detail = $this->getDetailPenilaian($periodeId, $karyawanId);

            echo json_encode([
                'status' => true,
                'data' => [
                    'fields' => [],
                    'data' => $detail ? [$detail] : [],
                    'filter' => [],
                    'primary' => 'id',
                    'request' => [],
                    'tambahan' => [],
                ]
            ]);
            return;
        }

        $periodeAktif = $this->getPeriodeAktif();
        $data = $this->getDaftarBawahan($Session, $periodeAktif->id ?? null);

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
    public function penambahanInput($Request)
    {
        $Request = parent::penambahanInput($Request);

        if (!isset($Request->input)) {
            $Request->input = (object) [];
        }

        $Request->input->penilai_id = $_SESSION['admin']->id ?? null;
        $Request->input->status = 'selesai';
        $Request->input->is_training = !empty($Request->input->is_training) ? 1 : 0;

        return $Request;
    }
    public function insert($model, $Request, $output)
    {
        $periodeId = intval($Request->input->periode_id ?? 0);
        $karyawanId = intval($Request->input->karyawan_id ?? 0);

        if ($periodeId > 0 && $karyawanId > 0) {
            $existing = $this->getExistingPenilaianId($periodeId, $karyawanId);
            if (!empty($existing)) {
                $Request->key = $existing;
                $Request->update = true;
                $where = EasyStatement::open()->with("{$model->getTable()}.{$model->primary} = ?", $existing);
                $model->setStatement($where);
                $model->all();
                $Request->cek = json_decode(json_encode($model->data->first()), true);
                $Request = $this->BeforeUpdate($model, $Request);
                $model->upData($Request, [$model->primary => $existing]);
                $this->afterUpdate($model, $Request, $existing);

                $model->setStatement(EasyStatement::open()->with("{$model->getTable()}.{$model->primary} = ?", $existing));
                $model->all();

                $output['msg'] .= "Data Berhasil Update";
                $output['filter'] = [];
                $output['data'] = $model->data->first();
                return $output;
            }
        }

        return parent::insert($model, $Request, $output);
    }
    private function getPeriodeAktif()
    {
        return $this->getDB()->run(
            "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai, status
             FROM periode_penilaian
             WHERE status = 'aktif'
             ORDER BY tanggal_mulai DESC
             LIMIT 1"
        )[0];
    }
    private function getExistingPenilaianId($periodeId, $karyawanId)
    {
        $row = $this->getDB()->run(
            "SELECT id FROM penilaian WHERE periode_id = ? AND karyawan_id = ? LIMIT 1",
            $periodeId,
            $karyawanId
        );
        if (empty($row)) {
            return null;
        }

        return $row[0]->id ?? null;
    }
    private function getDetailPenilaian($periodeId, $karyawanId)
    {
        $row = $this->getDB()->run(
            "SELECT id, periode_id, karyawan_id, penilai_id, nilai_kriteria, total_nilai, klasifikasi, catatan, status, is_training
             FROM penilaian
             WHERE periode_id = ? AND karyawan_id = ?
             LIMIT 1",
            $periodeId,
            $karyawanId
        );

        if (!$row) {
            return null;
        }

        $nilaiKriteria = json_decode($row[0]->nilai_kriteria ?? '[]', true) ?? [];
        $row[0]->nilai_kriteria = $nilaiKriteria;

        return $row[0];
    }
    private function getDaftarBawahan($Session, $periodeAktifId = null)
    {
        $atasanId = intval($Session['admin']->id ?? 0);

        if ($atasanId <= 0) {
            return [];
        }

        $params = [];
        $joinPenilaian = '';
        if (!empty($periodeAktifId)) {
            $joinPenilaian = "LEFT JOIN penilaian p ON p.karyawan_id = k.id AND p.periode_id = ?";
            $params[] = $periodeAktifId;
        } else {
            $joinPenilaian = "LEFT JOIN penilaian p ON 1 = 0";
        }
        if (in_array($Session['admin']->role, ['admin', 'pimpinan'])) {
            $whereAtasan = "";
        } else {
            $whereAtasan = "r.id_atasan = ? AND";
            $params[] = $atasanId;
        }
        $sql = "SELECT
                k.id,
                k.nama,
                k.jabatan,
                k.pekerjaan,
                k.status,
                k.role,
                NULL AS foto,
                p.id AS penilaian_id,
                p.total_nilai,
                p.klasifikasi,
                p.catatan,
                p.is_training,
                p.updated_at AS tanggal_penilaian,
                CASE WHEN p.id IS NULL THEN 0 ELSE 1 END AS sudah_dinilai
             FROM relasi_atasan r
             JOIN karyawan k ON k.id = r.id_karyawan
             {$joinPenilaian}
             WHERE {$whereAtasan} k.status = 'aktif'
             ORDER BY k.nama ASC";
            //  dd($sql,$params);
        $rows = $this->getDB()->run(
            $sql,
            ...($params)
        );
        return collect($rows)->map(function ($item) use ($periodeAktifId) {
            $item->sudah_dinilai = intval($item->sudah_dinilai) === 1;
            $item->is_training = intval($item->is_training ?? 0) === 1;
            $item->can_edit = !empty($periodeAktifId) && !empty($item->penilaian_id);
            return $item;
        })->toArray();
    }
    public function View($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Form Penilaian Bawahan',
            'path' => 'Pages.PenilaianBawahan.ModalForm',
            'link' => 'PenilaianBawahan-View',
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
            $data['path'] = 'Pages.PenilaianBawahan.View';
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
