<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("login_user_id",$body)
    || is_nullorwhitespace_in_array("gate_id",$body)
){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require login_user_id and gate_id");
}

$db = new DB();
try{
    $sql = "DELETE FROM login_range_gate WHERE login_user_id = :login_user_id AND gate_id = :gate_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":login_user_id",$body["login_user_id"]);
    $sth->bindValue(":gate_id",$body["gate_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];