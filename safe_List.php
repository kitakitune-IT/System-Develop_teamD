<?php
session_start();
if(!isset($_SESSION["emp_id"]) || !isset($_SESSION["time_limit"])|| $_SESSION["time_limit"] < time()){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}

    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    try{
    $db = new PDO($dsn, DB_USER , DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT,false);



    
    }catch(PDOException $poe){
    exit("DBエラー".$poe->getMessage());
    }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../css/社員安否一覧画面.css">
<title>社員安否一覧画面</title>
</head>
<body>

<h1>社員安否一覧画面</h1>

<div class="container">

<div class="search-box">
    <input type="text" placeholder="Search by name or ID">
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Last Update</th>
            <th>利用部門</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>001</td>
            <td>Taro Yamada</td>
            <td class="ok">OK</td>
            <td>2026-04-21 10:30</td>
            <td>システム管理部</td>
        </tr>

        <tr>
            <td>002</td>
            <td>Hanako Sato</td>
            <td class="danger">Need Help</td>
            <td>2026-04-21 10:20</td>
            <td>システム管理部</td>
        </tr>

        <tr>
            <td>003</td>
            <td>Ichiro Tanaka</td>
            <td class="unknown">Not Confirmed</td>
            <td>-</td>
            <td>システム管理部</td>
        </tr>
    </tbody>
</table>

</div>

</body>
</html>