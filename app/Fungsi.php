<?php

/**
 *
 */

namespace app;

use ClanCats\Hydrahon\Query\Expression as Ex;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use \DateInterval;
use \DateTime;

class Fungsi
{

    // public static $roles = ['Umum', 'Bagian', 'Kepala'];
    public static $jk = ['Laki-laki', 'Perempuan'];

    public static $type = [
        'varchar' => 'text',
        'text' => 'text',
        'json' => 'text',
        'longtext' => 'text',
        'char' => 'text',

        'int' => 'number',
        'smallint' => 'number',

        'bigint' => 'number',
        'float' => 'number',
        'double' => 'number',
        'decimal' => 'number',
        'tinyint' => 'number',
        'date' => 'date',
        'time' => 'time',
        'year' => 'number',
        'enum' => 'text',
        'timestamp' => 'timestamp',
        'datetime' => 'datetime-local',

    ];

    public static $hari = [
        1 => 'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        "Jum'at",
        'Sabtu',
        'Minggu',

    ];
    public static $huruf = [
        1 => 'A',
        'B',
        'C',
        'D',
        "E",
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',

    ];

    public static $bulan = array(
        '1' => 'Januari',
        '2' => 'Februari',
        '3' => 'Maret',
        '4' => 'April',
        '5' => 'Mei',
        '6' => 'Juni',
        '7' => 'Juli',
        '8' => 'Agustus',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    );
    public static $devices = [
        "Laptop",
        "PC",
        "PC AIO",
        "Printer",
        "Scanner",
        "Server",

    ];
    public static $status = [
        'belum_dicek' => [
            'label' => 'Belum Dicek',
            'class' => 'badge badge-secondary',
        ],
        'proses_cek' => [
            'label' => 'Proses Cek',
            'class' => 'badge badge-info',
        ],
        'menunggu_konfirmasi' => [
            'label' => 'Menunggu Konfirmasi',
            'class' => 'badge badge-warning ',
        ],
        'proses_pengerjaan' => [
            'label' => 'Proses Pengerjaan',
            'class' => 'badge badge-primary',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'class' => 'badge badge-success',
        ],
        'sudah_diambil' => [
            'label' => 'Sudah Diambil',
            'class' => 'badge badge-dark',
        ],
    ];
    public static $jenis = [
        'Pembelian' => [
            'label' => 'Pembelian',
            'class' => 'badge badge-danger',
        ],
        'Penjualan' => [
            'label' => 'Penjualan',
            'class' => 'badge badge-success',
        ],
        'Servis' => [
            'label' => 'Servis',
            'class' => 'badge badge-warning ',
        ]

    ];

    public static $agama = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
    public static $statusTinggal = ['Milik sendiri', 'Rumah Dinas', 'Milik Orang Tua', 'Kontrak', 'Lainnya'];
    public static $pendidikan = ['SMA/MA/SMK/MAK', 'D3/Diploma', 'Sarjana', 'Magister', 'Doktor'];
    public static $statusPerkawinan = ['Belum Menikah', 'Menikah', 'Duda/Janda'];
    public static $hubunganAhliWaris = ['Istri / Suami', 'Anak', 'Orang Tua', 'Lainnya'];
    public static function turnstileVerify($response)
    {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = array(
            'secret' => $_SERVER['CF_SECRET'],
            'response' => $response,
            'ipaddress' => $_SERVER['REMOTE_ADDR'],
        );

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        try {
            $response = curl_exec($ch);

            if ($response === false) {
                throw new \Exception('Curl error: ' . curl_error($ch));
            }

            return $response;
        } catch (\Exception $e) {
            // Handle error
            return 'Error: ' . $e->getMessage();
        } finally {
            // Close curl connection
            curl_close($ch);
        }
    }

    public static function hapusKosong($input, $array)
    {
        $x = collect($input)->filter(function ($value, $key) {
            return $value != "";
        });
        if ($array) {
            $x = $x->toArray();
        }

        return $x;
    }
    public static function cekUnik($table, $a, $input, $link)
    {
        if (array_key_exists($a['key'], $input)) {

            $cek = collect(Crud::table($table)->select()->where($a['key'], $input[$a['key']])->get());
            if ($cek->isNotEmpty()) {
                $GLOBALS['msg']->error("<strong>Gagal</strong>: $a[label] sudah terdaftar, silahkan menggunakan $a[label] yang lain");
                echo "<script>location.href='$link'</script>";
                die();
            }
        }
    }

    public static function hariini()
    {
        return date('d') . " " . Fungsi::$bulan[date('n')] . " " . date('Y');
    }
    public static function id($tb, $primary)
    {
        $jum = ['idBarang' => ['B-', 4], 'idNamaBarang' => ['N-', 3], 'idPenjualan' => ['P-', 10], 'idKategori' => ['K-', 2], 'idUser' => ['U-', 3]];
        $id = Crud::table($tb)->select(new Ex("max($primary) as id"))->get()[0]->id;
        $id = self::HapusHuruf($id) + 1;
        $id = $jum[$primary][0] . str_pad($id, $jum[$primary][1], '0', STR_PAD_LEFT);
        return $id;
    }

    public static function operator($retrieved, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }
        switch ($operator) {
            default:
            case '=':
            case '==':
                return $retrieved == $value;
            case '!=':
            case '<>':
                return $retrieved != $value;
            case '<':
                return $retrieved < $value;
            case '>':
                return $retrieved > $value;
            case '<=':
                return $retrieved <= $value;
            case '>=':
                return $retrieved >= $value;
            case '===':
                return $retrieved === $value;
            case '!==':
                return $retrieved !== $value;
        }
    }
    public static function HapusHuruf($string)
    {
        return preg_replace('/[^0-9]/', '', $string);
    }
    public static function truemod($num, $mod)
    {
        return ($mod + ($num % $mod)) % $mod;
    }
    public static function upload($file, $key, $ekstensi2 = array('png', 'jpg', 'jpeg', 'pdf'), $size = 1044070, $path = 'upload/')
    {
        $nama = $file['name'][$key];
        $x = explode('.', $nama);
        $ekstensi = strtolower(end($x));
        $nama = $x[0] . uniqid() . "." . $ekstensi;
        $result = (object) [
            'nama' => $nama,
            'status' => false,
            'error' => "Sukses",
        ];
        $ukuran = $file['size'][$key];
        $file_tmp = $file['tmp_name'][$key];
        //  if (in_array($ekstensi, $ekstensi2) === true) {
        if (in_array($ekstensi, $ekstensi2, false)) {

            if ($ukuran < $size * 5) {
                move_uploaded_file($file_tmp, $path . $nama);
                $result->status = true;
            } else {
                $result->error = 'UKURAN FILE TERLALU BESAR';
            }
        } else {
            $result->error = 'EKSTENSI FILE YANG DI UPLOAD TIDAK DI PERBOLEHKAN';
        }
        return $result;
    }
    public static function fields($tb, $Crud)
    {

        return $Crud->idupin()->getFields($tb)->toArray();
    }
    public static function auto($tb, $Crud)
    {

        return $Crud->idupin()->auto($tb);
    }
    public static function randomArray($n, $target = 100)
    {
        $awalT = $target;
        $awalN = $n;
        while ($n) {
            if (1 < $n--) {

                $addend = rand(1, $target - ($n));
                $target -= $addend;
                $addends[] = $addend;
            } else {
                $addends[] = $target;
            }

            // if (in_array(0, $addends)) {
            //     $addends[] = [];
            //     $n = $awalN;
            //     $target = $awalT;

            // }
        }
        $addends = array_map(function ($item) use ($awalT) {
            return $item / $awalT;
        }, $addends);
        return $addends;
    }
    public static function removeCurrencyFormat($value)
    {
        // Menghapus karakter selain digit, titik, atau koma
        $formattedValue = preg_replace('/[^0-9.,]/', '', $value);

        // Menghapus koma jika ada
        $formattedValue = str_replace(',', '', $formattedValue);

        // Menghapus dua digit desimal di akhir jika ada
        if (strpos($formattedValue, '.') !== false) {
            $formattedValue = ltrim($formattedValue, '.');
            $formattedValue = rtrim($formattedValue, '0');
            $formattedValue = rtrim($formattedValue, '.');
        }

        return $formattedValue;
    }

    public static function addMonths($months, DateTime $dateObject)
    {
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +' . $months . ' month');

        if ($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P' . $months . 'M');
        }
    }

    public static function endCycle($d1, $months)
    {
        $date = new DateTime($d1);

        // call second function to add the months
        $newDate = $date->add(Fungsi::addMonths($months, $date));

        // goes back 1 day from date, remove if you want same day of month
        // $newDate->sub(new DateInterval('P1D'));

        //formats final date to Y-m-d form
        $dateReturned = $newDate->format('Y-m-d');

        return $dateReturned;
    }
    public static function kirim($array)
    {
        if (empty($array)) {

            $array = [
                'subject' => "Uji Coba",
                'body' => $GLOBALS['blade']->run('Layout.email', [
                    'data' => '$data',
                    'Request' => '$Request',
                    'Session' => ' $Session',
                ]),
                'to' => ['nkurnia1031@gmail.com', 'Nama Penerima'],

            ];
        }

        $mail = new PHPMailer(true); // create a new object
        //Set mailer to use smtp

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output
            $mail->isSMTP(); //Send using SMTP
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );
            $mail->Host = 'mail.koperasi-ppt.com'; //Set the SMTP server to send through
            $mail->SMTPAuth = true; //Enable SMTP authentication
            $mail->Username = $_SERVER['mail']; //SMTP username
            $mail->Password = $_SERVER['mail_pass']; //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable implicit TLS encryption
            $mail->Port = 587; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($_SERVER['mail'], "Koperasi Jasa Pekerja Putri Tujuh");
            $mail->addAddress($array['to'][0], $array['to'][1]); //Add a recipient

            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = $array['subject'];
            $mail->Body = $array['body'];
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            dd($e);
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            // die();
        }
    }
}
