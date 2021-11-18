<?php

namespace Auth;


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

}