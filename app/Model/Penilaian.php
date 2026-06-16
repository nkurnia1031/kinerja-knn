<?php

namespace app\Model;

class Penilaian extends ModelNew
{
    public $cekP = [];

    public function __construct()
    {
        $this->table = "penilaian";
        $this->primary = "id";

        parent::__construct();

        foreach ($this->fields as $t => $k) {
            $this->fields->$t->req = false;
        }
    }

    public function ProsesField($Request)
    {
        $fields = array_keys(get_object_vars($this->fields));
        if (isset($Request->input->nilai_kriteria) && is_object($Request->input->nilai_kriteria)) {
            $nilaiKriteria = [];
            

            foreach ($Request->input->nilai_kriteria as $kriteriaId => $nilai) {
                $nilaiKriteria[] = [
                    'kriteria_id' => (int) $kriteriaId,
                    'nilai' => is_numeric($nilai) ? (float) $nilai : 0,
                ];
            }

            $Request->input->nilai_kriteria = json_encode($nilaiKriteria);
        }
        $input = collect($Request->input)
            ->only($fields)
            ->filter(function ($item) {
                return $item !== "";
            });

       
        return $input->toArray();
    }
}
