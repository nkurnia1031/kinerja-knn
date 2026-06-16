<?php

namespace app\Model;

class Kriteria extends ModelNew
{
    public $cekP = [
        ['key' => 'kode', 'label' => 'Kode'],
    ];

    public function __construct()
    {
        $this->table = "kriteria";
        $this->primary = "id";

        parent::__construct();

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
