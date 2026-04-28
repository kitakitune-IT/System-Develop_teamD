<?php
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();

if($_SERVER["REQUEST_METHOD"] === "GET"){
    $_SESSION["user_have_to_Regist"] = true;
    $db = db_connect();
    $sql = "SELECT * FROM safety WHERE emp_id = :emp_id AND isDelete = 0";
    $stmt = db_query($db, $sql, [":emp_id" => $_SESSION["connect_user"]["emp_id"]]);
    $result = $stmt -> fetch(PDO::FETCH_ASSOC);
    if($result){
        $_SESSION["user_have_to_Regist"] = false;
    }

    $csrf_token = csrf_token_generate();

}else if($_SERVER["REQUEST_METHOD"] === "POST"){
    try{

    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]){
        throw new csrfException("正しいリクエストではありません");
    }

    $db = db_connect();

    $Regist_data = [
        "emp_id" => $_SESSION["connect_user"]["emp_id"],
        "safe" => $_POST["safe"] ?? 0,
        "can_work" => $_POST["can_work"] ?? 0,
        "note" => $_POST["note"] ?? '',
    ];

    $db ->beginTransaction();

    db_insert($db, "safety", $Regist_data);

    $db ->commit();

    $db = null;

    header("location:" . WEB_ROOT . "safe_List.php");
    exit;


    }catch(PDOException $poe){
    $db ->rollback();
    echo "DB接続エラー\n".$poe->getMessage();
    exit;
    }catch(Exception $e){
    $db ->rollback();
    echo "エラー".$e->getMessage();
    header("Location: " . WEB_ROOT . "regist_failed.php");
    }catch(Error $e){
    $db ->rollback();
    echo "エラー".$e->getMessage();
    }catch(csrfException $e){
    echo "エラー".$e->getMessage();
    }finally{
    $stmt = null;
    $db = null;
    }
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>安否登録画面</title>
    <link rel="stylesheet" href="./css/safe_Regist.css">
</head>

<body>
    <header>
        <h3>安否登録</h3>
        <a href="./logout.php" id="logout">ログアウト</a>
    </header>
    <main>
        <?php if (!$_SESSION["user_have_to_Regist"]): ?>
            <p>あなたの安否情報は既に登録されています。情報の編集は、安否詳細画面より行ってください。</p>
            <a href="./safe_List.php">安否一覧画面</a>
        <?php endif; ?>
        <form action="./safe_Regist.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div>
                <p>現在の状況</p>
                <label><input type="radio" name="safe" id="damage1" value="0" selected>無事</label>
                <label><input type="radio" name="safe" id="damage2" value="1">軽傷</label>
                <label><input type="radio" name="safe" id="damage3" value="2">重傷</label>

            </div>
            <div>
                <p>勤務可否</p>
                <label><input type="radio" name="can_work" id="work1" value="0">勤務可能</label>
                <label><input type="radio" name="can_work" id="work2" value="1">勤務不可</label>
            </div>

            <input type="text" name="note" id="note" placeholder="無事・勤務可能以外の方は、具体的な状況を入力してください。">

            <button type="submit" id="submit">送信</button>
        </form>
        <!-- 本番はここにendif -->
    </main>

</body>

</html>