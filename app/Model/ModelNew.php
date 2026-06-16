<?php

namespace app\Model;

use app\DB as DB;
use app\Fungsi as Fungsi;
use \ParagonIE\EasyDB\EasyStatement;

abstract class ModelNew
{
    private static $instance = null;

    protected $table;
    public $primary;
    public $fields;
    protected $relasi;
    public $cekP;
    protected $DB;
    protected $select = '*';
    protected $join = '';
    protected $statement;
    protected $statementAfter;

    protected $with = [];
    public $data = null;

    protected $created_at = "created_at";
    protected $updated_at = "updated_at";

    public function __construct()
    {
        $this->DB = new DB;
        $this->statement = EasyStatement::open();
        $this->statementAfter = EasyStatement::open()->with('');

        $this->fields = json_decode(json_encode($this->getFields($this->table)));


        // $this->data = $this->all();
        // $this->data = $this->all();
        // dd($this->fields);

    }
    public function find($key, $tb = null)
    {
        if (empty($tb)) {
            $tb = $this->table;
        }
        $hasil = $this->DB->db->run("SELECT * FROM $tb WHERE $this->primary = ? LIMIT 1", $key);
        if (count($hasil) > 0) {
            return $hasil[0];
        } else {
            return null;
        }
    }
    public function getDB()
    {
        return $this->DB->db;
    }
    public function getTable()
    {
        return $this->table;
    }
    public function getFields($tb)
    {
        $hasil = [];
        $db = $GLOBALS['db'];
        $DB2 = new DB;

        $fields = $this->DB->db->run("SELECT * FROM information_schema.columns  where TABLE_NAME='$tb' and table_schema='$db'");
        foreach ($fields as $k) {
            $x = [];
            $x['name'] = $k->COLUMN_NAME;
            $x['label'] = $k->COLUMN_COMMENT;
            $x['small'] = "";
            $x['type'] = Fungsi::$type[$k->DATA_TYPE];
            $x['max'] = $k->CHARACTER_MAXIMUM_LENGTH;

            $x['pnj'] = 12;
            $x['val'] = null;
            $x['req'] = false;
            $x['red'] = "";
            // $x['up'] = true;
            $x['tb'] = $tb;

            $hasil[$k->COLUMN_NAME] = $x;
        }
        return collect($hasil);
    }

    public function cekUnik($a, $input, $Request)
    {
        if (array_key_exists($a['key'], $input)) {

            if (!empty($input[$a['key']])) {
                $cek = collect($this->DB->db->run("SELECT $a[key] FROM $this->table WHERE $a[key] = ?", $input[$a['key']]));
                if ($cek->isNotEmpty()) {
                    $out = [];
                    $out['msg'] = "<strong>Gagal</strong>: $a[label] sudah terdaftar, silahkan menggunakan $a[label] yang lain";
                    echo json_encode(
                        ['status' => false, 'data' => $out]
                    );
                    die();
                }
            }
        }
    }
    public function cekUnik2($a, $input, $Request)
    {
        if (array_key_exists($a['key'], $input)) {
            if (isset($Request->cek)) {
                // $Request->cek = collect($Request->cek)->filter(function ($value, $key) {
                //     return $value != "";
                // })->toArray();
                $Request->cek = collect($Request->cek)->toArray();
            }

            // dd($Request->cek);

            if (isset($Request->cek[$a['key']]) && !is_null($Request->cek[$a['key']])) {
                $cek = $this->DB->db->run(
                    "SELECT $a[key] FROM $this->table WHERE $a[key] = ? and $a[key] != ?",
                    $input[$a['key']],
                    $Request->cek[$a['key']]
                );

                $cek = collect($cek);
                if ($cek->isNotEmpty()) {
                    $out = [];
                    $out['msg'] = "<strong>Gagal</strong>: $a[label] sudah terdaftar, silahkan menggunakan $a[label] yang lain";
                    echo json_encode(
                        ['status' => false, 'data' => $out]
                    );
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
        // $input[$this->created_at] = date('Y-m-d H:i:s');

        return $this->DB->db->insertReturnId($this->table, $input);;
    }
    public function upData($Request, $where)
    {

        $input = $this->ProsesField($Request);

        foreach ($this->cekP as $k) {
            $this->cekUnik2($k, $input, $Request);
        }

        // $input[$this->updated_at] = date('Y-m-d H:i:s');

        // $input = $this->defaultUp($input);
        return $this->DB->db->update($this->table, $input, $where);;

        // DB::table($this->table)->update($input)->where($this->primary, $Request->key)->execute();
    }
    public function delData($where)
    {
        $this->DB->db->delete($this->table, $where);
    }
    public function get(): void
    {
        try {
            // dd("SELECT  $this->select FROM $this->table WHERE  $this->statement $this->statementAfter", array_merge($this->statement->values(), $this->statementAfter->values()));
            $this->data = $this->DB->db->safeQuery("SELECT  $this->select FROM $this->table $this->join WHERE  $this->statement $this->statementAfter", array_merge($this->statement->values(), $this->statementAfter->values()));
        } catch (\PDOException $e) {
            echo $e;
        }
    }
    public function Addwith($item, $select = "*", $relasi, $model, $add = null)
    {
        $this->with[$item] = [$select, $relasi, new $model, $add];
        return $this;
    }
    public function setSelect($select)
    {
        $this->select = $select;
        return $this;
    }
    public function setJoin($join)
    {
        $this->join = $join;
        return $this;
    }
    public function setStatement($statement)
    {
        $this->statement = $statement;
        return $this;
    }
    public function setStatementAfter($statement)
    {
        $this->statementAfter = $statement;
        return $this;
    }
    public function getStatement()
    {
        return $this->statement;
    }
    public function getStatementAfter()
    {
        return $this->statementAfter;
    }
    public function all()
    {
        $this->get();
        $this->data = collect($this->data);
        $with = $this->with;
        $this->data = $this->data->map(function ($item) use ($with) {
            foreach ($with as $key => $k) {
                $v = $k[1][0];
                $v2 = $k[1][1];
                $where = EasyStatement::open()->with(" $v2=? $k[3]", $item->$v);
                $k[2]->setStatement($where);
                $k[2]->setSelect($k[0]);
                $k[2]->all();
                $item->$key = $k[2]->data;
            }

            return $item;
        });
    }
}
