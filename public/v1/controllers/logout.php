<?php

include_once(__DIR__."/../auth/cookie.php");

use Auth\Cookie;

class LogoutController
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
        $cookie = new Cookie();
        $cookie->set(false);

        $this->code = 204;
        return [];
    }

    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }
}