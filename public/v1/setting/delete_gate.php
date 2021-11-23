<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;

if(is_nullorwhitespace_in_array("gate_id",$body)){
    return $cert->return->set_error("invalid_param","require gate_id");
}

$db = new DB();
try{
    $sql = "DELETE FROM setting_gate WHERE gate_id = :gate_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":gate_id",$body["gate_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];