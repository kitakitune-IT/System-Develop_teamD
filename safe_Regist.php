<?php
require_once __DIR__ . "/root.php";
require_once __DIR__ . "/def.php";
session_start();
if(!isset($_SESSION["emp_id"]) || !isset($_SESSION["time_limit"])|| $_SESSION["time_limit"] < time()){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}else{

}
if($_SERVER["REQUEST_METHOD"] === "GET"){
    $_SESSION["user_have_to_Regist"] = true;//既に情報を登録しているユーザーかどうかを確かめる
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT,false);
    $emp_id = $_SESSION["emp_id"];
    $sql = "SELECT * FROM safety WHERE emp_id = :emp_id AND isDelete = 0";
    $stmt = $db -> prepare($sql);
    $stmt -> execute(["emp_id" => $emp_id]);
    $result = $stmt -> fetch(PDO::FETCH_ASSOC);
    if($result){
        $_SESSION["user_have_to_Regist"] = false;
    }

    $toke_byte = random_bytes(16);
    $csrf_token = bin2hex($toke_byte);

$_SESSION['csrf_token'] = $csrf_token;//POSTの時にも再生成すると、絶対にトークンが一致しないのでget指定をする

}else if($_SERVER["REQUEST_METHOD"] === "POST"){
    try{

    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]){
        throw new csrfException("正しいリクエストではありません");
    }

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

    $sql = "INSERT INTO safety (emp_id, safe, can_work, note) VALUES (:emp_id, :safe, :go_office, :note)";
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
    }catch(csrfException $e){
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
        <?php if (!$_SESSION["user_have_to_Regist"]): ?>
            <p>あなたの安否情報は既に登録されています。情報の編集は、安否詳細画面より行ってください。</p>
            <a href="./safe_List.php">安否一覧画面</a>
        <?php else: ?>
            <p>安否情報を登録してください。</p>
        <?php endif; ?>
        <form action="./safe_Regist.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div>
                <p>現在の状況</p>
                <label><input type="radio" name="safe" id="damage1" value="0">無事</label>
                <label><input type="radio" name="safe" id="damage2" value="1">軽傷</label>
                <label><input type="radio" name="safe" id="damage3" value="2">重傷</label>

            </div>
            <div>
                <p>勤務可否</p>
                <label><input type="radio" name="go_office" id="work1" value="0">勤務可能</label>
                <label><input type="radio" name="go_office" id="work2" value="1">勤務不可</label>
            </div>

            <input type="text" name="note" id="note" placeholder="無事・勤務可能以外の方は、具体的な状況を入力してください。">

            <button type="submit" id="submit">送信</button>
        </form>
    </main>

</body>

</html>