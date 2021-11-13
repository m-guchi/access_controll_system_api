<?php

namespace Auth;

include_once(__DIR__ .  "/jwt.php");

use DateTimeImmutable;
use DB\DB;
use PDO;
use PDOException;

class Header
{
    public ?string $token = null;
    private bool $valid_token = false;

    public function __construct()
    {
        if(!is_null($this->get_token())){
            $this->valid_token = true;
        }
        $this->token = $this->get_token();
    }

    //tokenがheaderにあるか
    public function is_valid():bool
    {
        return $this->valid_token;
    }

    //tokenがない場合はnullを返す
    public function get_token():?string
    {
        $headers = getallheaders();
        if(is_nullorwhitespace_in_array("token",$headers)) return null;
        return $headers["token"];
    }

    //tokenがない場合にJWTから再取得
    public int $code = 200;
    public array $return = [];
    private jwt $jwt;
    public function get_token_from_jwt():bool
    {
        $this->jwt =  new JWT();
        if(!$this->is_token_within_time()){
            $this->code = 401;
            $this->return = ["ok"=>false,"re"=>false,"error" => [
                "type" => "timeout_token",
                "msg" => "this token is expired"
            ]];
        }else{
            if(!$this->is_fetch_token_from_db()) return false;
            if(is_null($this->token_from_db)){
                $this->code = 401;
                $this->return = ["ok"=>false,"re"=>false,"error" => [
                    "type" => "not_in_token",
                    "msg" => "this token is not exist"
                ]];
            }
            $this->return = ["ok"=>false,"re"=>true,"info" => [
                "type" => "need_this_token",
                "msg" => "request again with this token",
                "token" => $this->token_from_db,
            ]];
        }
        return false;
    }

    private function is_token_within_time():bool
    {
        $now = new DateTimeImmutable();
        return ($now <= $this->jwt->get("exp"));
    }

    private ?string $token_from_db;
    private function is_fetch_token_from_db():bool
    {
        $db = new DB();
        try{
            $sql = "SELECT token FROM login_tokens WHERE token_id = :token_id AND valid_date > NOW()";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":token_id",$this->jwt->get("token_id"));
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = fatal_error($e);
            return false;
        }
        $this->token_from_db = ($sth->fetch(PDO::FETCH_ASSOC)!==false) ? $sth->fetch(PDO::FETCH_ASSOC)["token"] : null;
        return true;
    }


}