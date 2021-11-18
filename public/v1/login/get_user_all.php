<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();

if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$db = new DB();
try{
    $db->pdo->beginTransaction();
    $sql = "SELECT login_user_id,login_id,login_user_name,auth_group FROM login_users ORDER BY login_user_id ASC";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->execute();
    $sql = "SELECT login_user_id,gate_id FROM login_range_gate ORDER BY gate_id ASC";
    $sth_gate = $db->pdo->prepare($sql);
    $sth_gate->execute();
    $db->pdo->commit();
}catch(PDOException $e){
    $db->pdo->rollBack();
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$login_user_data_list = [];
foreach($sth_user->fetchall() as $data){
    $data["gate_id_list"] = [];
    $login_user_data_list[$data["login_user_id"]] = $data;
}

foreach($sth_gate->fetchAll() as $gate){
    $login_user_data_list[$gate["login_user_id"]]["gate_id_list"][] = $gate["gate_id"];
}

return $cert->return->set_data($login_user_data_list);