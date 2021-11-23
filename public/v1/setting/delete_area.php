<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;

if(is_nullorwhitespace_in_array("area_id",$body)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require area_id");
}

$db = new DB();
try{
    $sql = "DELETE FROM setting_area WHERE area_id = :area_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":area_id",$body["area_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];