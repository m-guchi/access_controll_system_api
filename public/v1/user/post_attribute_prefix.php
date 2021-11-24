<?php

use Ramsey\Uuid\Uuid;
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
    return $cert->return->set_error("invalid_param","require attribute_id and prefix");
}

if(!is_between_strlen($body["attribute_id"],1,32)){
    return $cert->return->set_error("invalid_param_length","parameter attribute_id is 1 to 32");
}
if(!is_between_strlen($body["prefix"],1,32)){
    return $cert->return->set_error("invalid_param_length","parameter prefix is 1 to 32");
}

$db = new DB();
try{
    $sql = "SELECT COUNT(*) as count FROM attribute_prefix WHERE attribute_id = :attribute_id AND prefix = :prefix";
    $sth_prefix = $db->pdo->prepare($sql);
    $sth_prefix->bindValue(":attribute_id",$body["attribute_id"]);
    $sth_prefix->bindValue(":prefix",$body["prefix"]);
    $sth_prefix->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}
if($sth_prefix->fetch()["count"]>0){
    return $cert->return->set_error("already_attribute_id_and_prefix","this attribute_id and prefix is already used");
}

try{

    $sql = "INSERT INTO attribute_prefix (attribute_id, prefix) VALUES (:attribute_id, :prefix)";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->bindValue(":prefix",$body["prefix"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

return $cert->return->set_data([
    "attribute_id"=>$body["attribute_id"],
    "prefix"=>$body["prefix"],
]);