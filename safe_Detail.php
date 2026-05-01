<?php 
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();

if(!isset($_GET["responce_id"])){
    header("Location: " . WEB_ROOT . "safe_List.php");
    echo "レスポンスIDが指定されていません。";
    exit;
}
//作るべき機能


try{
    $db = db_connect();
    $sql = "SELECT s.*, e.d_id, e.ename
            FROM safety s
            INNER JOIN employee e ON s.emp_id = e.emp_id
            WHERE s.responce_id = :responce_id 
            AND s.isDelete = 0";
    $stmt = db_query($db, $sql, [":responce_id" => $_GET["responce_id"]]);
    $result = $stmt -> fetch(PDO::FETCH_ASSOC);

    if($result == false){
    echo "データが存在しませんでした。";
    exit;
    }

    $responce_id = $result["responce_id"];

    if($_SESSION["connect_user"]["administrator"] == 1 || (isset($_SESSION["connect_user"]["p_id"]) && $_SESSION["connect_user"]["p_id"] != 1)){
        $can_see_this_data = true;
    }else{
        if($result["d_id"] == $_SESSION["connect_user"]["d_id"]){
            $can_see_this_data = true;
        }else{
            echo "他部署の情報は見れません。";
            exit;
        }
    }

    if($result["emp_id"] == $_SESSION["connect_user"]["emp_id"]){
        $user_can_edit = true;
    }

    if($result["safe"] == 0){
        $result["safe"] = "無事";
    }else if($result["safe"] == 1){
        $result["safe"] = "軽傷";
    }else if($result["safe"] == 2){
        $result["safe"] = "重傷";
    }

    if($result["can_work"] == 0){
        $result["can_work"] = "勤務可能";
    }else if($result["can_work"] == 1){
        $result["can_work"] = "勤務不可";
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
    <title>安否詳細画面</title>
    <link rel="stylesheet" href="./css/safe_Detail.css">
</head>

<body>
    <header>
        <h3>安否詳細</h3>
        <button id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>

        <div class="card">
            <p><span>名前：</span><?php echo htmlspecialchars($result["ename"]); ?></p>
            <p><span>安否：</span><?php echo htmlspecialchars($result["safe"]); ?></p>
            <p><span>出勤可否：</span><?php echo htmlspecialchars($result["can_work"]); ?></p>
            <p>
                <span>コメント：</span>
                 <?php echo ($result['note'] == null || $result['note'] === '') ? 'コメントはありません' : h($result['note']); ?>
            </p>
        </div>

        <div class="action">
            <?php if(isset($user_can_edit) && $user_can_edit): ?>
            <button onclick="location.href='./safe_Edit.php?responce_id=<?php echo htmlspecialchars($responce_id); ?>'">編集</button>
            <?php endif; ?>
            <button onclick="location.href='./safe_List.php'">戻る</button>
            <!-- /ここで社員安否一覧画面に戻る -->
        </div>

    </main>

</body>

</html>