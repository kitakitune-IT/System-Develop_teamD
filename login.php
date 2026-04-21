<?php
require_once __DIR__ . "/root.php";
// require_once __DIR__ . "/def.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    try{
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $job = [
        1 => "システム管理部",
        2 => "other",
    ];


    $stmt = null;
    $db = null;
    }catch(PDOException $poe){
    exit("DBエラー".$poe->getMessage());
    }

}

?>