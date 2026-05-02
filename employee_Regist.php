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

if($_SERVER["REQUEST_METHOD"] === "GET"){
    $csrf_token = csrf_token_generate();

    try{
        $db = db_connect();
        
        // 部署一覧の取得
        $sql_dep = "SELECT d_id, dname FROM department";
        $stmt_dep = db_query($db, $sql_dep);
        $departments = $stmt_dep->fetchAll(PDO::FETCH_ASSOC);

        // 役職一覧の取得
        $sql_post = "SELECT p_id, pname FROM post";
        $stmt_post = db_query($db, $sql_post);
        $posts = $stmt_post->fetchAll(PDO::FETCH_ASSOC);

    }catch(Exception $e){
        echo "エラー: " . $e->getMessage();
        exit;
    }finally{
        $stmt_dep = null;
        $stmt_post = null;
        $db = null;
    }

}else if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]){
        echo "正しいリクエストではありません";
        exit;
    }
    unset($_SESSION["csrf_token"]);

    $tel = $_POST["tel"];

    try{
        $db = db_connect();

        // 電話番号の重複チェック
        $sql_tel_check = "SELECT emp_id FROM employee WHERE tel = :tel";
        $stmt_tel_check = db_query($db, $sql_tel_check, [":tel" => $tel]);
        
        if($stmt_tel_check->fetch()){
            echo "入力された電話番号は既に登録されています。";
            exit;
        }

        $db->beginTransaction();

        $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

        $insert_data = [
            "ename" => $_POST["ename"],
            "birth" => $_POST["birth"],
            "tel" => $tel,
            "d_id" => $_POST["d_id"],
            "p_id" => $_POST["p_id"],
            "administrator" => $_POST["administrator"],
            "password" => $password_hash,
            "create_id" => $_SESSION["connect_user"]["emp_id"] // 作成者のID
        ];

        // db_insert関数を使用して登録し、追加されたemp_idを受け取る
        // lastInsertIdをreturnしてるので、autoincrementのemp_idが返ってくる
        $new_emp_id = db_insert($db, "employee", $insert_data);
        
        $db->commit();
        $db = null;

        header("Location: " . WEB_ROOT . "employee_Detail.php?emp_id=" . $new_emp_id);
        exit;

    }catch(Exception $e){
        if(isset($db)) $db->rollBack();
        echo "エラー: " . $e->getMessage();
        exit;
    }finally{
        $stmt_tel_check = null;
        $db = null;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>社員登録画面</title>
    <link rel="stylesheet" href="./css/safe_Edit.css"> 
</head>
<body>
    <header>
        <h3>社員登録</h3>
        <button type="button" onclick="location.href='./admin_menu.php'">管理者用画面へ</button>
        <button type="button" id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>
        <form action="./employee_Regist.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token ?? ''); ?>">

            <div class="card">
                <label>社員名：</label><br>
                <input type="text" name="ename" required>
            </div>

            <div class="card">
                <label>生年月日：</label><br>
                <input type="date" name="birth" required>
            </div>

            <div class="card">
                <label>電話番号：</label><br>
                <input type="text" name="tel" required>
            </div>

            <div class="card">
                <label>部署：</label><br>
                <select name="d_id">
                    <?php foreach($departments as $dep): ?>
                        <option value="<?php echo h($dep['d_id']); ?>">
                            <?php echo h($dep['dname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="card">
                <label>役職：</label><br>
                <select name="p_id">
                    <?php foreach($posts as $post): ?>
                        <option value="<?php echo h($post['p_id']); ?>">
                            <?php echo h($post['pname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="card">
                <label>管理者権限：</label><br>
                <select name="administrator">
                    <option value="0">一般</option>
                    <option value="1">管理者</option>
                </select>
            </div>

            <div class="card">
                <label>初期パスワード：</label><br>
                <input type="password" name="password" required>
            </div>

            <div class="action">
                <button type="submit">登録</button>
                <button type="button" onclick="location.href='./employee_List.php'">戻る</button>
            </div>
        </form>
    </main>
</body>
</html>