<?php

namespace Auth;

class Cookie
{
    public string $cookie_name = "emgmt_1118";
    private string $path = "/";
    private bool $secure = false;
    private bool $httponly = true;

    private function cookie_option():array
    {
        return [
            "path" => $this->path,
            "secure" => $this->secure,
            "httponly" => $this->httponly,
        ];
    }

    public function set($val):bool
    {
        setcookie($this->cookie_name, $val, $this->cookie_option());
        return true;
    }

    public function get()
    {
        if(!array_key_exists($this->cookie_name,$_COOKIE)) return null;
        return $_COOKIE[$this->cookie_name];
    }
}