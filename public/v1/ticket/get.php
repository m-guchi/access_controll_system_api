<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $_GET;

if(is_nullorwhitespace_in_array("ticket_id",$body)){
    return $cert->return->set_error("invalid_param","require ticket_id");
}

$db = new DB();
try{
    $sql = "SELECT ticket_id,user_id,time FROM tickets WHERE ticket_id=:ticket_id";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":ticket_id",$body["ticket_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$ticket_data = $sth->fetch();

if($ticket_data===false){
    return $cert->return->set_error("not_in_ticket_id","this ticket_id is not exist");
}

return $cert->return->set_data($ticket_data);