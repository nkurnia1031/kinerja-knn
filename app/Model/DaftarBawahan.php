<?php

namespace app\Model;

class DaftarBawahan extends ModelNew
{
    public $cekP = [];

    public function __construct()
    {
        $this->table = "v_daftar_bawahan";
        $this->primary = "relasi_id";

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
