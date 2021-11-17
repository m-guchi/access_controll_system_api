<?php

use Ramsey\Uuid\Uuid;
use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $this->request_body;
if((is_nullorwhitespace_in_array("user_id",$body)
    && is_nullorwhitespace_in_array("ticket_id",$body))
    || is_nullorwhitespace_in_array("gate_id",$body)
){
    $this->code = 400;
    return $return->set_error("invalid_param","require user_id or ticket_id, and gate_id");
}

$not_exist_user_id = is_nullorwhitespace_in_array("user_id",$body);

$db = new DB();
try{
    if($not_exist_user_id){
        $sql = "SELECT user_id FROM tickets WHERE ticket_id=:ticket_id";
        $sth_user = $db->pdo->prepare($sql);
        $sth_user->bindValue(":ticket_id",$body["ticket_id"]);
        $sth_user->execute();
    }
    $sql = "SELECT in_area, out_area FROM setting_gate WHERE gate_id=:gate_id";
    $sth_gate = $db->pdo->prepare($sql);
    $sth_gate->bindValue(":gate_id",$body["gate_id"]);
    $sth_gate->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}
if($not_exist_user_id){
    $user_data = $sth_user->fetch();
    if($user_data===false){
    $this->code = 400;
    return $return->set_error("not_in_ticket_id","this ticket_id is not exist");
    }
}
$gate_data = $sth_gate->fetch();
if($gate_data===false){
    $this->code = 400;
    return $return->set_error("not_in_gate_id","this gate_id is not exist");
}

$user_id = $not_exist_user_id ? $user_data["user_id"] : $body["user_id"];
$now = new DateTimeImmutable();

$is_exist_attribute_id = !is_nullorwhitespace_in_array("attribute_id",$body);

try{
    $sql = "INSERT INTO users_pass (user_id, in_area, out_area, time) VALUES (:user_id, :in_area, :out_area, :time)";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":user_id",$user_id);
    $sth->bindValue(":in_area",$gate_data["in_area"]);
    $sth->bindValue(":out_area",$gate_data["out_area"]);
    $sth->bindValue(":time",$now->format('Y-m-d H:i:s'));
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

try{
    if($is_exist_attribute_id){
        $sql = "UPDATE users SET area_id = :area_id, time = :time, attribute_id = :attribute_id WHERE user_id = :user_id";
    }else{
        $sql = "UPDATE users SET area_id = :area_id, time = :time WHERE user_id = :user_id";
    }
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":user_id",$user_id);
    $sth->bindValue(":area_id",$gate_data["in_area"]);
    $sth->bindValue(":time",$now->format('Y-m-d H:i:s'));
    if($is_exist_attribute_id) $sth->bindValue(":attribute_id",$body["attribute_id"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

if($sth->rowCount()===0){
    try{
        $sql = "INSERT INTO users (user_id, area_id, time, attribute_id) VALUES (:user_id, :area_id, :time, :attribute_id)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":user_id",$user_id);
        $sth->bindValue(":area_id",$gate_data["in_area"]);
        $sth->bindValue(":time",$now->format('Y-m-d H:i:s'));
        $sth->bindValue(":attribute_id",$is_exist_attribute_id?$body["attribute_id"]:null);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $return->set_db_error($e);
    }
}

return $return->set_data([
    "user_id"=>$user_id,
    "gate_id"=>$body["gate_id"],
    "in_area"=>$gate_data["in_area"],
    "out_area"=>$gate_data["out_area"],
    "time"=>$now->format('Y-m-d H:i:s'),
    "attribute_id"=>$is_exist_attribute_id?$body["attribute_id"]:null
]);