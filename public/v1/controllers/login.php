<?php

include_once(__DIR__ . "/../auth/jwt.php");
include_once(__DIR__."/../auth/certification.php");

use Auth;
use DB\DB;
use Auth\Certification;

class LoginController
{
    public int $code = 200;
    public string $url;
    public ?array $request_body;

    function __construct($url,$request_body)
    {
        $this->url = $url;
        $this->request_body = $request_body;
    }

    public function get($arg1=null,$arg2=null):array
    {
        if(is_nullorwhitespace($arg1)) return include(__DIR__."/../login/get.php");
        if($arg1==="user"){
            if(is_nullorwhitespace($arg2)) return include(__DIR__."/../login/get_user.php");
            if($arg2==="all") return include(__DIR__."/../login/get_user_all.php");
        }
        $this->code=404;
        return [];
    }

    public function post($arg1=null,$arg2=null):array
    {
        if(is_nullorwhitespace($arg1)) return include(__DIR__."/../login/post.php");
        if($arg1==="user"){
            if(is_nullorwhitespace($arg2)) return include(__DIR__."/../login/post_user.php");
            if($arg2==="gate") return include(__DIR__."/../login/post_user_gate.php");
        }
        $this->code=404;
        return [];
    }

    public function patch($arg1=null):array
    {
        if($arg1==="user"){
            return include(__DIR__."/../login/patch_user.php");
        }
        $this->code=404;
        return [];
    }

    // public function get():array
    // {
    //     $cert = new Certification();
    //     if(!$cert->is_continue()){
    //         if($cert->code() >= 400){
    //             return [
    //                 "login" => false,
    //                 "error" => $cert->return()["error"],
    //             ];
    //         }
    //         return [
    //             "login" => false,
    //             "info" => $cert->return()["info"],
    //         ];
    //     }
    //     return [
    //         "login" => true
    //     ];
    // }

    // public function post():array
    // {
    //     $post = $this->request_body;
    //     if(!array_key_exists("login_id",$post) || !array_key_exists("password",$post)){
    //         $this->code = 400;
    //         return ["error" => [
    //             "type" => "invalid_param",
    //             "msg" => "require login_id and password",
    //         ]];
    //     }

    //     $db = new DB();

    //     try{
    //         $sql = "SELECT user_id,login_id,password FROM login_users WHERE login_id = :login_id";
    //         $sth = $db->pdo()->prepare($sql);
    //         $sth->bindValue(":login_id",$post["login_id"]);
    //         $sth->execute();
    //     }catch(PDOException $e){
    //         $this->code = 500;
    //         return fatal_error($e);
    //     }

    //     //ユーザーが存在するか
    //     $user_data = $sth->fetch(PDO::FETCH_ASSOC);
    //     if($user_data===false){
    //         $this->code = 401;
    //         return ["error" => [
    //             "type" => "not_in_user",
    //             "msg" => "not exist this login id",
    //         ]];
    //     }

    //     //パスワードが一致するか
    //     $is_match = password_verify($post["password"],$user_data["password"]);
    //     if(!$is_match){
    //         $this->code = 401;
    //         return ["error" => [
    //             "type" => "invalid_password",
    //             "msg" => "input password is wrong",
    //         ]];
    //     }

    //     //CRSFトークンの発行
    //     $jwt = new Auth\JWT();
    //     $user_id = $user_data["user_id"];
    //     $jwt_res = $jwt->create_token($user_id, "+1 hour");
    //     if(!$jwt->insert_token($jwt_res, $user_id, "+1 hour")){
    //         $this->code = 500;
    //         return $jwt->error_msg;
    //     }

    //     unset($user_data['user_id']);
    //     unset($user_data['password']);
    //     $user_data["token"] = $jwt_res["token"];
    //     return $user_data;
    // }

    // public function options():array
    // {
    //     header("Access-Control-Allow-Methods: OPTIONS,GET,POST");
    //     header("Access-Control-Allow-Headers: Content-Type");
    //     return [];
    // }
}