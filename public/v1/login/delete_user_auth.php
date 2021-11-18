<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("auth_group",$body)
    || is_nullorwhitespace_in_array("auth_name",$body)
){
    // $this->code = 400;
    return $cert->return->set_error("invalid_param","require auth_group and auth_name");
}

$db = new DB();
try{
    $sql = "SELECT COUNT(auth_name) as count FROM login_auth_group WHERE auth_group = :auth_group";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":auth_group",$body["auth_group"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

if($sth->fetch()["count"]==1){
    try{
        $sql = "SELECT COUNT(login_user_id) as count FROM login_users WHERE auth_group = :auth_group";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":auth_group",$body["auth_group"]);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    if($sth->fetch()["count"]>0){
        // $this->code = 400;
        return $cert->return->set_error("cannot_delete_last_auth_group","cannot delete current auth group because exist login user having this group");
    }
}


try{
    $sql = "DELETE FROM login_auth_group WHERE auth_group = :auth_group AND auth_name = :auth_name";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":auth_group",$body["auth_group"]);
    $sth->bindValue(":auth_name",$body["auth_name"]);
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];