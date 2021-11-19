<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$db = new DB();
try{
    $db->pdo->beginTransaction();
    $sql = "SELECT * FROM setting ORDER BY id ASC";
    $sth_setting = $db->pdo->prepare($sql);
    $sth_setting->execute();
    $sql = "SELECT * FROM setting_area ORDER BY area_id ASC";
    $sth_area = $db->pdo->prepare($sql);
    $sth_area->execute();
    $sql = "SELECT * FROM setting_gate ORDER BY gate_id ASC";
    $sth_gate = $db->pdo->prepare($sql);
    $sth_gate->execute();
    $sql = "SELECT * FROM login_auth_list ORDER BY auth_name ASC";
    $sth_auth_list = $db->pdo->prepare($sql);
    $sth_auth_list->execute();
    $sql = "SELECT * FROM attribute_list ORDER BY attribute_id ASC";
    $sth_att_list = $db->pdo->prepare($sql);
    $sth_att_list->execute();
    $sql = "SELECT * FROM login_auth_group ORDER BY auth_name ASC";
    $sth_auth_group = $db->pdo->prepare($sql);
    $sth_auth_group->execute();
    $sql = "SELECT * FROM attribute_prefix ORDER BY attribute_id ASC, prefix ASC";
    $sth_att_prefix = $db->pdo->prepare($sql);
    $sth_att_prefix->execute();
    $db->pdo->commit();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$setting_list = [];
foreach($sth_setting->fetchAll() as $setting){
    $setting_list[$setting["id"]] = $setting;
}

$area_list = [];
foreach($sth_area->fetchAll() as $area){
    $area_list[$area["area_id"]] = $area;
}

$gate_list = [];
foreach($sth_gate->fetchAll() as $gate){
    $gate_list[$gate["gate_id"]] = $gate;
}

$auth_group_list = [];
foreach($sth_auth_group->fetchAll() as $auth){
    $auth_group_list[$auth["auth_group"]][] = $auth["auth_name"];
}

$attribute_list = [];
$att_list = $sth_att_list->fetchAll();
foreach($att_list as $att){
    $attribute_list[$att["attribute_id"]] = $att;
}
foreach($sth_att_prefix as $val){
    $attribute_list[$val["attribute_id"]]["prefix"][] = $val["prefix"];
}



return $cert->return->set_data([
    "area"=>$area_list,
    "gate"=>$gate_list,
    "setting"=>$setting_list,
    "auth_list"=>$sth_auth_list->fetchAll(),
    "auth_group"=>$auth_group_list,
    "attribute"=>$attribute_list,
]);