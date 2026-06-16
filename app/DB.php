<?php

/**
 *
 */

namespace app;

use \Exception;
use \PDO;

class DB
{
    private static $_instance = null;
    public $db;
    public function __construct()
    {
        $dbname = $GLOBALS['db'];
        $user = $GLOBALS['user'];
        $pass = $GLOBALS['pass'];

        $this->db = \ParagonIE\EasyDB\Factory::fromArray([
            "mysql:host=localhost;dbname=$dbname",
            $user,
            $pass,
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]
        ]);
    }
    static public function con()
    {
        $dbname = $GLOBALS['db'];
        $user = $GLOBALS['user'];
        $pass = $GLOBALS['pass'];

        $db = \ParagonIE\EasyDB\Factory::fromArray([
            "mysql:host=localhost;dbname=$dbname",
            $user,
            $pass,
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]
        ]);
        return $db;
    }
}
