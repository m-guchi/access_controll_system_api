<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $_GET;

$int_next = (!is_nullorwhitespace_in_array("next",$body) && intval($body["next"])>0) ? intval($body["next"]) : 0;
$int_num = (!is_nullorwhitespace_in_array("num",$body) && intval($body["num"])>0) ? intval($body["num"]) : 100;

$db = new DB();
try{
    $sql = "SELECT user_id,area_id,time,attribute_id FROM users ORDER BY time DESC LIMIT :next , :num";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":next",$int_next, PDO::PARAM_INT);
    $sth->bindValue(":num",$int_num, PDO::PARAM_INT);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$user_data_list = [];
foreach($sth->fetchAll() as $user){
    $user_data_list[$user["user_id"]] = $user;
}

$data_count = count($user_data_list);
return $cert->return->set_data([
    "num"=>$data_count,
    "next"=>$int_next+$data_count,
    "users"=>$user_data_list,
]);