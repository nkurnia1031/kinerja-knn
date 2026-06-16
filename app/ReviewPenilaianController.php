<?php

namespace app;


class ReviewPenilaianController extends Controller
{
    public function __construct()
    {
        $this->judul = "ReviewPenilaian";
        $this->allowedWhere = ['tanggal', 'karyawan_id', 'penilai_id', 'periode_id','klasifikasi'];
        $this->selectFilter = ['karyawan_id', 'penilai_id', 'periode_id','klasifikasi'];
        $this->fulltext = '`nama`, `nohp`, `username`';
        $this->mainModel = 'app\Model\ReviewPenilaian';
        $this->hapusHuruf = [];
        $this->formatUang = [];
        $this->except = ['id'];
        $this->exceptExcel = ['password'];
        $this->tambahan = [
            // 'fieldsAnggota' => ['jabatan', 'alamat', 'ttd', 'nama', 'anjuran'],
            // 'fieldsPengurus' => ['tanggapan', 'ttdPengurus'],

        ];
        $this->Icon = 'fa fa-users';
        $this->Link = 'ReviewPenilaian';
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
        $x->Addwith('karyawan', "nama , jabatan ,pekerjaan", ["karyawan_id", "id"], "app\Model\Karyawan");
        // Join with penilai (atasan) table
        $x->Addwith('penilai', "nama as nama_penilai", ["penilai_id", "id"], "app\Model\Karyawan");
        // Join with periode_penilaian table
        $x->Addwith('periode', "nama_periode", ["periode_id", "id"], "app\Model\PeriodePenilaian");
        if (!empty($this->Request->id)) {
            $x->setStatement($x->getStatement()->with(" {$x->getTable()}.id = ?", $this->Request->id));
        }
        if ($_SESSION['admin']->role=='atasan') {
            $x->setStatement($x->getStatement()->with(" {$x->getTable()}.penilai_id = ?", $_SESSION['admin']->id));
        }
        return $x;
    }
    public function setFilter($filter)
    {
        // 1. Inisialisasi Processor
        $processor = new \app\Filters\FilterProcessor($this->getDB());

        // 2. Daftarkan strategi apa saja yang ingin diproses
        $processor
            ->addStrategy('karyawan_id', new \app\Filters\Strategies\KaryawanFilterStrategy())
            ->addStrategy('penilai_id', new \app\Filters\Strategies\KaryawanFilterStrategy())
            ->addStrategy('periode_id', new \app\Filters\Strategies\PeriodeFilterStrategy());


        // 3. Jalankan dan kembalikan hasilnya
        return  $processor->process($filter);
    }
    public function setModelAfter($x)
    {
        $kriteriaList = null;
        if (!empty($this->Request->id)) {
            $kriteriaList = $this->getDB()->run(
                "SELECT id, nama, bobot FROM kriteria WHERE status = 'aktif' ORDER BY urutan ASC"
            );
        }

        $x->data = $x->data->map(function ($item) use ($kriteriaList) {
            if (!empty($this->Request->id)) {
                $nilaiKriteria = $item->nilai_kriteria;
                if (is_string($nilaiKriteria)) {
                    $nilaiKriteria = json_decode($nilaiKriteria, true);
                }
                if (!is_array($nilaiKriteria)) {
                    $nilaiKriteria = [];
                }

                $nilaiByKriteriaId = [];
                foreach ($nilaiKriteria as $key => $val) {
                    // Shape: [{"kriteria_id":1,"nilai":4}, ...]
                    if (is_array($val) && isset($val['kriteria_id'])) {
                        $kid = (int) $val['kriteria_id'];
                        $nilaiByKriteriaId[$kid] = is_numeric($val['nilai'] ?? null) ? (float) $val['nilai'] : 0.0;
                        continue;
                    }

                    // Shape: {"1":4,"2":3,...} (or array with numeric keys)
                    if (is_numeric($key) && is_numeric($val)) {
                        $nilaiByKriteriaId[(int) $key] = (float) $val;
                    }
                }

                $detail = [];
                foreach (($kriteriaList ?? []) as $kr) {
                    $kid = (int) ($kr->id ?? 0);
                    $bobot = is_numeric($kr->bobot ?? null) ? (float) $kr->bobot : 0.0;
                    $nilai = (float) ($nilaiByKriteriaId[$kid] ?? 0);

                    $detail[] = [
                        'nama_kriteria' => $kr->nama ?? '',
                        'bobot' => $bobot,
                        'nilai' => $nilai,
                        'nilai_bobot' => round(($nilai * $bobot) / 100, 2),
                    ];
                }

                $item->nilai_kriteria = $nilaiKriteria;
                $item->detail_kriteria = $detail;
            }

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
            'judul' => 'Detail Review Penilaian',
            'path' => 'Pages.ReviewPenilaian.ModalForm',
            'link' => 'ReviewPenilaian-View',
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
            $data['path'] = 'Pages.ReviewPenilaian.View';
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
