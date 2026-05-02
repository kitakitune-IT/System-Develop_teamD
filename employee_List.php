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

try{
    $db = db_connect();
    $sql = "SELECT e.emp_id, e.ename, e.birth, e.tel, d.dname, p.pname, 
    CASE WHEN e.administrator = 1 THEN '管理者'
    ELSE '一般'
    END AS administrator,
    e.create_id, 
    DATE_FORMAT(e.create_at, '%Y-%m-%d %H:%i:%s') as create_at, DATE_FORMAT(e.update_at, '%Y-%m-%d %H:%i:%s') as update_at FROM employee as e
    JOIN department as d ON d.d_id = e.d_id
    JOIN post as p ON p.p_id = e.p_id";
    $stmt = db_query($db, $sql);
    $emp_List = [];

    while($emp_fetch_result = $stmt -> fetch(PDO::FETCH_ASSOC)){
        $emp_data = "<tr>";
        $emp_id = $emp_fetch_result["emp_id"];
        foreach($emp_fetch_result as $column => $value){
            if($column == "ename"){
              $emp_data .= "<td><a href='employee_Datail.php?emp_id=" . $emp_id . "'>" . h($value) . "</a></td>";
            }else{
              $emp_data .= "<td>" . h($value) . "</td>";
            }
        }
        $emp_data .= "</tr>";
        $emp_List[] = $emp_data; 

    }
    // $sql = "SELECT d_id, dname FROM department";
    // $stmt = db_query($db, $sql);
    // $department_fetch_result = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    // $sql = "SELECT p_id, pname FROM post";
    // $stmt = db_query($db, $sql);
    // $post_fetch_result = $stmt -> fetchAll(PDO::FETCH_ASSOC);






}catch(Exception $e){
    echo "エラー".$e->getMessage();
    exit;
}finally{
    $db = null;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="./css/社員一覧画面.css">
    <title>社員一覧画面</title>
</head>
<body>

<header>
  <h1>社員一覧画面</h1>
  <div class="top-buttons">
    <button class="btn">新規社員登録</button>
    <button class="btn logout">LOG OUT</button>
  </div>
</header>

<section class="filters">

  <label>名前:
    <select>
      <option>A</option>
      <option>B</option>
    </select>
  </label>
</section>

<table>
  <thead>
    <tr>
      <th>社員ID</th>
      <th>名前</th>
      <th>生年月日</th>
      <th>電話番号</th>
      <th>部署</th>
      <th>役職</th>
      <th>管理者権限</th>
      <th>作成ユーザー</th>
      <th>登録日時</th>
      <th>更新日時</th>
    </tr>
  </thead>
  <tbody>
    <?php echo implode($emp_List) ?>
  </tbody>
</table>

</body>
</html>

