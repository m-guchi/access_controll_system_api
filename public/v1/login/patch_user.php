<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;

if(is_nullorwhitespace_in_array("login_user_id",$body)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require login_user_id");
}

$is_change_data = [
    "login_id"=>false,
    "login_user_name"=>false,
    "password"=>false,
    "auth_group"=>false,
];

if(!is_nullorwhitespace_in_array("login_id",$body)) $is_change_data["login_id"] = true;
if(!is_nullorwhitespace_in_array("login_user_name",$body)) $is_change_data["login_user_name"] = true;
if(!is_nullorwhitespace_in_array("password",$body)) $is_change_data["password"] = true;
if(!is_nullorwhitespace_in_array("auth_group",$body)) $is_change_data["auth_group"] = true;

if($is_change_data["login_id"] && !is_between_strlen($body["login_id"],1,32)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter login_id is 1 to 32");
}
if($is_change_data["login_user_name"] && !is_between_strlen($body["login_user_name"],1,64)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter login_user_name is 1 to 64");
}
if($is_change_data["password"] && !is_between_strlen($body["password"],1,64)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter password is 1 to 64");
}
if($is_change_data["auth_group"] && !is_between_strlen($body["auth_group"],1,16)){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param_length","parameter auth_group is 1 to 16");
}

if($is_change_data["auth_group"] && $body["login_user_id"]===$cert->login_user_id){
    // $this->code = 400;
    return $cert->return->set_error("cannot_change_myself","cannot change authority myself");
}


$db = new DB();
try{
    $sql = "SELECT login_id,login_user_name,password,auth_group FROM login_users WHERE login_user_id = :login_user_id";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->bindValue(":login_user_id",$body["login_user_id"]);
    $sth_user->execute();
    if($is_change_data["login_id"]){
        $sql = "SELECT COUNT(login_id) as count FROM login_users WHERE login_id = :login_id and login_user_id != :login_user_id";
        $sth_user_count = $db->pdo->prepare($sql);
        $sth_user_count->bindValue(":login_id",$body["login_id"]);
        $sth_user_count->bindValue(":login_user_id",$body["login_user_id"]);
        $sth_user_count->execute();
    }
    if($is_change_data["auth_group"]){
        $sql = "SELECT COUNT(auth_group) as count FROM login_auth_group WHERE auth_group = :auth_group";
        $sth_auth = $db->pdo->prepare($sql);
        $sth_auth->bindValue(":auth_group",$body["auth_group"]);
        $sth_auth->execute();
    }
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}
$user_data = $sth_user->fetch();
if($user_data===false){
    // $this->code = 400;
    return $cert->return->set_error("not_in_login_user_id","this login_user_id is not exist");
}
if($is_change_data["login_id"] && $sth_user_count->fetch()["count"]>0){
    // $this->code = 400;
    return $cert->return->set_error("already_login_id","this login_id is already used");
}
if($is_change_data["auth_group"] && $sth_auth->fetch()["count"]==0){
    // $this->code = 400;
    return $cert->return->set_error("not_in_authority_group","this authority_group is not exist");
}

if($is_change_data["login_id"]) $user_data["login_id"] = $body["login_id"];
if($is_change_data["login_user_name"]) $user_data["login_user_name"] = $body["login_user_name"];
if($is_change_data["password"]) $user_data["password"] = password_hash($body["password"],PASSWORD_DEFAULT);
if($is_change_data["auth_group"]) $user_data["auth_group"] = $body["auth_group"];

try{
    $password_hash = password_hash($body["password"],PASSWORD_DEFAULT);

    $sql = "UPDATE login_users SET login_id = :login_id, login_user_name = :login_user_name, password = :password, auth_group = :auth_group WHERE login_user_id = :login_user_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":login_user_id",$body["login_user_id"]);
    $sth->bindValue(":login_id",$user_data["login_id"]);
    $sth->bindValue(":login_user_name",$user_data["login_user_name"]);
    $sth->bindValue(":password",$user_data["password"]);
    $sth->bindValue(":auth_group",$user_data["auth_group"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

return $cert->return->set_data([
    "login_user_id"=>$body["login_user_id"],
    "login_id"=>$user_data["login_id"],
    "login_user_name"=>$user_data["login_user_name"],
    "auth_group"=>$user_data["auth_group"]
]);