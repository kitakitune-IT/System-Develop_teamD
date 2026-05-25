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
        
        // 対象社員データの取得
        $sql = "SELECT * FROM employee WHERE emp_id = :emp_id";
        $stmt = db_query($db, $sql, [":emp_id" => $emp_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$employee){
            echo "データが存在しませんでした。";
            exit;
        }

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
        $stmt = null;
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

    $emp_id = $_POST["emp_id"];
    $tel = $_POST["tel"];

    try{
        $db = db_connect();

        // 電話番号の重複チェック（他の社員と同じ電話番号になっていないか）
        $sql_tel_check = "SELECT emp_id FROM employee WHERE tel = :tel AND emp_id != :emp_id";
        $stmt_tel_check = db_query($db, $sql_tel_check, [
            ":tel" => $tel,
            ":emp_id" => $emp_id
        ]);
        
        if($stmt_tel_check->fetch()){
            echo "入力された電話番号は既に他の社員に登録されています。";
            exit;
        }

        $db->beginTransaction();

        $update_data = [
            "ename" => $_POST["ename"],
            "tel" => $tel,
            "d_id" => $_POST["d_id"],
            "p_id" => $_POST["p_id"],
            "administrator" => $_POST["administrator"],
            "create_id" => $_SESSION["connect_user"]["emp_id"] // 最終編集者のIDを更新
        ];

        db_update($db,"employee", $update_data, "emp_id = :emp_id",  [":emp_id" => $emp_id]);
        
        $db->commit();

        $db = null;

        header("Location: " . WEB_ROOT . "employee_Detail.php?emp_id=" . $emp_id);
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
    <title>社員編集画面</title>
    <link rel="stylesheet" href="./css/safe_Edit.css"> 
</head>
<body>
    <header>
        <h3>社員情報編集</h3>
        <button type="button" onclick="location.href='./admin_menu.php'">管理者用画面へ</button>
        <button type="button" id="logout" onclick="location.href='./logout.php'">ログアウト</button>
    </header>
    <main>
        <form action="./employee_Edit.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token ?? ''); ?>">
            <input type="hidden" name="emp_id" value="<?php echo h($employee["emp_id"]); ?>">

            <div class="card">
                <label>社員ID：</label><br>
                <input type="text" value="<?php echo h($employee["emp_id"]); ?>" readonly disabled>
            </div>

            <div class="card">
                <label>社員名：</label><br>
                <input type="text" name="ename" value="<?php echo h($employee["ename"]); ?>" required>
            </div>

            <div class="card">
                <label>電話番号：</label><br>
                <input type="text" name="tel" value="<?php echo h($employee["tel"]); ?>" required>
            </div>

            <div class="card">
                <label>部署：</label><br>
                <select name="d_id">
                    <?php foreach($departments as $dep): ?>
                        <option value="<?php echo h($dep['d_id']); ?>" <?php if($employee['d_id'] == $dep['d_id']) echo 'selected'; ?>>
                            <?php echo h($dep['dname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="card">
                <label>役職：</label><br>
                <select name="p_id">
                    <?php foreach($posts as $post): ?>
                        <option value="<?php echo h($post['p_id']); ?>" <?php if($employee['p_id'] == $post['p_id']) echo 'selected'; ?>>
                            <?php echo h($post['pname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="card">
                <label>管理者権限：</label><br>
                <select name="administrator">
                    <option value="0" <?php if($employee['administrator'] == 0) echo 'selected'; ?>>一般</option>
                    <option value="1" <?php if($employee['administrator'] == 1) echo 'selected'; ?>>管理者</option>
                </select>
            </div>

            <div class="action">
                <button type="submit">保存</button>
                <button type="button" onclick="location.href='./employee_Detail.php?emp_id=<?php echo h($employee["emp_id"]); ?>'">戻る</button>
            </div>
        </form>
    </main>
</body>
</html>