<?php

namespace app\Model;

class RelasiAtasan extends ModelNew
{
    public $cekP = [];

    public function __construct()
    {
        $this->table = "relasi_atasan";
        $this->primary = "id";

        parent::__construct();

        $this->select = "
            relasi_atasan.*,
            karyawan.nama as nama_karyawan,
            karyawan.jabatan as jabatan_karyawan,
            atasan.nama as nama_atasan,
            atasan.jabatan as jabatan_atasan
        ";
        $this->join = "
            LEFT JOIN karyawan as karyawan ON karyawan.id = relasi_atasan.id_karyawan
            LEFT JOIN karyawan as atasan ON atasan.id = relasi_atasan.id_atasan
        ";

        foreach ($this->fields as $t => $k) {
            $this->fields->$t->req = false;
        }
    }

    public function ProsesField($Request)
    {
        $fields = array_keys(get_object_vars($this->fields));

        return collect($Request->input)
            ->only($fields)
            ->filter(function ($item) {
                return $item !== "";
            })
            ->toArray();
    }
}
