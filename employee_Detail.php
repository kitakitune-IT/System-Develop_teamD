<?php 
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();
$user_id_admin = admin_check();

if(!$user_id_admin){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}

if(!isset($_GET["emp_id"])){
    header("Location: " . WEB_ROOT . "safe_List.php");
    echo "社員IDが指定されていません。";
    exit;
}

try{
    $db = db_connect();
    $sql = "SELECT e.emp_id, e.ename, e.birth, e.tel, d.dname as '部署', p.pname as '役職',
    CASE WHEN e.administrator = 1 THEN '管理者'
    ELSE '一般'
    END AS administrator,
    e.create_id, DATE_FORMAT(e.create_at, '%Y-%m-%d %H:%i:%s') as create_at, DATE_FORMAT(e.update_at, '%Y-%m-%d %H:%i:%s') as update_at FROM employee as e
    JOIN department as d
    ON d.d_id = e.d_id
    JOIN post as p
    ON p.p_id = e.p_id
    WHERE e.emp_id = :emp_id";
    //社員id,社員名,誕生日,電話番号,部署名,役職,管理者権限,作成日時,更新日時を取った
    $stmt = db_query($db, $sql,[":emp_id" => $_GET["emp_id"]]);
    $result = $stmt -> fetch(PDO::FETCH_ASSOC);

    if($result == false){
    echo "データが存在しませんでした。";
    exit;
    }

}catch(Exception $e){
    echo "エラー".$e->getMessage();
    exit;
}finally{
    $db = null;
    $stmt = null;
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員詳細ページ</title>
    <!-- cssファイルのパスは適宜調整してください -->
    <link rel="stylesheet" href="./css/safe_Detail.css"> 
</head>

<body>
    <header>
        <h3>社員詳細</h3>
        <button onclick="location.href='./admin_menu.php'">管理者用画面へ</button>
        <button id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>

        <div class="card">
            <p><span>社員ID：</span><?php echo h($result["emp_id"]); ?></p>
            <p><span>社員名：</span><?php echo h($result["ename"]); ?></p>
            <p><span>生年月日：</span><?php echo h($result["birth"]); ?></p>
            <p><span>電話番号：</span><?php echo h($result["tel"]); ?></p>
            <p><span>部署：</span><?php echo h($result["部署"]); ?></p>
            <p><span>役職：</span><?php echo h($result["役職"]); ?></p>
            <p><span>権限：</span><?php echo $result["administrator"] == 1 ? "管理者" : "一般"; ?></p>
            <p><span>最終編集者ID：</span><?php echo h($result["create_id"]); ?></p>
            <p><span>登録日時：</span><?php echo h($result["create_at"]); ?></p>
            <p><span>更新日時：</span><?php echo h($result["update_at"]); ?></p>
        </div>

        <div class="action">
            <button onclick="location.href='./employee_Edit.php?emp_id=<?php echo h($result["emp_id"]); ?>'">編集</button>
            <button onclick="location.href='./employee_Password_Reset.php?emp_id=<?php echo h($result["emp_id"]); ?>'">パスワードを変更</button>
            <button onclick="location.href='./employee_List.php'">戻る</button>
        </div>

    </main>

</body>

</html>