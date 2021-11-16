<?php

include_once(__DIR__ . "/../auth/jwt.php");
include_once(__DIR__."/../auth/certification.php");

class SettingController
{
    public int $code = 200;
    public string $url;
    public ?array $request_body;

    function __construct($url,$request_body)
    {
        $this->url = $url;
        $this->request_body = $request_body;
    }

    public function get():array
    {
        return include(__DIR__."/../setting/get.php");
    }

    public function put($arg1=null):array
    {
        if(is_nullorwhitespace($arg1))return include(__DIR__."/../setting/put.php");
        if($arg1==="area") return include(__DIR__."/../setting/put_area.php");
        if($arg1==="gate") return include(__DIR__."/../setting/put_gate.php");
        $this->code=404;
        return [];
    }

    public function delete($arg1=null):array
    {
        if($arg1==="area") return include(__DIR__."/../setting/delete_area.php");
        if($arg1==="gate") return include(__DIR__."/../setting/delete_gate.php");
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