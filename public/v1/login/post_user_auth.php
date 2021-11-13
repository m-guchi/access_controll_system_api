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
if(is_nullorwhitespace_in_array("auth_group",$body)
    || is_nullorwhitespace_in_array("auth_name",$body)
){
    $this->code = 400;
    return $return->set_error("invalid_param","require auth_group and auth_name");
}


$db = new DB();
try{
    $sql = "SELECT COUNT(auth_name) as count FROM login_auth_list WHERE auth_name = :auth_name";
    $sth_auth_name = $db->pdo->prepare($sql);
    $sth_auth_name->bindValue(":auth_name",$body["auth_name"]);
    $sth_auth_name->execute();
    $sql = "SELECT COUNT(*) as count FROM login_auth_group WHERE auth_group = :auth_group AND auth_name = :auth_name";
    $sth_auth = $db->pdo->prepare($sql);
    $sth_auth->bindValue(":auth_group",$body["auth_group"]);
    $sth_auth->bindValue(":auth_name",$body["auth_name"]);
    $sth_auth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

if($sth_auth_name->fetch()["count"]==0){
    $this->code = 400;
    return $return->set_error("not_in_auth_name","this auth_name is not exist");
}

if($sth_auth->fetch()["count"]==0){
    try{
        $sql = "INSERT INTO login_auth_group (auth_group, auth_name) VALUES (:auth_group, :auth_name)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":auth_group",$body["auth_group"]);
        $sth->bindValue(":auth_name",$body["auth_name"]);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $return->set_db_error($e);
    }
}

return $return->set_data([
    "auth_group"=>$body["auth_group"],
    "auth_name"=>$body["auth_name"],
]);