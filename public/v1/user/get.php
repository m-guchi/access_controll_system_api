<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $_GET;

if(is_nullorwhitespace_in_array("user_id",$body)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require user_id");
}

$db = new DB();
try{
    $sql = "SELECT user_id,area_id,time,attribute_id FROM users WHERE user_id=:user_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":user_id",$body["user_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$user_data = $sth->fetch();

if($user_data===false){
    // $this->code = 400;
    return $cert->return->set_error("not_in_user_id","this user_id is not exist");
}

return $cert->return->set_data($user_data);