<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue()){
    $this->code = $cert->code();
    return $cert->return();
}

$return = new ApiReturn();
$body = $_GET;

$db = new DB();
try{
    $sql = "SELECT COUNT(user_id) as count, area_id FROM users GROUP BY area_id ORDER BY area_id ASC";
    $sth_all = $db->pdo->prepare($sql);
    $sth_all->execute();
    $sql = "SELECT COUNT(user_id) as count, area_id, attribute_id FROM users GROUP BY area_id, attribute_id ORDER BY area_id ASC, attribute_id ASC";
    $sth_att = $db->pdo->prepare($sql);
    $sth_att->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $return->set_db_error($e);
}

$count_data = [];

foreach($sth_all->fetchAll() as $data){
    $count_data[$data["area_id"]]["total"] = $data["count"];
}

foreach($sth_att->fetchAll() as $data){
    if(!is_null($data["attribute_id"]))
    $count_data[$data["area_id"]]["attribute"][$data["attribute_id"]] = $data["count"];
}

return $return->set_data($count_data);