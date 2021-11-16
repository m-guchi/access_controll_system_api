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
        if($arg1==="user") return include(__DIR__."/../login/patch_user.php");
        $this->code=404;
        return [];
    }

    public function delete($arg1=null,$arg2=null):array
    {
        if($arg1==="user"){
            if(is_nullorwhitespace($arg2)) return include(__DIR__."/../login/delete_user.php");
            if($arg2==="gate") return include(__DIR__."/../login/delete_user_gate.php");
            if($arg2==="auth") return include(__DIR__."/../login/delete_user_auth.php");
            if($arg2==="token") return include(__DIR__."/../login/delete_user_token.php");
        }
        $this->code=404;
        return [];
    }

    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PATCH,DELETE");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }
}