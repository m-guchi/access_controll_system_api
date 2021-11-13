<?php

use Auth\Certification;

$cert = new Certification();
$return = new ApiReturn();

if(!$cert->is_continue()){
    $this->code = $cert->code();
    if($this->code>= 400){
        $this->code = 200;
        $return->set_token($cert->return()["token"]);
        return $return->set_data([
            "login" => false,
        ]);
    }
}
$this->code = 200;
$return->set_token($cert->return()["token"]);
return $return->set_data([
    "login" => true,
]);