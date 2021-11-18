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
    $sql = "SELECT login_user_id,login_id,login_user_name,auth_group FROM login_users WHERE login_user_id=:login_user_id";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->bindValue(":login_user_id",$cert->login_user_id);
    $sth_user->execute();
    $sql = "SELECT gate_id FROM login_range_gate WHERE login_user_id = :login_user_id ORDER BY gate_id ASC";
    $sth_gate = $db->pdo->prepare($sql);
    $sth_gate->bindValue(":login_user_id",$cert->login_user_id);
    $sth_gate->execute();
    $db->pdo->commit();
}catch(PDOException $e){
    $db->pdo->rollBack();
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$login_user_data = $sth_user->fetch();
$login_user_data["gate_id_list"] = array_reduce($sth_gate->fetchAll(), function($carry, $item){
    $carry[] = $item["gate_id"];
    return $carry;
}, []);

return $cert->return->set_data($login_user_data);