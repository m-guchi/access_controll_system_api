<?php

namespace DB;

include(__DIR__ . "/env/db_mysql.php");

use PDO;
use PDOException;

class DB
{
    public $pdo;

    public function __construct()
    {
        $this->pdo = $this->pdo();
    }

    public function pdo()
    {
        global $env_mysql;
        try{
            $dns = "mysql:dbname=".$env_mysql['dbname'].";host=".$env_mysql['host'].";charset=utf8mb4";
            $pdo = new PDO($dns,$env_mysql["user"],$env_mysql["password"]);
            // $pdo = new PDO('sqlite:db/e_mgmt.db');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }catch(PDOException $error){
            header("Content-Type: application/json; charset=utf-8", true, 500);
            echo json_encode(["error" => ["type" => "db_error","message"=>$error->getMessage()]]);
            die();
        }
        return $pdo;
    }
}