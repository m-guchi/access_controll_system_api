<?php

use Ramsey\Uuid\Uuid;
use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("login_id",$body)
    || is_nullorwhitespace_in_array("login_user_name",$body)
    || is_nullorwhitespace_in_array("password",$body)
){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require login_id, password and login_user_name");
}

if(!is_between_strlen($body["login_id"],1,32)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter login_id is 1 to 32");
}
if(!is_between_strlen($body["login_user_name"],1,64)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter login_user_name is 1 to 64");
}
if(!is_between_strlen($body["password"],1,64)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter password is 1 to 64");
}
if(!is_between_strlen($body["auth_group"],1,16)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter auth_group is 1 to 16");
}


$db = new DB();
try{
    $sql = "SELECT COUNT(login_id) as count FROM login_users WHERE login_id = :login_id";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->bindValue(":login_id",$body["login_id"]);
    $sth_user->execute();
    $sql = "SELECT COUNT(auth_group) as count FROM login_auth_group WHERE auth_group = :auth_group";
    $sth_auth = $db->pdo->prepare($sql);
    $sth_auth->bindValue(":auth_group",$body["auth_group"]);
    $sth_auth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}
if($sth_user->fetch()["count"]>0){
    // $this->code = 400;
    return $cert->return->set_error("already_login_id","this login_id is already used");
}
if($sth_auth->fetch()["count"]==0){
    // $this->code = 400;
    return $cert->return->set_error("not_in_authority_group","this authority_group is not exist");
}

try{
    //ユーザ情報を登録
    $login_user_id = Uuid::uuid4();
    $password_hash = password_hash($body["password"],PASSWORD_DEFAULT);

    $sql = "INSERT INTO login_users (login_user_id, login_id, login_user_name, password, auth_group) VALUES (:login_user_id, :login_id, :login_user_name, :password, :auth_group)";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":login_user_id",$login_user_id);
    $sth->bindValue(":login_id",$body["login_id"]);
    $sth->bindValue(":login_user_name",$body["login_user_name"]);
    $sth->bindValue(":password",$password_hash);
    $sth->bindValue(":auth_group",$body["auth_group"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

return $cert->return->set_data([
    "login_user_id"=>$login_user_id,
    "login_id"=>$body["login_id"],
    "login_user_name"=>$body["login_user_name"],
    "auth_group"=>$body["auth_group"]
]);