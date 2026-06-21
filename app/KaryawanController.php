<?php

namespace app;


class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->judul = "Karyawan";
        $this->allowedWhere = ['status', 'role', 'pekerjaan', 'tanggal_bergabung'];
        $this->selectFilter = ['status', 'role', 'pekerjaan'];
        $this->fulltext = '`nama`, `jabatan`, `pekerjaan`, `status`, `username`';
        $this->mainModel = 'app\Model\Karyawan';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id','created_at','updated_at'];
        $this->exceptExcel = ['password'];
        $this->tambahan = [
            // 'fieldsAnggota' => ['jabatan', 'alamat', 'ttd', 'nama', 'anjuran'],
            // 'fieldsPengurus' => ['tanggapan', 'ttdPengurus'],

        ];
        $this->Icon = 'fa fa-users';
        $this->Link = 'Karyawan';
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
        $x->data = $x->data->map(function ($item) {

            $item->password = '************';


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
    public function View($Request, $Session, $blade)
    {
        $data = [
            'judul' => 'Form Karyawan',
            'path' => 'Pages.Karyawan.ModalForm',
            'link' => 'Karyawan-View',
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
            $data['path'] = 'Pages.Karyawan.View';
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
