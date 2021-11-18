<?php

class ApiReturn
{
    private ?string $token = null;
    private $err;
    private $data;
    private bool $is_error = false;

    public function set_token($val)
    {
        if(is_null($val)) return false;
        $this->token = $val;
    }

    public function set_db_error($e)
    {
        $bt = debug_backtrace();
        $file = mb_substr($bt[0]['file'],-20);
        $line = $bt[0]['line'];
        $this->is_error = true;
        $this->err = [
            "type"=>"db_error",
            "msg"=>[
                "l"=>$file." ".$line,
                "db_msg"=>$e
            ],
        ];
        return $this->get();
    }

    public function set_error($type,$msg)
    {
        $this->is_error = true;
        $this->err = [
            "type"=>$type,
            "msg"=>$msg,
        ];
        return $this->get();
    }

    public function set_data($data)
    {
        $this->is_error = false;
        $this->data = $data;
        return $this->get();
    }

    public function get()
    {
        if($this->is_error){
            return [
                "ok"=>false,
                "token"=>$this->token,
                "error"=>$this->err,
            ];
        }else{
            return [
                "ok"=>true,
                "token"=>$this->token,
                "data"=>$this->data,
            ];
        }
    }
}