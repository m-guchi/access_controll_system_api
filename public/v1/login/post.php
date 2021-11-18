<?php

use DB\DB;

$return = new ApiReturn();
$body = $this->request_body;
if(is_nullorwhitespace_in_array("login_id",$body)
    || is_nullorwhitespace_in_array("password",$body)
){
    // $this->code = 400;
    return $return->set_error("invalid_param","require login_id and password");
}

$db = new DB();
try{
    $sql = "SELECT login_user_id,login_id,password FROM login_users WHERE login_id = :login_id";
    $sth = $db->pdo()->prepare($sql);
    $sth->bindValue(":login_id",$body["login_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

//ユーザーが存在するか
$login_user_data = $sth->fetch();
if($login_user_data===false){
    $this->code = 401;
    return $return->set_error("not_in_user","not exist this login_id");
}

//パスワードが一致するか
$is_match = password_verify($body["password"],$login_user_data["password"]);
if(!$is_match){
    $this->code = 401;
    return $return->set_error("invalid_password","input password is wrong");
}

//CRSFトークンの発行
$jwt = new Auth\JWT();
$user_id = $login_user_data["login_user_id"];
$jwt_res = $jwt->create_token($user_id);
if(!$jwt->insert_token($jwt_res, $user_id)){
    $this->code = 500;
    return $jwt->error_msg;
}

$return->set_token($jwt_res["token"]);
return $return->set_data([
    "login_user_id"=>$login_user_data["login_user_id"],
    "login_id"=>$login_user_data["login_id"],
    "token"=>$jwt_res["token"],
]);