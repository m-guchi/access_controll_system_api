<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("gate_id",$body)){
    return $cert->return->set_error("invalid_param","require gate_id");
}

//TODO: 色不要

$is_change_data = [
    "gate_name"=>false,
    "in_area"=>false,
    "out_area"=>false,
    "can_make_ticket"=>false,
];

if(!is_nullorwhitespace_in_array("gate_name",$body)) $is_change_data["gate_name"] = true;
if(!is_nullorwhitespace_in_array("in_area",$body)) $is_change_data["in_area"] = true;
if(!is_nullorwhitespace_in_array("out_area",$body)) $is_change_data["out_area"] = true;
if(!is_nullorwhitespace_in_array("can_make_ticket",$body) || $body["can_make_ticket"]===false) $is_change_data["can_make_ticket"] = true;

$db = new DB();

try{
    $sql = "SELECT gate_name,in_area,out_area,can_make_ticket FROM setting_gate WHERE gate_id = :gate_id";
    $sth_data = $db->pdo->prepare($sql);
    $sth_data->bindValue(":gate_id",$body["gate_id"]);
    $sth_data->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$gate_data = $sth_data->fetch();
if($gate_data){
    if($is_change_data["gate_name"]) $gate_data["gate_name"] = $body["gate_name"];
    if($is_change_data["in_area"]) $gate_data["in_area"] = $body["in_area"];
    if($is_change_data["out_area"]) $gate_data["out_area"] = $body["out_area"];
    if($is_change_data["can_make_ticket"]) $gate_data["can_make_ticket"] = $body["can_make_ticket"];

    try{
        $sql = "SELECT COUNT(area_id) as count FROM setting_area WHERE area_id = :in_area_id";
        $sth_in_area = $db->pdo->prepare($sql);
        $sth_in_area->bindValue(":in_area_id",$gate_data["in_area"]);
        $sth_in_area->execute();
        $sql = "SELECT COUNT(area_id) as count FROM setting_area WHERE area_id = :out_area_id";
        $sth_out_area = $db->pdo->prepare($sql);
        $sth_out_area->bindValue(":out_area_id",$gate_data["out_area"]);
        $sth_out_area->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    if($sth_in_area->fetch()["count"]==0){
        return $cert->return->set_error("not_in_area_id","this in_area is not exist");
    }
    if($sth_out_area->fetch()["count"]==0){
        return $cert->return->set_error("not_out_area_id","this out_area is not exist");
    }

    try{
        $sql = "UPDATE setting_gate SET gate_name = :gate_name, in_area = :in_area, out_area = :out_area, can_make_ticket = :can_make_ticket WHERE gate_id = :gate_id";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":gate_id",$body["gate_id"]);
        $sth->bindValue(":gate_name",$gate_data["gate_name"]);
        $sth->bindValue(":in_area",$gate_data["in_area"]);
        $sth->bindValue(":out_area",$gate_data["out_area"]);
        $sth->bindValue(":can_make_ticket",$gate_data["can_make_ticket"]?1:0);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    return $cert->return->set_data([
        "gate_id"=>$body["gate_id"],
        "gate_name"=>$gate_data["gate_name"],
        "in_area"=>$gate_data["in_area"],
        "out_area"=>$gate_data["out_area"],
        "can_make_ticket"=>$gate_data["can_make_ticket"],
    ]);

}else{
    if(is_nullorwhitespace_in_array("gate_id",$body)
        || is_nullorwhitespace_in_array("gate_name",$body)
        || is_nullorwhitespace_in_array("in_area",$body)
        || is_nullorwhitespace_in_array("out_area",$body)
        || (is_nullorwhitespace_in_array("can_make_ticket",$body) && $body["can_make_ticket"]!==false)
    ){
        return $cert->return->set_error("invalid_param","require gate_id, gate_name, in_area, out_area and can_make_ticket");
    }

    if(!is_between_strlen($body["gate_id"],1,8)){
        return $cert->return->set_error("invalid_param_length","parameter gate_id is 1 to 8");
    }
    if(!is_between_strlen($body["gate_name"],1,64)){
        return $cert->return->set_error("invalid_param_length","parameter area_name is 1 to 64");
    }
    if(!is_between_strlen($body["in_area"],1,8)){
        return $cert->return->set_error("invalid_param_length","parameter in_area is 1 to 8");
    }
    if(!is_between_strlen($body["out_area"],1,8)){
        return $cert->return->set_error("invalid_param_length","parameter out_area is 1 to 8");
    }

    try{
        $sql = "INSERT INTO setting_gate (gate_id, gate_name, in_area, out_area, can_make_ticket) VALUES (:gate_id, :gate_name, :in_area, :out_area, :can_make_ticket)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":gate_id",$body["gate_id"]);
        $sth->bindValue(":gate_name",$body["gate_name"]);
        $sth->bindValue(":in_area",$body["in_area"]);
        $sth->bindValue(":out_area",$body["out_area"]);
        $sth->bindValue(":can_make_ticket",$body["can_make_ticket"]?1:0);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    return $cert->return->set_data([
        "gate_id"=>$body["gate_id"],
        "gate_name"=>$body["gate_name"],
        "in_area"=>$body["in_area"],
        "out_area"=>$body["out_area"],
        "can_make_ticket"=>$body["can_make_ticket"],
    ]);
}

