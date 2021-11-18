<?php

use Auth\Certification;

$cert = new Certification();

if(!$cert->is_continue()){
    $this->code = $cert->code();
    if($this->code>= 400){
        $this->code = 200;
        return $cert->return->set_data([
            "login" => false,
        ]);
    }
}
$this->code = 200;
$cert->return->set_token($cert->return()["token"]);
return $cert->return->set_data([
    "login" => true,
]);