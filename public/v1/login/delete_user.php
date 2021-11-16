<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $this->request_body;

if(is_nullorwhitespace_in_array("login_user_id",$body)){
    $this->code = 400;
    return $return->set_error("invalid_param","require login_user_id");
}

if($cert->login_user_id===$body["login_user_id"]){
    $this->code = 400;
    return $return->set_error("cannot_delete_user","cannot delete current login user");
}

$db = new DB();
try{
    $sql = "DELETE FROM login_users WHERE login_user_id = :login_user_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":login_user_id",$body["login_user_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

$this->code = 204;
return [];