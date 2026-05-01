<?php
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();




if($_SERVER["REQUEST_METHOD"] === "GET"){

    if(!isset($_GET["responce_id"])){
    header("Location: " . WEB_ROOT . "safe_List.php");
    exit;
    }

    $csrf_token = csrf_token_generate();

    try{
        $db = db_connect();
        $sql = "SELECT * FROM safety WHERE responce_id = :responce_id AND isDelete = 0";
        $stmt = db_query($db, $sql, [":responce_id" => $_GET["responce_id"]]);
        $result = $stmt -> fetch(PDO::FETCH_ASSOC);

        //アクセスしたユーザーが本人であることを確認
        if(!$result || $result["emp_id"] !== $_SESSION["connect_user"]["emp_id"]){
            header("Location: " . WEB_ROOT . "safe_List.php");
            exit;
        }

    }catch(Exception $e){
        echo "エラー".$e->getMessage();
        exit;
    }finally{
        $db = null;
        $stmt = null;
    }
}else if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]){
        echo "正しいリクエストではありません";
        exit;
    }
    unset($_SESSION["csrf_token"]);
    $responce_id = $_POST["responce_id"];

    try{
        $db = db_connect();
        $sql = "SELECT * FROM safety WHERE responce_id = :responce_id AND isDelete = 0";
        $stmt = db_query($db, $sql, [":responce_id" => $responce_id]);
        $result = $stmt -> fetch(PDO::FETCH_ASSOC);

        if(!$result || $result["emp_id"] !== $_SESSION["connect_user"]["emp_id"]){
            // header("Location: " . WEB_ROOT . "safe_List.php");
            echo "フェッチ失敗oremp_idの不一致";
            exit;
        }
        $db -> beginTransaction();

        $update_data = [
            "safe" => $_POST["safe"],
            "can_work" => $_POST["work"], // formのnameはwork、DBカラムはcan_work
            "note" => $_POST["note"]
        ];

        // SQLインジェクションを考慮し、intvalでキャストしてWHEREに直接渡す
        db_update($db, "safety", "responce_id = " . intval($responce_id), $update_data);
        
        $db -> commit();

        $db = null;

        header("Location: " . WEB_ROOT . "safe_Detail.php?responce_id=" . $responce_id);
        exit;

    }catch(Exception $e){
        if($db) $db->rollBack();
        echo "エラー".$e->getMessage();
        exit;
    }finally{
        $db = null;
    }

}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>安否詳細編集画面</title>
    <link rel="stylesheet" href="./css/safe_Edit.css">
</head>

<body>
    <header>
        <h3>安否詳細編集</h3>
        <button type="logout" id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>
        <form action="./safe_edit.php" method="POST">
<!--form-->
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token ?? ''); ?>">
        <input type="hidden" name="responce_id" value="<?php echo h($_GET["responce_id"] ?? ''); ?>">
        <!-- なおこのidは後で整合性を検証する、IDORの原因になるので -->


        <div class="card">
            <label>社員ID：</label><br>
            <input type="text" name="emp_id" value="<?php echo h($result["emp_id"] ?? ''); ?>" readonly>
        </div>

        <div class="card">
            <label>現在の状況：</label><br>
            <select name="safe">
                <option value="1">無事</option>
                <option value="2">軽傷</option>
                <option value="3">重傷</option>
            </select>
        </div>
        <div class="card">
            <label>勤務可否：</label><br>
            <select name="work">
                <option value="0">勤務可能</option>
                <option value="1">勤務不可</option>
            </select>
        </div>
        <div class="card">
            <label>コメント：</label><br>
            <textarea name="note" placeholder="無事・勤務可能以外の方は、現在の具体的な状況を入力してください。"></textarea>
        </div>
        <div class="action">
            <button type="submit">保存</button>
            <!-- form送信する -->
            <button type="button" onclick="location.href='./safe_List.php'">戻る</button>
        </div>
        </form>
    </main>

</body>

</html>
