<?php

namespace app;


class PeriodePenilaianController extends Controller
{
    public function __construct()
    {
        $this->judul = "PeriodePenilaian";
        $this->allowedWhere = ['tanggal_mulai', 'tanggal_selesai', 'status'];
        $this->selectFilter = ['status'];
        $this->fulltext = '`nama_periode`';
        $this->mainModel = 'app\Model\PeriodePenilaian';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id','created_at','updated_at'];
        $this->exceptExcel = ['password'];
        $this->tambahan = [
            // 'fieldsAnggota' => ['jabatan', 'alamat', 'ttd', 'nama', 'anjuran'],
            // 'fieldsPengurus' => ['tanggapan', 'ttdPengurus'],

        ];
        $this->Icon = 'fa fa-users';
        $this->Link = 'PeriodePenilaian';
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
        // $x->Addwith('pengurus', "nama", ["idPengurus", 'idAnggota'], "app\Model\AnggotaNew");
        return $x;
    }
    public function setModelAfter($x)
    {
        $periodeIds = collect($x->data)
            ->pluck('id')
            ->filter(function ($id) {
                return !empty($id);
            })
            ->map(function ($id) {
                return intval($id);
            })
            ->values()
            ->toArray();

        $totalKaryawan = intval($this->getDB()->run(
            "SELECT COUNT(DISTINCT r.id_karyawan) AS total
             FROM relasi_atasan r
             JOIN karyawan k ON k.id = r.id_karyawan
             WHERE k.status = 'aktif'"
        )[0]->total ?? 0);

        $progressMap = [];
        if (!empty($periodeIds)) {
            $placeholders = implode(',', array_fill(0, count($periodeIds), '?'));
            $rows = $this->getDB()->run(
                "SELECT
                    p.periode_id,
                    COUNT(DISTINCT p.karyawan_id) AS sudah_dinilai
                 FROM penilaian p
                 JOIN karyawan k ON k.id = p.karyawan_id
                 JOIN relasi_atasan r ON r.id_karyawan = p.karyawan_id
                 WHERE p.status = 'selesai'
                   AND k.status = 'aktif'
                   AND p.periode_id IN ({$placeholders})
                 GROUP BY p.periode_id",
                ...$periodeIds
            );

            foreach ($rows as $row) {
                $progressMap[intval($row->periode_id ?? 0)] = intval($row->sudah_dinilai ?? 0);
            }
        }

        $x->data = $x->data->map(function ($item) use ($progressMap, $totalKaryawan) {
            $periodeId = intval($item->id ?? 0);

            // Progress diambil dari jumlah karyawan bawahan aktif yang sudah punya
            // penilaian berstatus 'selesai' pada periode terkait, dibandingkan
            // total karyawan aktif yang tercatat di relasi_atasan.
            $item->total_karyawan = $totalKaryawan;
            $item->sudah_dinilai = intval($progressMap[$periodeId] ?? 0);
            $item->belum_dinilai = max($item->total_karyawan - $item->sudah_dinilai, 0);
            $item->progress = $item->total_karyawan > 0
                ? round(($item->sudah_dinilai / $item->total_karyawan) * 100, 2)
                : 0;

            return $item;
        });
        return $x;
    }
    public function BeforeUpdate($model, $Request){
        if(!empty($Request->input->status) && $Request->input->status=='aktif'){
            $model->getDB()->update( $model->getTable(),['status'=>'selesai'], ['status'=>'aktif']);
       }
        return    $Request;
    }
    public function BeforeInsert($model, $Request){
        if(!empty($Request->input->status) && $Request->input->status=='aktif'){
             $model->getDB()->update( $model->getTable(),['status'=>'selesai'], ['status'=>'aktif']);
        }
        return $Request;
    }
    public function setModelForExcel($x)
    {
        $x = $x->map(function ($item) {

            // $item->pengawasHadir = implode(',', $item->pengawasHadir);
            return $item;
        });
        return $x;
    }
    public function View($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Form Periode Penilaian',
            'path' => 'Pages.PeriodePenilaian.ModalForm',
            'link' => 'PeriodePenilaian-View',
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
            $data['path'] = 'Pages.PeriodePenilaian.View';
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
