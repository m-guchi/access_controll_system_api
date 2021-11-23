<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("id",$body)){
    return $cert->return->set_error("invalid_param","require id");
}

$db = new DB();


try{
    $sql = "UPDATE setting SET value = :value WHERE id = :id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":id",$body["id"]);
    $sth->bindValue(":value",$body["value"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

return $cert->return->set_data([
    "id"=>$body["id"],
    "value"=>$body["value"],
]);

