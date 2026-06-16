<?php

namespace app\Model;

use app\Crud;

class Karyawan extends ModelNew
{
    static $instance = null;
    public $data = null;
    protected $select = '*';
    protected $statement;
    protected $with = [];
    public $cekP = [
        // 'noSurat' => ['key' => 'noSurat', 'label' => 'Nomor Surat'],
        // 'NIP' => ['key' => 'NIP', 'label' => 'NIP'],
    ];
    public function __construct()
    {
        $this->table = "karyawan";
        $this->primary = "id";

        parent::__construct();
        // dd($this->fields);
        foreach ($this->fields as $t => $k) {
            $this->fields->$t->req = true;
        }
        // $this->fields->tanggal->val = date('Y-m-d');
        // $this->fields->ttdKetua->pnj = 6;
        // $this->fields->ttdNotulis->pnj = 6;

        // $this->fields->password->type = "password";
        // $this->fields->password->req = false;
        // $this->fields->harga2->type = "text";
    }

    public function ProsesField($Request)
    {

        // $Request->input->AnggotaHadir = implode(';', $Request->input->AnggotaHadir);
        // $Request->input->idAnggota = $_SESSION['admin']->id;
        // $Request->input->created_at = date('Y-m-d H:i:s');

        $fields = array_keys(get_object_vars($this->fields));
        $input = collect($Request->input)->only($fields)->filter(function ($item) {
            return $item != "";
        });
        // if ($_SESSION['admin']->level == 'Anggota') {
        //     $input = $input->except(['idPengurus', 'ttdPengurus', 'tanggapan ']);
        // }
        return $input->toArray();;
    }
    static public function getIds()
    {
        $get = new self;
        $get->setSelect('idAnggota');
        $get->all();

        return $get->data->pluck('idAnggota')->toArray();
    }
}
