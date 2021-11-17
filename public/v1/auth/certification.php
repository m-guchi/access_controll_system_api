<?php

namespace Auth;

include_once(__DIR__ .  "/jwt.php");
include_once(__DIR__ .  "/header.php");

use ApiReturn;
use DateTimeImmutable;
use DB\DB;
use PDO;
use PDOException;

class Certification
{
    private jwt $jwt;
    private header $header;
    private int $code = 200;
    private array $return = [];
    private $api_return;
    private bool $is_continue = false;
    public ?string $login_user_id = null;

    public ?string $new_token = null;

    function __construct()
    {
        $this->jwt = new JWT();
        $this->header = new Header();
        $this->api_return = new ApiReturn();

        if(!$this->is_exist_jwt_in_cookie()) return false; //cookieにtokenが存在するか
        if(!$this->header->is_valid()){
            //header tokenがない場合
            if(!$this->is_token_within_time()) return false; //JWTが有効期限内か
            if(!$this->is_fetch_token_from_db_where_token_id($this->jwt->get("token_id"))) return false; //DBに接続できるか(↓用)
            if(!$this->is_token_data_exist()) return false; //JWTのtokenがDBに存在するか
            $this->code = 401;
            $this->api_return->set_token($this->login_data["token"]);
            $this->return = $this->api_return->set_error("need_this_token","request again with this token");
            return false;
        }else{
            //header tokenがある場合
            if(!$this->is_match_jwt_and_header()) return false; //JWTとheaderのtokenが一致するか
            if(!$this->fetch_data_and_is_exist_header_in_db($this->header->token)) return false; //headerのtokenがDBに存在するか
            if($this->is_token_near_timeout($this->exp_date,'+1 hour')){ //tokenの有効期限が近いか
                //tokenを再生成
                $jwt = new JWT();
                $jwt_res = $jwt->create_token($this->login_user_id);
                if(!$jwt->insert_token($jwt_res, $this->login_user_id)){
                    $this->code = 500;
                    $this->return = $jwt->error_msg;
                    return false;
                }

                $this->api_return->set_token($jwt_res["token"]);
            }
            $this->is_continue = true; 
            return true;
        }
    }

    public function is_continue():bool
    {
        return $this->is_continue;
    }

    public function code():int
    {
        return $this->code;
    }

    public function return():array
    {
        return $this->return;
    }

    public function authority($key):bool
    {
        if(!$this->is_continue()) return false;
        if(!$this->is_authority_in_db($key)) return false;
        if($this->is_permit_auth===0){
            $this->code = 403;
            $this->return = $this->api_return->set_error("no_permission","does not have necessary permissions");
            return false;
        }
        return true;
    }

    private function is_exist_jwt_in_cookie():bool
    {
        if(!$this->jwt->is_valid()){
            $this->code = 401;
            $this->return = $this->api_return->set_error("not_in_jwt","require jwt in cookies");
            return false;
        }
        return true;
    }

    // ■以下、header tokenがない場合の処理

    private function is_token_within_time():bool
    {
        $now = new DateTimeImmutable();
        if($now > $this->jwt->get("exp")){
            $this->code = 401;
            $this->return = $this->api_return->set_error("timeout_jwt","this JWT is expired");
            return false;
        }
        return true;
    }

    private ?array $login_data;
    private function is_fetch_token_from_db_where_token_id($token_id):bool
    {
        $db = new DB();
        try{
            $sql = "SELECT token, login_user_id FROM login_tokens WHERE token_id = :token_id AND valid_date > NOW()";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":token_id",$token_id);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = $this->api_return->set_db_error($e);
            return false;
        }
        $data = $sth->fetch();
        $this->login_data = ($data!==false) ? $data : null ;
        return true;
    }

    private function is_token_data_exist():bool
    {
        if(is_null($this->login_data)){
            $this->code = 401;
            $this->return = $this->api_return->set_error("not_in_token_id","this token id is not exist or expired in db");
            return false;
        }else{
            $this->login_user_id = $this->login_data["login_user_id"];
        }
        return true;
    }


    // ■以下、header tokenがある場合の処理

    private function is_match_jwt_and_header():bool
    {
        if(!password_verify($this->header->token, $this->jwt->get("token"))){
            $this->code = 401;
            $this->return = $this->api_return->set_error("invalid_token","header token is not match jwt token");
            return false;
        }
        return true;
    }

    private $exp_date;
    private function fetch_data_and_is_exist_header_in_db($token):bool
    {
        $db = new DB();
        try{
            $sql = "SELECT valid_date,login_user_id FROM login_tokens WHERE token = :token";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":token",$token);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = $this->api_return->set_db_error($e);
            return false;
        }
        $data = $sth->fetch();
        if($data===false){
            $this->code = 401;
            $this->return = $this->api_return->set_error("not_in_token","this token is not exist in db");
            return false;
        }
        $now = new DateTimeImmutable();
        $valid_date = new DateTimeImmutable($data["valid_date"]);
        if($now > $valid_date){
            $this->code = 401;
            $this->return = $this->api_return->set_error("timeout_token","this token is expired");
            return false;
        }
        $this->exp_date = $data["valid_date"];
        $this->login_user_id = $data["login_user_id"];
        return true;
    }

    private function is_token_near_timeout($date,$refresh_time):bool
    {
        $now = new DateTimeImmutable();
        $limit_date = $now->modify($refresh_time);
        $exp_date = new DateTimeImmutable($date);
        return ($limit_date > $exp_date);
    }

    public int $is_permit_auth;
    private function is_authority_in_db($key):bool
    {
        $db = new DB();
        try{
            $sql = "SELECT * FROM login_users LEFT JOIN login_auth_group USING(auth_group) WHERE login_user_id = :login_user_id AND auth_name =:auth_name";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":login_user_id",$this->login_user_id);
            $sth->bindValue(":auth_name",$key);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = fatal_error($e);
            return false;
        }
        $this->is_permit_auth = count($sth->fetchAll());
        return true;
    }

}