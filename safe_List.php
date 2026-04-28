<?php
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();

try{
    $db = db_connect();

    //他の部署の情報を見る権利があるかどうかを判定するブロック。
    if($_SESSION["connect_user"]["administrator"] === 1 || (isset($_SESSION["connect_user"]["post_id"]) && $_SESSION["connect_user"]["post_id"] !== 1)){
        $can_see_other_department = true;
    }

    if(isset($can_see_other_department)){
        $limit = "";
    }else{
        $limit = " AND e.department =" . $_SESSION["connect_user"]["department"];
    }

    $sql = "SELECT COUNT(*) FROM safety as s JOIN employee as e ON s.emp_id = e.emp_id WHERE s.isDelete = 0" . $limit;
    $stmt = db_query($db, $sql);
    $safety_count = $stmt -> fetchColumn();

    $sql = "SELECT d_id, dname FROM department";
    $stmt = db_query($db, $sql);
    $department_names = $stmt -> fetchALL(PDO::FETCH_ASSOC);

    $safety_info_list = [];//安否情報のリストを入れる配列。取得した情報から生成した、一行分のHTML要素を格納した配列を格納する、二重配列になる予定
    $sql = "SELECT s.responce_id, s.emp_id,e.ename,s.safe,s.can_work,d.dname,DATE_FORMAT(s.create_at,'%Y-%m-%d %H:%i'),DATE_FORMAT(s.update_at,'%Y-%m-%d %H:%i') FROM safety as s JOIN employee as e ON s.emp_id = e.emp_id JOIN department as d ON e.department = d.d_id WHERE s.isDelete = 0" . $limit;
    $page = 0;
    if(isset($_GET["page"])){
        $page = (int)$_GET["page"];
    }
    $one_page_limit = 20;
    $start_at = ($page) * $one_page_limit;
    $limit_sql = " LIMIT 20 OFFSET " . ($page * $one_page_limit);

    $querys = [];//検索条件が指定されていた場合は、それらの情報を格納する
    if(isset($_GET["ename"]) && $_GET["ename"] !== ""){
        $sql .= " AND e.ename LIKE :ename";
        $querys[":ename"] = "%" . $_GET["ename"] . "%";
    }
    if(isset($_GET["safe"]) && $_GET["safe"] !== ""){
        $sql .= " AND s.safe = :safe";
        $querys[":safe"] = (int)$_GET["safe"];
    }
    if(isset($_GET["can_work"]) && $_GET["can_work"] !== ""){
        $sql .= " AND s.can_work = :can_work";
        $querys[":can_work"] = (int)$_GET["can_work"];
    }
    if(isset($_GET["department"]) && $_GET["department"] !== ""){
        $sql .= " AND e.department = :department";
        $querys[":department"] = (int)$_GET["department"];
    }
    $sql .= $limit_sql;

    $stmt = db_query($db, $sql, $querys);

    while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
        $safety_info = "<tr>";
        foreach($row as $key => $value){
            if($key === "can_work"){
                $value = $value ? "勤務不可" : "勤務可";
            }
            if($key === "safe"){
                if($value === 0){
                    $safety_info .= "<td class='ok'>無事</td>";
                }else if($value === 1) {
                    $safety_info .= "<td class='injury'>軽傷</td>";
                }else if($value === 2) {
                    $safety_info .= "<td class='danger'>重傷</td>";
                }
            }else if($key === "ename"){
                $safety_info .= "<td><a href='./safe_Detail.php?id=" . $row["responce_id"] . "'>" . h($value) . "</a></td>";
            }else{
                $safety_info .= "<td>" . h($value) . "</td>";
            }
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

<h1 class="page-title">社員安否一覧画面</h1>

<button><a href="./logout.php">ログアウト</a></button>

<div class="container">

    <form action="./safe_List.php" method ="get" class="search">
        <div>
            <input type="number" name = "emp_id" placeholder="社員IDを指定" value="<?php if(isset($_GET["emp_id"])){echo h($_GET["emp_id"]);} ?>">
        </div>
        <div>
            <input type="text" name="ename" placeholder="社員名を指定" value="<?php if(isset($_GET["ename"])){echo h($_GET["ename"]);} ?>">
        </div>
        <div>
            <select name="safe">
                <option value="" selected>安否状況を指定</option>
                <option value="0">無事</option>
                <option value="1">軽傷</option>
                <option value="2">重傷</option>
            </select>
        </div>
        <div>
            <select name = "can_work">
                <option value="" selected>勤務の可否を指定</option>
                <option value="0">勤務可</option>
                <option value="1">勤務不可</option>
            </select>
        </div>
        <?php if(isset($can_see_other_department)): ?>
        <div>
            <select name = "department">
                <option value="" selected>所属部署を指定</option>
                <?php foreach($department_names as $dep): ?>
                    <option value="<?php echo h($dep["d_id"]); ?>"><?php echo h($dep["dname"]); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <button type = "submit">この条件で絞り込み</button>
    </form>

<table>
    <thead>
        <tr>
            <th>報告ID</th>
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




</div>

</body>
</html>