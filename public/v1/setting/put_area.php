<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("area_id",$body)){
    return $cert->return->set_error("invalid_param","require area_id");
}

//TODO: 色不要

$is_change_data = [
    "area_name"=>false,
    "capacity"=>false,
    "hide"=>false,
];

if(!is_nullorwhitespace_in_array("area_name",$body)) $is_change_data["area_name"] = true;
if(!is_nullorwhitespace_in_array("capacity",$body)) $is_change_data["capacity"] = true;
if(!is_nullorwhitespace_in_array("hide",$body) || $body["hide"]===false) $is_change_data["hide"] = true;

if($is_change_data["capacity"] && !is_numeric($body["capacity"])){
    return $cert->return->set_error("invalid_param_type","parameter capacity is need to type number");
}else if(intval($body["capacity"])<0){
    return $cert->return->set_error("invalid_param_type","parameter capacity is up to 0");
}

$db = new DB();
try{
    $sql = "SELECT area_name,capacity,hide FROM setting_area WHERE area_id = :area_id";
    $sth_data = $db->pdo->prepare($sql);
    $sth_data->bindValue(":area_id",$body["area_id"]);
    $sth_data->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$area_data = $sth_data->fetch();
if($area_data){
    if($is_change_data["area_name"]) $area_data["area_name"] = $body["area_name"];
    if($is_change_data["capacity"]) $area_data["capacity"] = intval($body["capacity"]);
    if($is_change_data["hide"]) $area_data["hide"] = $body["hide"];
    
    try{
        $sql = "UPDATE setting_area SET area_name = :area_name, capacity = :capacity, hide = :hide WHERE area_id = :area_id";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":area_id",$body["area_id"]);
        $sth->bindValue(":area_name",$area_data["area_name"]);
        $sth->bindValue(":capacity",intval($area_data["capacity"]));
        $sth->bindValue(":hide",$area_data["hide"]?1:0);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    return $cert->return->set_data([
        "area_id"=>$body["area_id"],
        "area_name"=>$area_data["area_name"],
        "capacity"=>$area_data["capacity"],
        "hide"=>$area_data["hide"],
    ]);

}else{
    if(is_nullorwhitespace_in_array("area_id",$body)
        || is_nullorwhitespace_in_array("area_name",$body)
        || is_nullorwhitespace_in_array("capacity",$body)
        || (is_nullorwhitespace_in_array("hide",$body) && $body["hide"]!==false)
    ){
        return $cert->return->set_error("invalid_param","require area_id, area_name, capacity and hide");
    }

    if(!is_between_strlen($body["area_id"],1,8)){
        return $cert->return->set_error("invalid_param_length","parameter area_id is 1 to 8");
    }
    if(!is_between_strlen($body["area_name"],1,64)){
        return $cert->return->set_error("invalid_param_length","parameter area_name is 1 to 64");
    }

    try{
        $sql = "INSERT INTO setting_area (area_id, area_name, capacity, hide) VALUES (:area_id, :area_name, :capacity, :hide)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":area_id",$body["area_id"]);
        $sth->bindValue(":area_name",$body["area_name"]);
        $sth->bindValue(":capacity",intval($body["capacity"]));
        $sth->bindValue(":hide",$body["hide"]?1:0);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    return $cert->return->set_data([
        "area_id"=>$body["area_id"],
        "area_name"=>$body["area_name"],
        "capacity"=>$body["capacity"],
        "hide"=>$body["hide"],
    ]);
}

