<?php 
require_once __DIR__ . "/root.php";
require_once __DIR__ . "/def.php";
session_start();
if(!isset($_SESSION["emp_id"]) || !isset($_SESSION["time_limit"])|| $_SESSION["time_limit"] < time()){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}


?>