<?php

namespace app;


use app\Fungsi;
use app\DB;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;



class Upload
{
    public function __construct() {}
    public function Index($Request, $Session)
    {
        $data = [
            'judul' => 'Upload Data',
            'induk' => 'Master',
            'path' => 'Pages.Anggota.Index',
            'link' => 'Upload',
            'icon' => 'fas fa-fw fa-upload',

        ];
        return $GLOBALS['blade']->run('Pages.Upload', [
            'data' => $data,
            'Request' => $Request,
            'Session' => $Session,


        ]);
    }
    public function ProsesUpload($Request, $Session)
    {
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $nama = $file['name'];
            $_FILES['file'];


            move_uploaded_file($file['tmp_name'], "upload/data.xlsx");
        } else {
            echo "<script>window.location.href = 'Upload-data'</script>";
            die();
        }

        $reader = ReaderEntityFactory::createReaderFromFile('upload/data.xlsx');

        $reader->open('upload/data.xlsx');
        $data = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {

                $cells = $row->getCells();
                $isi = [];
                foreach ($cells as $k) {
                    $x = $k->getValue();
                    if ($x instanceof \DateTime) {
                        $x = $k->getValue()->format('Y-m-d');
                    }
                    if ($x == '') {
                        $x = null;
                    }
                    array_push($isi, $x);
                }
                $data[] = $isi;
            }
        }
        $keys = $data[0];
        unset($data[0]);

        $keysCount = count($keys);
        $data = array_map(function ($item) use ($keysCount, $keys) {
            // Jika $keys lebih banyak dari $item, tambahkan null atau ""
            while (count($item) < $keysCount) {
                $item[] = null; // Anda juga bisa menggunakan "" sesuai kebutuhan
            }
            // Jika $item lebih banyak dari $keys, potong array tersebut
            $item = array_slice($item, 0, $keysCount);

            return array_combine($keys, $item);
        }, $data);
        $data2 = [];
        foreach ($data  as $key => $value) {
            array_push($data2, [
                'tipe' => 'Normal',
                'entitas ' => 'elmotion',
                'aksi' => 'Menambahkan Data',
                'tambahan' => json_encode(['input' => $value]),
                'idAnggota ' => $_SESSION['admin']->id,
                'created_at ' => date('Y-m-d H:i:s'),
            ]);
        }

        $reader->close();
        DB::con()->insertMany('log', $data2);

        // $this->ProsesSHU($data);
        // $this->ProsesSaldoAwal($data);
        // $this->ProsesModalPenyertaan($data);
        echo "<script>alert('Berhasil')</script>";

        echo "<script>window.location.href = 'Upload-data'</script>";
        die();
    }
    public function ProsesSHU($data)
    {
        try {
            foreach ($data as $k) {
                if (isset($k['shu']) && is_numeric($k['shu'])) {

                    $idAnggota = DB::con()->cell(
                        "SELECT idAnggota FROM anggota WHERE nomorPekerja = ?",
                        $k['nopek']
                    );
                    if (!empty($idAnggota)) {
                        DB::con()->delete('shu', [
                            'idAnggota' => $idAnggota,
                            'tahun' => $_REQUEST['tahun'],
                        ]);
                        DB::con()->insert('shu', [
                            'idAnggota' => $idAnggota,
                            'tahun' => $_REQUEST['tahun'],
                            'shu' => $k['shu']
                        ]);
                    }
                }
            }
            DB::con()->insert('log', [
                'tipe' => 'Normal',
                'entitas ' => 'SHU',
                'aksi' => "Import Data SHU Excel",
                'tambahan' => '',
                'idAnggota ' => $_SESSION['admin']->idAnggota,
                'created_at ' => date('Y-m-d H:i:s'),

            ]);
        } catch (\Throwable $th) {
            DB::con()->insert('log', [
                'tipe' => 'Error',
                'entitas ' => 'SHU',
                'aksi' => 'Import Data SHU Excel',
                'tambahan' => $th->getMessage(),
                'idAnggota ' => $_SESSION['admin']->idAnggota,
                'created_at ' => date('Y-m-d H:i:s'),

            ]);

            echo "format tidak sesuai";
        }
    }
    public function ProsesSaldoAwal($data)
    {
        try {
            foreach ($data as $k) {
                if (isset($k['saldoAwal']) && is_numeric($k['saldoAwal'])) {

                    $idAnggota = DB::con()->cell(
                        "SELECT idAnggota FROM anggota WHERE nomorPekerja = ?",
                        $k['nopek']
                    );
                    if (!empty($idAnggota)) {

                        DB::con()->update('anggota', [
                            'saldoAwal' => $k['saldoAwal'],
                        ], [
                            'idAnggota' => $idAnggota,

                        ]);
                    }
                }
            }
            DB::con()->insert('log', [
                'tipe' => 'Normal',
                'entitas ' => 'Saldo Awal Anggota',
                'aksi' => "Import Data Saldo Awal Anggota Excel",
                'tambahan' => '',
                'idAnggota ' => $_SESSION['admin']->idAnggota,
                'created_at ' => date('Y-m-d H:i:s'),

            ]);
        } catch (\Throwable $th) {
            DB::con()->insert('log', [
                'tipe' => 'Error',
                'entitas ' => 'SHU',
                'aksi' => 'Import Data Saldo Awal Anggota Excel',
                'tambahan' => $th->getMessage(),
                'idAnggota ' => $_SESSION['admin']->idAnggota,
                'created_at ' => date('Y-m-d H:i:s'),

            ]);

            echo "format tidak sesuai";
        }
    }
    public function ProsesModalPenyertaan($data)
    {
        try {
            foreach ($data as $k) {
                if (isset($k['modalPenyertaan']) && is_numeric($k['modalPenyertaan'])) {

                    $idAnggota = DB::con()->cell(
                        "SELECT idAnggota FROM anggota WHERE nomorPekerja = ?",
                        $k['nopek']
                    );
                    if (!empty($idAnggota)) {

                        DB::con()->update('anggota', [
                            'modalPenyertaan' => $k['modalPenyertaan'],
                        ], [
                            'idAnggota' => $idAnggota,

                        ]);
                    }
                }
            }
            DB::con()->insert('log', [
                'tipe' => 'Normal',
                'entitas ' => 'Modal Penyertaan Anggota',
                'aksi' => "Import Data Modal Penyertaan Anggota Excel",
                'tambahan' => '',
                'idAnggota ' => $_SESSION['admin']->idAnggota,
                'created_at ' => date('Y-m-d H:i:s'),

            ]);
        } catch (\Throwable $th) {
            DB::con()->insert('log', [
                'tipe' => 'Error',
                'entitas ' => 'Modal Penyertaan Anggota',
                'aksi' => 'Import Data Modal Penyertaan Anggota Excel',
                'tambahan' => $th->getMessage(),
                'idAnggota ' => $_SESSION['admin']->idAnggota,
                'created_at ' => date('Y-m-d H:i:s'),

            ]);

            echo "format tidak sesuai";
        }
    }
}
