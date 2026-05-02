<?php
define( "DB_HOST", "localhost" );
define( "DB_USER", "suser" );
define( "DB_PASS", "safe" );
define( "DB_NAME", "safety_system" );
define( "DB_CHARSET", "utf8mb4" );

define("WEB_ROOT", "http://localhost/SystemDevelop/");

function h($str){
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

function csrf_token_generate(){
    if(!isset($_SESSION["csrf_token"])){
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function def_session_check(){
    if(!isset($_SESSION["connect_user"]) || !isset($_SESSION["connection_time_limit"])|| $_SESSION["connection_time_limit"] < time()){
    session_destroy();
    header("Location: " . WEB_ROOT . "index.php");
    exit;
    }else{
        $_SESSION["connection_time_limit"] = time() + 3600;
        return true;
    }
}

function admin_check(){
    if(!isset($_SESSION["connect_user"]["administrator"]))return false;
    
    if($_SESSION["connect_user"]["administrator"] == 1){
        return true;
    }else{
        return false;
    }
}