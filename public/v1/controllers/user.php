<?php

include_once(__DIR__."/../auth/certification.php");

class UserController
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
        if(is_nullorwhitespace($arg1)) return include(__DIR__."/../user/get.php");
        if($arg1==="all") return include(__DIR__."/../user/get_all.php");
        if($arg1==="count") return include(__DIR__."/../user/get_count.php");
        if($arg1==="pass"){
            if(is_nullorwhitespace($arg2)) return include(__DIR__."/../user/get_pass.php");
            if($arg2==="all") return include(__DIR__."/../user/get_pass_all.php");
            if($arg2==="search") return include(__DIR__."/../user/get_pass_search.php");
        }
        $this->code=404;
        return [];
    }

    public function post($arg1=null,$arg2=null):array
    {
        if($arg1==="reset") return include(__DIR__."/../user/post_reset.php");
        if($arg1==="attribute"){
            if($arg2==="prefix") return include(__DIR__."/../user/post_attribute_prefix.php");
        }
        $this->code=404;
        return [];
    }

    public function put($arg1=null):array
    {
        if(is_nullorwhitespace($arg1)) return include(__DIR__."/../user/put.php");
        if($arg1==="attribute") return include(__DIR__."/../user/put_attribute.php");
        $this->code=404;
        return [];
    }

    public function delete($arg1=null,$arg2=null):array
    {
        if(is_nullorwhitespace($arg1)) return include(__DIR__."/../user/delete.php");
        if($arg1==="old") return include(__DIR__."/../user/delete_old.php");
        if($arg1==="attribute"){
            if(is_nullorwhitespace($arg2)) return include(__DIR__."/../user/delete_attribute.php");
            if($arg2==="prefix") return include(__DIR__."/../user/delete_attribute_prefix.php");
        }
        if($arg1==="pass"){
            if(is_nullorwhitespace($arg2)) return include(__DIR__."/../user/delete_pass.php");
            if($arg2==="old") return include(__DIR__."/../user/delete_pass_old.php");
        }
        $this->code=404;
        return [];
    }

    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }
}