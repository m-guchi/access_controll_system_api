<?php

include_once(__DIR__."/../auth/certification.php");

class TicketController
{
    public int $code = 200;
    public string $url;
    public ?array $request_body;

    function __construct($url,$request_body)
    {
        $this->url = $url;
        $this->request_body = $request_body;
    }

    public function get($arg1=null):array
    {
        if(is_nullorwhitespace($arg1)) return include(__DIR__."/../ticket/get.php");
        if($arg1==="all") return include(__DIR__."/../ticket/get_all.php");
        $this->code=404;
        return [];
    }

    public function put():array
    {
        return include(__DIR__."/../ticket/put.php");
    }

    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,PUT");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }
}