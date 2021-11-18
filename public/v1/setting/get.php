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
    $sth_auth = $db->pdo->prepare($sql);
    $sth_auth->execute();
    $sql = "SELECT * FROM attribute_list ORDER BY attribute_id ASC";
    $sth_att_list = $db->pdo->prepare($sql);
    $sth_att_list->execute();
    $sql = "SELECT * FROM attribute_prefix ORDER BY attribute_id ASC, prefix ASC";
    $sth_att_prefix = $db->pdo->prepare($sql);
    $sth_att_prefix->execute();
    $db->pdo->commit();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$att_list = $sth_att_list->fetchAll();
$att_list_id = array_column($att_list, "attribute_id");
foreach($sth_att_prefix as $val){
    $index = array_search($val["attribute_id"],$att_list_id);
    $att_list[$index]["prefix"][] = $val["prefix"];
}


return $cert->return->set_data([
    "area"=>$sth_area->fetchAll(),
    "gate"=>$sth_gate->fetchAll(),
    "setting"=>$sth_setting->fetchAll(),
    "auth"=>$sth_auth->fetchAll(),
    "attribute"=>$att_list,
]);