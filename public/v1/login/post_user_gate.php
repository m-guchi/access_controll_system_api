<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("login_user_id",$body)
    || is_nullorwhitespace_in_array("gate_id",$body)
){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require login_user_id and gate_id");
}


$db = new DB();
try{
    $sql = "SELECT COUNT(login_user_id) as count FROM login_users WHERE login_user_id = :login_user_id";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->bindValue(":login_user_id",$body["login_user_id"]);
    $sth_user->execute();
    $sql = "SELECT COUNT(gate_id) as count FROM setting_gate WHERE gate_id = :gate_id";
    $sth_gate = $db->pdo->prepare($sql);
    $sth_gate->bindValue(":gate_id",$body["gate_id"]);
    $sth_gate->execute();
    $sql = "SELECT COUNT(*) as count FROM login_range_gate WHERE login_user_id = :login_user_id AND gate_id = :gate_id";
    $sth_range_gate = $db->pdo->prepare($sql);
    $sth_range_gate->bindValue(":login_user_id",$body["login_user_id"]);
    $sth_range_gate->bindValue(":gate_id",$body["gate_id"]);
    $sth_range_gate->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

if($sth_user->fetch()["count"]==0){
    // $this->code = 400;
    return $cert->return->set_error("not_in_login_user_id","this login_user_id is not exist");
}
if($sth_gate->fetch()["count"]==0){
    // $this->code = 400;
    return $cert->return->set_error("not_in_gate_id","this gate_id is not exist");
}

if($sth_range_gate->fetch()["count"]==0){
    try{
        $sql = "INSERT INTO login_range_gate (login_user_id, gate_id) VALUES (:login_user_id, :gate_id)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":login_user_id",$body["login_user_id"]);
        $sth->bindValue(":gate_id",$body["gate_id"]);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }
}

return $cert->return->set_data([
    "login_user_id"=>$body["login_user_id"],
    "gate_id"=>$body["gate_id"],
]);