<?php

namespace app\Model;

class LaporanKaryawan extends ModelNew
{
    public $cekP = [];

    public function __construct()
    {
        $this->table = "v_karyawan_aktif";
        $this->primary = "id";

        parent::__construct();

        foreach ($this->fields as $t => $k) {
            $this->fields->$t->req = false;
        }
    }

    public function ProsesField($Request)
    {
        return [];
    }
}
