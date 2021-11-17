<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $_GET;

if(is_nullorwhitespace_in_array("area_id",$body)
    || is_nullorwhitespace_in_array("start_date",$body)
    || is_nullorwhitespace_in_array("end_date",$body)
){
    $this->code = 400;
    return $return->set_error("invalid_param","require area_id, start_date and end_date");
}


$db = new DB();
try{
    $sql = "SELECT user_id FROM users_pass WHERE in_area=:area_id AND time >= :start_date AND time <= :end_date ORDER BY time DESC";
    $sth_intime = $db->pdo->prepare($sql);
    $sth_intime->bindValue(":area_id",$body["area_id"]);
    $sth_intime->bindValue(":start_date",$body["start_date"]);
    $sth_intime->bindValue(":end_date",$body["end_date"]);
    $sth_intime->execute();
    $sql = "SELECT u1.user_id FROM users_pass AS u1 INNER JOIN( SELECT user_id, MAX(time) AS last_time FROM users_pass WHERE time <= :start_date GROUP BY user_id) AS u2 ON ( u1.user_id = u2.user_id AND u1.time = u2.last_time ) WHERE in_area=:area_id ORDER BY u2.last_time DESC";
    $sth_beforetime = $db->pdo->prepare($sql);
    $sth_beforetime->bindValue(":area_id",$body["area_id"]);
    $sth_beforetime->bindValue(":start_date",$body["start_date"]);
    $sth_beforetime->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

$user_list = array_merge(array_column($sth_intime->fetchAll(),"user_id"),array_column($sth_beforetime->fetchAll(),"user_id"));
$user_list = array_unique($user_list);
sort($user_list);

return $return->set_data([
    "area_id"=>$body["area_id"],
    "users"=>array_values($user_list),
]);