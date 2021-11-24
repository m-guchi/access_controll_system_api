<?php

use Auth\Certification;
use DB\DB;

$cert = new Certification();
if(!$cert->is_continue() || !$cert->authority("setting_mgmt")){
    $this->code = $cert->code();
    return $cert->return();
}

$body = $this->request_body;
if(is_nullorwhitespace_in_array("attribute_id",$body)){
    return $cert->return->set_error("invalid_param","require attribute_id");
}

$is_change_data = [
    "attribute_name"=>false,
    "color"=>false,
];

if(!is_nullorwhitespace_in_array("attribute_name",$body)) $is_change_data["attribute_name"] = true;
if(!is_nullorwhitespace_in_array("color",$body)) $is_change_data["color"] = true;

if($is_change_data["color"] && !preg_match("/^#[0-9A-Fa-f]{6}$/",$body["color"])){
    return $cert->return->set_error("invalid_param_type","parameter color is need to type '#xxxxxx'");
}

$db = new DB();
try{
    $sql = "SELECT * FROM attribute_list WHERE attribute_id = :attribute_id";
    $sth_data = $db->pdo->prepare($sql);
    $sth_data->bindValue(":attribute_id",$body["attribute_id"]);
    $sth_data->execute();
}catch(PDOException $e){
    $this->code = 500;
    return $cert->return->set_db_error($e);
}
$attribute_data = $sth_data->fetch();

if($attribute_data){
    if($is_change_data["attribute_name"]) $attribute_data["attribute_name"] = $body["attribute_name"];
    if($is_change_data["color"]) $attribute_data["color"] = $body["color"];

    try{
        $sql = "UPDATE attribute_list SET attribute_name = :attribute_name, color = :color WHERE attribute_id = :attribute_id";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":attribute_id",$body["attribute_id"]);
        $sth->bindValue(":attribute_name",$attribute_data["attribute_name"]);
        $sth->bindValue(":color",$attribute_data["color"]);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    return $cert->return->set_data([
        "attribute_id"=>$body["attribute_id"],
        "attribute_name"=>$attribute_data["attribute_name"],
        "color"=>$attribute_data["color"],
    ]);

}else{
    if(is_nullorwhitespace_in_array("attribute_name",$body)
        || is_nullorwhitespace_in_array("color",$body)
    ){
        return $cert->return->set_error("invalid_param","require attribute_name and color");
    }

    try{
        $sql = "INSERT INTO attribute_list (attribute_id, attribute_name, color) VALUES (:attribute_id, :attribute_name, :color)";
        $sth = $db->pdo->prepare($sql);
        $sth->bindValue(":attribute_id",$body["attribute_id"]);
        $sth->bindValue(":attribute_name",$body["attribute_name"]);
        $sth->bindValue(":color",$body["color"]);
        $sth->execute();
    }catch(PDOException $e){
        $this->code = 500;
        return $cert->return->set_db_error($e);
    }

    return $cert->return->set_data([
        "attribute_id"=>$body["attribute_id"],
        "attribute_name"=>$body["attribute_name"],
        "color"=>$body["color"],
    ]);
}


