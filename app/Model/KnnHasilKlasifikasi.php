<?php

namespace app\Model;

class KnnHasilKlasifikasi extends ModelNew
{
    public $cekP = [];

    public function __construct()
    {
        $this->table = "knn_hasil_klasifikasi";
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
