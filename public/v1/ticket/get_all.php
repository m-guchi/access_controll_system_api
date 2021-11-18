<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("log_watcher")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $_GET;

$int_next = (!is_nullorwhitespace_in_array("next",$body) && intval($body["next"])>0) ? intval($body["next"]) : 0;
$int_num = (!is_nullorwhitespace_in_array("num",$body) && intval($body["num"])>0) ? intval($body["num"]) : 1000;

$db = new DB();
try{
    $sql = "SELECT time,ticket_id,user_id FROM tickets ORDER BY time DESC LIMIT :next , :num";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":next",$int_next, PDO::PARAM_INT);
    $sth->bindValue(":num",$int_num, PDO::PARAM_INT);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$ticket_data = $sth->fetchAll();
$data_count = count($ticket_data);
return $cert->return->set_data([
    "num"=>$data_count,
    "next"=>($data_count===$int_num)?$int_next+$data_count:false,
    "tickets"=>$ticket_data,
]);