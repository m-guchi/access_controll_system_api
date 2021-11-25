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
    $sql = "SELECT login_user_id,login_id,login_user_name,auth_group FROM login_users WHERE login_user_id=:login_user_id";
    $sth_user = $db->pdo->prepare($sql);
    $sth_user->bindValue(":login_user_id",$cert->login_user_id);
    $sth_user->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$login_user_data = $sth_user->fetch();

return $cert->return->set_data($login_user_data);