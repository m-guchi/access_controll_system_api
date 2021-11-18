<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("ticket_id",$body)){
    return $cert->return->set_error("invalid_param","require ticket_id");
}

$now = new DateTimeImmutable();
$db = new DB();

$user_id = is_nullorwhitespace_in_array("user_id",$body) ? null : $body["user_id"];

try{
    $sql = "UPDATE tickets SET user_id = :user_id, time = :time WHERE ticket_id = :ticket_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":user_id",$user_id);
    $sth->bindValue(":ticket_id",$body["ticket_id"]);
    $sth->bindValue(":time",$now->format('Y-m-d H:i:s'));
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

if($sth->rowCount()===0){
    try{
        $sql = "INSERT INTO users (user_id, ticket_id, time) VALUES (:user_id, :ticket_id, :time)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":user_id",$user_id);
        $sth->bindValue(":ticket_id",$body["ticket_id"]);
        $sth->bindValue(":time",$now->format('Y-m-d H:i:s'));
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }
}

return $cert->return->set_data([
    "time"=>$now->format('Y-m-d H:i:s'),
    "ticket_id"=>$body["ticket_id"],
    "user_id"=>$user_id,
]);