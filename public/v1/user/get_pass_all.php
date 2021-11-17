<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("log_watcher")){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $_GET;

$int_next = (!is_nullorwhitespace_in_array("next",$body) && intval($body["next"])>0) ? intval($body["next"]) : 0;
$int_num = (!is_nullorwhitespace_in_array("num",$body) && intval($body["num"])>0) ? intval($body["num"]) : 1000;

$db = new DB();
try{
    $sql = "SELECT user_id,time,in_area,out_area FROM users_pass ORDER BY time DESC, user_id ASC LIMIT :next , :num";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":next",$int_next, PDO::PARAM_INT);
    $sth->bindValue(":num",$int_num, PDO::PARAM_INT);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

$data_count = $sth->rowCount();
return $return->set_data([
    "num"=>$data_count,
    "next"=>$int_next+$data_count,
    "pass"=>$sth->fetchAll()
]);