<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $_GET;

if(is_nullorwhitespace_in_array("user_id",$body)){
    $this->code = 400;
    return $return->set_error("invalid_param","require user_id");
}

$int_next = (!is_nullorwhitespace_in_array("next",$body) && intval($body["next"])>0) ? intval($body["next"]) : 0;
$int_num = (!is_nullorwhitespace_in_array("num",$body) && intval($body["num"])>0) ? intval($body["num"]) : 100;

$db = new DB();
try{
    $sql = "SELECT time,in_area,out_area FROM users_pass WHERE user_id=:user_id ORDER BY time DESC LIMIT :next , :num";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":user_id",$body["user_id"]);
    $sth->bindValue(":next",$int_next, PDO::PARAM_INT);
    $sth->bindValue(":num",$int_num, PDO::PARAM_INT);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

$data_count = $sth->rowCount();
return $return->set_data([
    "user_id"=>$body["user_id"],
    "num"=>$data_count,
    "next"=>$int_next+$data_count,
    "pass"=>$sth->fetchAll()
]);