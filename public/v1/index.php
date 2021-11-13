<?php

include(__DIR__ . "/../../vendor/autoload.php");
include(__DIR__ . "/../../config/autoload.php");

// include(__DIR__."/auth/certification.php");
include(__DIR__ . "/function.php");
include(__DIR__ . "/return.php");

date_default_timezone_set('Asia/Tokyo');

$return = new ApiReturn();

function htmlescape($val){
    return htmlspecialchars($val);
}

preg_match('|'.dirname($_SERVER["SCRIPT_NAME"]).'/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$paths = explode('/',$matches[1]);
$file = array_shift($paths);
$params = array_map("htmlescape",$paths);

$file_path = __DIR__.'/controllers/'.$file.'.php';
if(file_exists($file_path)){
    include($file_path);
    $class_name = ucfirst($file)."Controller";
    $method_name = strtolower($_SERVER["REQUEST_METHOD"]);
    $url = get_url().mb_substr($_SERVER['SCRIPT_NAME'],0,-9).$file."/";
    $request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
    $object = new $class_name($url,$request_body);
    $method = $method_name==="head" ? "get": $method_name;
    if(method_exists($object, $method)){
        $response = $object->$method(...$paths);
        $response_code = $object->code ?? 200;
    }else{
        $response_code = 405;
        $response = $return->set_error("method_not_allowed","this method is not allowd");
    }
    if($response_code===404){
        $response = $return->set_error("not_found","this page is not found");
    }
}else{
    $response_code = 404;
    $response = $return->set_error("not_found","this page is not found");
}

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: content-type, token");
header("Content-Type: application/json; charset=utf-8", true, $response_code);

echo json_encode($method_name==="head" ? [] : $response);