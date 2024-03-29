<?php

use DateTimeImmutable;
use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("login_users_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;

$now = new DateTimeImmutable();
$default_delete_date = $now->modify("-3 day");
$delete_date = is_nullorwhitespace_in_array("day",$body) ? $default_delete_date : new DateTimeImmutable($body["day"]);

$db = new DB();
try{
    $sql = "DELETE FROM login_tokens WHERE valid_date < :valid_date";
    $sth = $db->pdo->prepare($sql);
    $sth->bindValue(":valid_date",$delete_date->format('Y-m-d H:i:s'));
    $sth->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}

$this->code = 204;
return [];