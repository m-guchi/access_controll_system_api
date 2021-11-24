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
    $sql = "SELECT login_user_id,login_id,login_user_name,auth_group FROM login_users ORDER BY login_user_id ASC";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$login_user_data_list = [];
foreach($sth_user->fetchall() as $data){
    $data["gate_id_list"] = [];
    $login_user_data_list[$data["login_user_id"]] = $data;
}

return $cert->return->set_data($login_user_data_list);