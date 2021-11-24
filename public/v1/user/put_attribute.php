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
    || is_nullorwhitespace_in_array("attribute_name",$body)
){
    return $cert->return->set_error("invalid_param","require attribute_id and attribute_name");
}

$db = new DB();
try{
    $sql = "UPDATE attribute_list SET attribute_name = :attribute_name WHERE attribute_id = :attribute_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->bindValue(":attribute_name",$body["attribute_name"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

if($sth->rowCount()===0)
try{
    $sql = "INSERT INTO attribute_list (attribute_id, attribute_name) VALUES (:attribute_id, :attribute_name)";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->bindValue(":attribute_name",$body["attribute_name"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

return $cert->return->set_data([
    "attribute_id"=>$body["attribute_id"],
    "attribute_name"=>$body["attribute_name"],
]);

