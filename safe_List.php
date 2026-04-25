<?php
require_once __DIR__ . "/root.php";
require_once __DIR__ . "/def.php";
session_start();
if(!isset($_SESSION["emp_id"]) || !isset($_SESSION["time_limit"])|| $_SESSION["time_limit"] < time()){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}//セッションが存在しないか期限切れの場合はログアウトページでセッション破壊

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
try{
    $db = new PDO($dsn, DB_USER , DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT,false);
// CREATE TABLE IF NOT EXISTS employee(
//     emp_id          INT AUTO_INCREMENT,
//     ename           VARCHAR(50) NOT NULL,
//     birth           DATE NOT NULL,
//     tel             VARCHAR(50) NOT NULL,
//     department      INT NOT NULL,
//     post            VARCHAR(20) NOT NULL,
//     administrator   BOOLEAN DEFAULT 0,
//     password        VARCHAR(255) NOT NULL,
//     create_id       INT NOT NULL,
//     create_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     update_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     PRIMARY KEY(emp_id),
//     FOREIGN KEY(department) REFERENCES department(d_id),
//     UNIQUE(tel)
// );

//     CREATE TABLE IF NOT EXISTS safety(
//     responce_id INT AUTO_INCREMENT,
//     emp_id      INT,
//     safe        BOOLEAN NOT NULL DEFAULT 0,
//     status      VARCHAR(10) NOT NULL DEFAULT '無事',
//     go_office   BOOLEAN NOT NULL DEFAULT 0,
//     note        VARCHAR(255),
//     create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     isDelete  BOOLEAN DEFAULT 0,
//     PRIMARY KEY(responce_id),
//     FOREIGN KEY(emp_id) REFERENCES employee(emp_id)
// );テーブルの構造を置いておく
//必要な情報は、社員ID、社員名(同姓同名がありえるので)、安否、勤務の可否、部署、安否登録日時、更新日時


    $safety_count;
    $sql = "SELECT COUNT(*) FROM safety as s JOIN employee as e ON s.emp_id = e.emp_id WHERE s.isDelete = 0";
    //退社済みでない(社員テーブルに存在する)、かつ論理削除されていないデータの数を調べる
    //このデータは後で、細かい表示と、ページの切り替えという名の再検索で使う
    $stmt = $db -> prepare($sql);
    $stmt -> execute();
    $safety_count = $stmt -> fetchColumn();
    //データが一列であることが確定している時は、fetchColumnを使うと、連想配列ではなくそのままの値で入るから便利なようだ

    $safety_info_list = [];//安否情報のリストを入れる配列。取得した情報から生成した、一行分のHTML要素を格納した配列を格納する、二重配列になる予定
    $sql = "SELECT s.emp_id,e.ename,s.safe,s.go_office,e.department,DATE_FORMAT(s.create_at,'%Y-%m-%d %H:%i'),DATE_FORMAT(s.update_at,'%Y-%m-%d %H:%i') FROM safety as s JOIN employee as e ON s.emp_id = e.emp_id WHERE s.isDelete = 0";

    $page = 0;
    if(isset($_GET["page"])){
        $page = (int)$_GET["page"];
    }
    $one_page_limit = 20;
    $start_at = ($page) * $one_page_limit;
    $limit_sql = " LIMIT 20 OFFSET " . ($page * $one_page_limit);

    $querys = [];//検索条件が指定されていた場合は、その条件をWHERE句に追加する必要があるため、それらの情報を格納する配列
    if(isset($_GET["safe"]) && $_GET["safe"] !== ""){
        $sql .= " AND s.safe = :safe";
        $querys[":safe"] = $_GET["safe"];
    }
    if(isset($_GET["can_work"]) && $_GET["can_work"] !== ""){
        $sql .= " AND s.go_office = :can_work";
        $querys[":can_work"] = $_GET["can_work"];
    }
    if(isset($_GET["department"]) && $_GET["department"] !== ""){
        $sql .= " AND e.department = :department";
        $querys[":department"] = $_GET["department"];
    }//この辺では、検索条件が存在して、空でないのなら、sql文に文字結合で条件を追加する処理をしている。
    //また、executeの際に渡して安全性を高めるために、キーとバリューがそれに対応する形で連想配列に入れている

    $stmt = $db -> prepare($sql);
    $stmt -> execute($querys);
    //今回だと、tdobyの中にtr/tdの形で指定されているのでそれに沿った要素を作る必要がある
    while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
        $safety_info = "<tr>";
        foreach($row as $key => $value){
            if($key === "go_office"){
                $value = $value ? "出社不可" : "出社可";
            }elseif($key === "safe"){
                if($value === 0){
                    $value = "無事";
                }else if($value === 1) {
                    $value = "軽傷";
                }else if($value === 2) {
                    $value = "重傷";   
                }
            }
            $safety_info .= "<td>" . htmlspecialchars($value) . "</td>";
        }
        $safety_info .= "</tr>";
        $safety_info_list[] = $safety_info;
    }

    $stmt = null;
    $db = null;
    
}catch(PDOException $poe){
    exit("DBエラー".$poe->getMessage());
}catch(Exception $e){
    exit("エラー".$e->getMessage());
}catch(Error $e){
    exit("エラー".$e->getMessage());
}finally{
    $stmt = null;
    $db = null;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="./css/社員安否一覧画面.css">
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
            <th>社員ID</th>
            <th>社員名</th>
            <th>状況</th>
            <th>勤務可否</th>
            <th>所属部署</th>
            <th>安否登録日時</th>
            <th>更新日時</th>
        </tr>
    </thead>

    <tbody>
    <?php 
        echo implode($safety_info_list);
     ?>
    </tbody>
</table>
<!-- 明日はこれ以降に検索条件の指定と、何ページ目からデータを見るか、の選択肢を記述するフォームを作る -->


</div>

</body>
</html>