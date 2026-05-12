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

//名前、部署、安否、報告日時
if($_SERVER["REQUEST_METHOD"] === "GET"){
    $csrf_token = csrf_token_generate();

    try{
        $db = db_connect();
        $sql = "SELECT s.responce_id, e.ename, d.dname, s.safe, e.update_at FROM employee as e
        JOIN safety as s ON e.emp_id = s.emp_id
        JOIN department as d ON d.d_id = e.d_id
        WHERE s.isdelete = 0";
        $stmt = db_query($db,$sql);
        $checkbox_value = 1;
        $safety_table = [];
        while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
            $safety_columns = "<tr>";
            foreach($row as $key => $value){
                if($key === "responce_id"){
                    $safety_columns .= "<td><input type='checkbox' class='checks' name='delete_id[]' value='" . h($value) . "'></td>";
                    //ここのvalueとは安否情報のresponse_id
                }else if($key === "safe"){
                if($value == 0){
                    $safety_columns .= "<td class='ok'>無事</td>";
                }else if($value == 1) {
                    $safety_columns .= "<td class='injury'>軽傷</td>";
                }else if($value == 2) {
                    $safety_columns .= "<td class='danger'>重傷</td>";
                }
                //なおclassは安否一覧からコピペしたなごり、後でCSSにも使える
            }else{
                    $safety_columns .= "<td>" . h($value) . "</td>";
                }
            }
            $safety_columns .= "</tr>";
            $safety_table[] = $safety_columns;
        }

    }catch(Exection $e){
        echo $e;
    }
}else if($_SERVER["REQUEST_METHOD"] === "POST"){
    if(!isset($_POST["csrf_token"]) || $_POST       ["csrf_token"] !== $_SESSION["csrf_token"]){
        echo "正しいリクエストではありません";
        exit;
    }
    unset($_SESSION["csrf_token"]);
    if(!$user_id_admin){
    header("Location: " . WEB_ROOT . "logout.php");
    exit;
    }
    //二重なのは知ってるけど念のためね
    if(!isset($_POST["delete_id"]) || !is_array($_POST["delete_id"])){
        echo "安否情報が選択されていません";
        header("Location: " . WEB_ROOT . "safe_Delete.php");
        exit;
    }
    try{
        $db = db_connect();
        $db->beginTransaction();

        $delete_ids = $_POST["delete_id"];
        $placeholders = rtrim(str_repeat("?,", count($delete_ids)), ",");
        $sql = "UPDATE safety SET isDelete = 1 WHERE responce_id IN ($placeholders)";
        db_query($db, $sql, $delete_ids);
        $db->commit();
        header("Location: " . WEB_ROOT . "safe_Delete.php");
        exit;
    }catch(Exception $e){
            $db->rollBack();
        echo "エラー: " . $e->getMessage();
        exit;
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
    <title>安否情報削除画面</title>

</head>
<body>
  <!-- 戻るボタン -->
  <button type ="button" onclick="location.href='./admin_menu.php'">管理者用画面に戻る</button>
  <button type="button" onclick="location.href='./safe_List.php'">安否一覧画面へ</button>

  <!-- ログアウトボタン -->
  <button type="button" onclick="location.href='./logout.php'">ログアウト</button>

    <h1>安否情報削除画面</h1>

    <!-- 全選択・全解除 -->
    <p>
      <label><input type="checkbox" id="checkAll">全て選択/解除</label>
    </p>

    <table border="1">
    <thead>
        <tr>
        <th>選択</th>
        <th>名前</th>
        <th>所属部署</th>
        <th>安否</th>
        <th>報告日時</th>
        </tr>
    </thead>
    <form action="./safe_Delete.php" method="post" id="deleteForm">
        <button type="button" id="deleteBtn">削除を実行</button>
        <dialog id="confirmDialog">
            <p>選択した安否情報を削除しますか?</p>
            <button type="button" id="cancel">キャンセル</button>
            <button type="button" id="ok">実行</button>
        </dialog>
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token ?? ''); ?>">
    <tbody>
        <?php echo implode($safety_table); ?>
    </tbody>
    </form>


  </table>
  </form>
  <script src="./js/safe_Delete.js"></script>
</body>
</html>