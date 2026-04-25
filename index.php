<?php
require_once __DIR__ . "/root.php";
require_once __DIR__ . "/def.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    try{
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT,false);
    $user_input = [
        "ename" => $_POST["ename"],
        "password" => ($_POST["password"]),
    ];
    //https://www.php.net/manual/ja/faq.passwords.php#faq.passwords.fasthash
    //パスワードの解析の際にはpassword_verify()を使う必要があるらしい

    $sql = "SELECT * FROM employee WHERE ename = :ename";
    $stmt = $db -> prepare($sql);
    $stmt -> execute(["ename" => $user_input["ename"]]);
    $user = $stmt -> fetch();
    // $stmt = $db->prepare('SELECT * FROM `employee` WHERE ename = ?');
    // $stmt->execute([$user_input["ename"]]);
    // $user = $stmt->fetch();
    if($user && password_verify($user_input["password"], $user["password"])){

        $check = session_start([
            'cookie_httponly' => true,
            'cookie_secure'   => false,
        ]);//今は開発であってHTTPSでないのでそっちはfalse、js対策は常時に

        if(!($check)){
            header("Location: " . WEB_ROOT . "index.php");
            exit;
        }
        
        session_regenerate_id(true);
        $_SESSION["emp_id"] = $user["emp_id"];
        $_SESSION["ename"] = $user["ename"];
        $_SESSION["time_limit"] = time()+7200;//2時間後にセッションの有効期限が切れるようにする
        //header("Location: /次のページ.php");
        header("Location: " . WEB_ROOT . "safe_Regist.php");
    }else{
        header("Location: " . WEB_ROOT . "failed.php");
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
    <div class="desc-wrap">
        <div class="description">
            <h2 class="item-name">X社ログイン画面</h2>

            <div class="materials">
                <form action="./index.php" method="post">
                    <li>
                        <input type="text" name="ename" placeholder="社員ID">
                    </li>
                    <li>
                        <input type="password" name="password"placeholder="パスワード">
                    </li>
                    <li>
                        <button type="submit">ログイン</button> 
                        </a>
                    </li>
                </form>
            </div>

        </div>
    </div>
</main>

</body>
</html>

