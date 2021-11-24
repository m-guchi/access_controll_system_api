<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;

if(is_nullorwhitespace_in_array("attribute_id",$body)){
    return $cert->return->set_error("invalid_param","require attribute_id");
}

$db = new DB();
try{
    $db->pdo->beginTransaction();
    $sql = "DELETE FROM attribute_list WHERE attribute_id = :attribute_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->execute();
    $sql = "DELETE FROM attribute_prefix WHERE attribute_id = :attribute_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->execute();
    $db->pdo->commit();
}catch(PDOException $e){
    $db->pdo->rollBack();
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];