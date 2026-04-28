<?php 
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();

if(!isset($_GET["responce_id"])){
    header("Location: " . WEB_ROOT . "safe_List.php");
    exit;
}


try{
    $db = db_connect();
    $sql = "SELECT * FROM safety as s JOIN employee as e ON s.emp_id = e.emp_id WHERE responce_id = :responce_id AND s.isDelete = 0";
    $stmt = db_query($db, $sql, [":responce_id" => $_GET["responce_id"]]);
    $result = $stmt -> fetch(PDO::FETCH_ASSOC);

    //管理者か役職持ちである、そうでないなら自分の所属する部署のデータであることを検証
    if(!$result){
        header("Location: " . WEB_ROOT . "safe_List.php");
        exit;
    }
    if($_SESSION["connect_user"]["administorator"] !== 1 || ($_SESSION["connect_user"]["post_id"] > 1)){
        if($_SESSION["connect_user"]["department"] !== $result["department"]){
            header("Location: " . WEB_ROOT . "safe_List.php");
            exit;
        }
    }

}catch(Exception $e){
    echo "エラー".$e->getMessage();
    exit;
}finally{
    $stmt = null;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>安否詳細画面</title>
    <link rel="stylesheet" href="../style/Syo-sai.css">
</head>

<body>
    <header>
        <h3>安否詳細</h3>
        <button id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>

        <div class="card">
            <p><span>名前：</span>山田太郎</p>
            <p><span>安否：</span>無事</p>
            <p><span>出勤可否：</span>可能</p>
            <p><span>コメント：</span>無事です。いつも通り出勤できます。</p>
        </div>

        <div class="action">
            <button onclick="location.href='edit.html'">編集</button>
            <button onclick="location.href=''">戻る</button>
            <!-- /ここで社員安否一覧画面に戻る -->
        </div>

    </main>

</body>

</html>