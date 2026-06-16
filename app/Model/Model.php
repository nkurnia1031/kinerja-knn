<?php

namespace app\Model;

use app\Crud as Crud;
use app\Fungsi as Fungsi;

abstract class Model
{
    private static $instance = null;

    public $table;
    public $data;
    public $primary;
    public $fields;
    public $relasi;
    public $cekP;
    public $created_at = "created_at";
    public $updated_at = "updated_at";

    public function __construct()
    {
        $this->fields = json_decode(json_encode(Fungsi::fields($this->table, new Crud)));

        // $this->data = $this->all();
        // $this->data = $this->all();
        // dd($this->fields);

    }
    public static function Mulai()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    public function table()
    {
        return Crud::table($this->table);
    }
    public function cekUnik($a, $input, $Request)
    {
        if (array_key_exists($a['key'], $input)) {

            if (!empty($input[$a['key']])) {
                $cek = collect(Crud::table($this->table)->select()->where($a['key'], $input[$a['key']])->get());
                if ($cek->isNotEmpty()) {
                    $GLOBALS['msg']->error("<strong>Gagal</strong>: $a[label] sudah terdaftar, silahkan menggunakan $a[label] yang lain");
                    $link = $_SERVER['HTTP_REFERER'];
                    if (isset($Request->link)) {
                        $link = $Request->link;
                    }
                    echo "<script>location.href='$link'</script>";
                    die();
                }
            }
        }
    }
    public function cekUnik2($a, $input, $Request)
    {
        if (array_key_exists($a['key'], $input)) {
            if (isset($Request->cek)) {

                $Request->cek = collect($Request->cek)->filter(function ($value, $key) {
                    return $value != "";
                })->toArray();
            }

            if (isset($Request->cek[$a['key']]) && !is_null($Request->cek[$a['key']])) {

                $cek = Crud::table($this->table)->select()->where($a['key'], $input[$a['key']]);
                $cek = $cek->where($a['key'], "!=", $Request->cek[$a['key']]);

                $cek = collect($cek->get());
                if ($cek->isNotEmpty()) {
                    $link = $_SERVER['HTTP_REFERER'];
                    if (isset($Request->link)) {
                        $link = $Request->link;
                    }

                    $GLOBALS['msg']->error("<strong>Gagal</strong>: $a[label] sudah terdaftar, silahkan menggunakan $a[label] yang lain");
                    echo "<script>location.href='$link'</script>";
                    die();
                }
            }
        }
    }

    abstract public function ProsesField($Request);
    public function addData($Request)
    {

        $input = $this->ProsesField($Request);
        foreach ($this->cekP as $k) {

            $this->cekUnik($k, $input, $Request);
        }
        //   $input = $this->defaultInsert($input);
        $input[$this->created_at] = date('Y-m-d H:i:s');
        return Crud::table($this->table)->insert($input)->execute();
    }
    public function upData($Request)
    {

        $input = $this->ProsesField($Request);

        foreach ($this->cekP as $k) {
            $this->cekUnik2($k, $input, $Request);
        }
        $input[$this->updated_at] = date('Y-m-d H:i:s');

        // $input = $this->defaultUp($input);
        Crud::table($this->table)->update($input)->where($this->primary, $Request->key)->execute();
    }
    public function delData($key)
    {
        return Crud::table($this->table)->delete()->where($this->primary, $key)->execute();
    }
    public function defaultInsert($input)
    {
        $input['created_at'] = date('Y-m-d H:i:s');
        $input['oleh'] = $_SESSION['admin']->idUser;
        return $input;
    }
    public function defaultUp($input)
    {
        $input['updated_at'] = date('Y-m-d H:i:s');
        $input['oleh'] = $_SESSION['admin']->idUser;
        return $input;
    }
    public function all($new = null)
    {
        if (isset($new)) {
            $data = collect($new->get());
        } else {
            $data = collect($this->table()->select()->get());
        }
        if (isset($this->relasi)) {
            $relasi = $this->relasi;
            $data = $data->map(function ($item) use ($relasi) {
                foreach ($relasi as $v => $k) {
                    $r = $k[1];
                    $item->$v = collect(Crud::table($k[2])->select()->where($k[0], $item->$r)->get());
                }
                return $item;
            });
        }

        return $data;
    }
    public function insert($input)
    {
        Crud::table($this->table)->insert($input)->execute();
    }
}
