<?php
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
$check = session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => false,
]);//今は開発であってHTTPSでないのでそっちはfalse、js対策は常時に

if(!($check)){
    header("Location: " . WEB_ROOT . "index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
    try{
    $db = db_connect();
    $user_input = [
        "emp_id" => $_POST["emp_id"],
        "password" => ($_POST["password"]),
    ];
    //https://www.php.net/manual/ja/faq.passwords.php#faq.passwords.fasthash

    $login_success = db_login($db, $user_input);
    //$_SESSION["connect_user"]に、ユーザーのデータが入っている
    //$_SESSION["connection_time_limit"]で有効期限を管理する
    if($login_success){
        if(isset($_SESSION["connect_user"]["administrator"]) && $_SESSION["connect_user"]["administrator"] === 1){
            header("Location: " . WEB_ROOT . "admin_menu.php");
            exit;
        }
        header("Location: " . WEB_ROOT . "safe_Regist.php");
        exit;
    }else{
        header("Location: " . WEB_ROOT . "failed.php");
        exit;
    }

    }catch(Exception $e){
    exit("エラー".$e->getMessage());
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/ログイン画面.css">
    <title>ログイン画面</title>

</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="./" class="logo">災害安否報告システム</a>
    </div>
</header>

<main>
    <?php if (isset($_SESSION["connect_user"])): ?>
        <div class="induction">
            <p class="item-name">既にログインしています！</p>
            <div class="induction ">
                <a class="btn-link" href="./safe_Regist.php">安否登録画面へ</a>
                <br>
                <a class="btn-link btn-link-secondary" href="./logout.php">ログアウト</a>
            </div>
        </div>
    <?php else: ?>
        <div class="materials">
            <form action="./index.php" method="post">
                <div class = "user-input">
                    <input type="text" name="emp_id" placeholder="社員ID">
                </div>
                <div class = "user-input">
                    <input type="password" name="password"placeholder="パスワード">
                </div>
                <div class = "btn">
                    <button type="submit">ログイン</button> 
                </div>
            </form>
    </div>
    
    <?php endif; ?>
</main>

</body>
</html>

