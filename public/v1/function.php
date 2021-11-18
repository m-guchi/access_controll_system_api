<?php

function get_url(){
    return (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'];
}

function is_between_strlen($str,$min,$max){
    return !(strlen($str)<$min || $max<strlen($str));
}

function is_nullorempty($obj){
    if($obj === 0 || $obj === "0") return false;
    return empty($obj);
}

function is_nullorwhitespace($obj){
    if(is_nullorempty($obj) === true) return true;
    if(is_string($obj) && mb_ereg_match("^(\s|　)+$", $obj)) return true;
    return false;
}

function is_nullorempty_in_array($key,$array){
    if(!array_key_exists($key,$array)) return true;
    if(is_nullorempty($array[$key])) return true;
    return false;
}

function is_nullorwhitespace_in_array($key,$array){
    if(!array_key_exists($key,$array)) return true;
    if(is_nullorwhitespace($array[$key])) return true;
    return false;
}