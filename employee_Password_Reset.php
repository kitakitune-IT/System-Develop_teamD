<?php
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();
$user_id_admin = admin_check();

// 管理者以外は弾く
if(!$user_id_admin){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] === "GET"){
    if(!isset($_GET["emp_id"])){
        header("Location: " . WEB_ROOT . "employee_List.php");
        exit;
    }

    $csrf_token = csrf_token_generate();
    $emp_id = $_GET["emp_id"];

    try{
        $db = db_connect();
        
        $sql = "SELECT emp_id, ename FROM employee WHERE emp_id = :emp_id";
        $stmt = db_query($db, $sql, [":emp_id" => $emp_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$employee){
            echo "データが存在しませんでした。";
            exit;
        }

    }catch(Exception $e){
        echo "エラー: " . $e->getMessage();
        exit;
    }finally{
        $stmt = null;
        $db = null;
    }

}else if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]){
        echo "正しいリクエストではありません";
        exit;
    }
    unset($_SESSION["csrf_token"]);

    $emp_id = $_POST["emp_id"];
    $new_password = $_POST["password"];

    try{
        $db = db_connect();
        $db->beginTransaction();

        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $update_data = [
            "password" => $password_hash,
            "create_id" => $_SESSION["connect_user"]["emp_id"]
        ];

        db_update($db, "employee", "emp_id = " . intval($emp_id), $update_data);
        
        $db->commit();
        $db = null;

        header("Location: " . WEB_ROOT . "employee_Detail.php?emp_id=" . $emp_id);
        exit;

    }catch(Exception $e){
        if(isset($db)) $db->rollBack();
        echo "エラー: " . $e->getMessage();
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
    <title>パスワード変更画面</title>
    <link rel="stylesheet" href="./css/safe_Edit.css"> 
</head>
<body>
    <header>
        <h3>パスワード変更</h3>
        <button type="button" onclick="location.href='./admin_menu.php'">管理者用画面へ</button>
        <button type="button" id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>
        <form action="./employee_Password_Reset.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token ?? ''); ?>">
            <input type="hidden" name="emp_id" value="<?php echo h($employee["emp_id"]); ?>">

            <div class="card">
                <label>対象社員ID：</label><br>
                <input type="text" value="<?php echo h($employee["emp_id"]); ?>" readonly disabled>
            </div>

            <div class="card">
                <label>対象社員名：</label><br>
                <input type="text" value="<?php echo h($employee["ename"]); ?>" readonly disabled>
            </div>

            <div class="card">
                <label>新しいパスワード：</label><br>
                <input type="password" name="password" required>
            </div>

            <div class="action">
                <button type="submit">変更を保存</button>
                <button type="button" onclick="location.href='./employee_Detail.php?emp_id=<?php echo h($employee["emp_id"]); ?>'">戻る</button>
            </div>
        </form>
    </main>
</body>
</html>