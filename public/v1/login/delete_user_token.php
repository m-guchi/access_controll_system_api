<?php

use DateTimeImmutable;
use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $this->request_body;

$delete_range_day = (!is_nullorwhitespace_in_array("day",$body) && intval($body["day"])!==0) ? intval($body["day"]) : 3;

$now = new DateTimeImmutable();
$db = new DB();
try{
    $sql = "DELETE FROM login_tokens WHERE login_user_id = :login_user_id AND valid_date < :valid_date";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":login_user_id",$body["login_user_id"]);
    $sth->bindValue(":valid_date",$now->modify("-".$delete_range_day." day")->format('Y-m-d H:i:s'));
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

$this->code = 204;
return [];