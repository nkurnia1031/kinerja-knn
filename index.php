<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);

// error_reporting(0);
// MODE_DEBUG allows to pinpoint troubles.
require_once "vendor/autoload.php";
require_once 'env.php';
require_once 'Link.php';
require_once 'vendor/phpqrcode/qrlib.php';

spl_autoload_register(function ($className) {
    $ds = DIRECTORY_SEPARATOR;
    $dir = __DIR__;

    // replace namespace separator with directory separator (prolly not required)
    $className = str_replace('\\', $ds, $className);

    // get full name of file containing the required class
    $file = "{$dir}{$ds}{$className}.php";

    // get file if it is readable
    if (is_readable($file)) {
        require_once $file;
    }
});

use eftec\bladeone\BladeOne;

$views = __DIR__ . '/views';
$cache = __DIR__ . '/cache';
$blade = new BladeOne($views, $cache, BladeOne::MODE_DEBUG);
$blade->addInclude('Komponen.input', 'input');
$Session = $_SESSION;
$Request = json_decode(json_encode($_REQUEST));
$num = strripos($_SERVER['PHP_SELF'], 'index.php');
//dd($_SERVER['REQUEST_URI']);
$hal = substr($_SERVER['REQUEST_URI'], $num);
$hal = explode("?", $hal);
$hal = $hal[0];
if ($hal == '') {
    echo "<script>location.href = 'Dashboard';</script>";
    die();
}
if (empty($route[$hal])) {
    header('Content-Type: image/png');
    readfile('upload/no-img-placeholder.png');
    die();
};

$ctr = $route[$hal]['class'];
$hal2 = $route[$hal]['@'];
$akses = $route[$hal]['akses'];
if (!is_null($akses)) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $auth_header = $headers['Authorization'];
        $jwt = str_replace("Bearer ", "", $auth_header);
        app\Master::cekToken($jwt);
        $Session = $_SESSION;
    }

    if (!isset($Session['admin'])) {
        echo "<script>location.href = 'Login';</script>";
        die();
    }

    if (!empty($akses)) {
        $userRole = $Session['admin']->role ?? null;
        if (empty($userRole) || !in_array($userRole, $akses, true)) {
            http_response_code(403);
            echo "Akses ditolak";
            die();
        }
    }
}
$Controller = new $ctr;
$komponen = 'views/Komponen';
echo $Controller->$hal2($Request, $Session, $blade);

//include 'views/html.php';

/* Start to develop here. Best regards https://php-download.com/ */
