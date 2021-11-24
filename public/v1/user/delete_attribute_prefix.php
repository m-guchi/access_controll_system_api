<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;

if(is_nullorwhitespace_in_array("attribute_id",$body)
    || is_nullorwhitespace_in_array("prefix",$body)
){
    return $cert->return->set_error("invalid_param","require attribute_id AND prefix");
}

$db = new DB();
try{
    $sql = "DELETE FROM attribute_prefix WHERE attribute_id = :attribute_id AND prefix = :prefix";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->bindValue(":prefix",$body["prefix"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];