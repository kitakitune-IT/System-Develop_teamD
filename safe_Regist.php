<?php
require_once __DIR__ . "/root.php";
require_once __DIR__ . "/def.php";
session_start();
if(!isset($_SESSION["emp_id"]) || !isset($_SESSION["time_limit"])|| $_SESSION["time_limit"] < time()){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}//セッションが存在しないか期限切れの場合はログアウトページでセッション破壊

if($_SERVER["REQUEST_METHOD"] === "POST"){
    try{
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT,false);
    $emp_id = $_SESSION["emp_id"];
    $safety = [
        "safe" => $_POST["safe"],
        "go_office" => $_POST["go_office"],
        "note" => $_POST["note"],
    ];
    //https://www.php.net/manual/ja/faq.passwords.php#faq.passwords.fasthash
    //パスワードの解析の際にはpassword_verify()を使う必要があるらしい
    $db ->begintransaction();

    $sql = "INSERT INTO safety (emp_id, safe, go_office, note) VALUES (:emp_id, :safe, :go_office, :note)";
    $stmt = $db -> prepare($sql);
    $stmt -> execute(["emp_id" => $emp_id, "safe" => $safety["safe"], "go_office" => $safety["go_office"], "note" => $safety["note"]]);

    $db ->commit();

    $stmt = null;
    $db = null;

    header("location:" . WEB_ROOT . "safe_List.php");
    exit;


    }catch(PDOException $poe){
    $db ->rollback();
    echo "DB接続エラー\n".$poe->getMessage();
    header("Location: " . WEB_ROOT . "regist_failed.php");
    }catch(Exception $e){
    $db ->rollback();
    echo "エラー".$e->getMessage();
    header("Location: " . WEB_ROOT . "regist_failed.php");
    }catch(Error $e){
    $db ->rollback();
    echo "エラー".$e->getMessage();
    header("Location: " . WEB_ROOT . "regist_failed.php");
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
    <link rel="stylesheet" href="./css/To-roku.css">
</head>

<body>
    <header>
        <h3>安否登録</h3>
        <a href="./logout.php" id="logout">ログアウト</a>
    </header>
    <main>
        <form action="./safe_Regist.php" method="post">

            <div>
                <p>現在の状況</p>
                <label><input type="radio" name="safe" id="damage1" value="1">無事</label>
                <label><input type="radio" name="safe" id="damage2" value="2">軽傷</label>
                <label><input type="radio" name="safe" id="damage3" value="3">重傷</label>

            </div>
            <div>
                <p>勤務可否</p>
                <label><input type="radio" name="go_office" id="work1" value="1">勤務可能</label>
                <label><input type="radio" name="go_office" id="work2" value="2">勤務不可</label>
            </div>

            <input type="text" name="note" id="note" placeholder="無事・勤務可能以外の方は、具体的な状況を入力してください。">

            <button type="submit" id="submit">送信</button>
        </form>
    </main>

</body>

</html>