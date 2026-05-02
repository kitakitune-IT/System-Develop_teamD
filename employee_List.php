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

    // 検索フォーム用のマスタデータ取得
    $sql_dep = "SELECT d_id, dname FROM department";
    $stmt_dep = db_query($db, $sql_dep);
    $department_List = $stmt_dep -> fetchAll(PDO::FETCH_ASSOC);

    $sql_post = "SELECT p_id, pname FROM post";
    $stmt_post = db_query($db, $sql_post);
    $post_List = $stmt_post -> fetchAll(PDO::FETCH_ASSOC);

    // 社員一覧のベースSQL
    $sql = "SELECT e.emp_id, e.ename, e.birth, e.tel, d.dname, p.pname, 
    CASE WHEN e.administrator = 1 THEN '管理者'
    ELSE '一般'
    END AS administrator,
    e.create_id, 
    DATE_FORMAT(e.create_at, '%Y-%m-%d %H:%i:%s') as create_at, DATE_FORMAT(e.update_at, '%Y-%m-%d %H:%i:%s') as update_at FROM employee as e
    JOIN department as d ON d.d_id = e.d_id
    JOIN post as p ON p.p_id = e.p_id";
    
    // 検索条件の構築
    $where_clauses = [];
    $params = [];

    if (isset($_GET['emp_id']) && $_GET['emp_id'] !== '') {
        $where_clauses[] = "e.emp_id = :emp_id";
        $params[':emp_id'] = $_GET['emp_id'];
    }
    if (isset($_GET['ename']) && $_GET['ename'] !== '') {
        $where_clauses[] = "e.ename LIKE :ename";
        $params[':ename'] = "%" . $_GET['ename'] . "%";
    }
    if (isset($_GET['d_id']) && $_GET['d_id'] !== '') {
        $where_clauses[] = "e.d_id = :d_id";
        $params[':d_id'] = $_GET['d_id'];
    }
    if (isset($_GET['p_id']) && $_GET['p_id'] !== '') {
        $where_clauses[] = "e.p_id = :p_id";
        $params[':p_id'] = $_GET['p_id'];
    }
    if (isset($_GET['administrator']) && $_GET['administrator'] !== '') {
        $where_clauses[] = "e.administrator = :administrator";
        $params[':administrator'] = $_GET['administrator'];
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY e.emp_id ASC";

    $stmt = db_query($db, $sql, $params);
    $emp_List = [];

    while($emp_fetch_result = $stmt -> fetch(PDO::FETCH_ASSOC)){
        $emp_data = "<tr>";
        $emp_id = $emp_fetch_result["emp_id"];
        foreach($emp_fetch_result as $column => $value){
            if($column == "ename"){
              $emp_data .= "<td><a href='employee_Detail.php?emp_id=" . h($emp_id) . "'>" . h($value) . "</a></td>";
            }else{
              $emp_data .= "<td>" . h($value) . "</td>";
            }
        }
        $emp_data .= "</tr>";
        $emp_List[] = $emp_data; 
    }

}catch(Exception $e){
    echo "エラー".$e->getMessage();
    exit;
}finally{
    $stmt_dep = null;
    $stmt_post = null;
    $stmt = null;
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
    <?php if($user_id_admin) :?>
      <button class="btn" onclick="location.href='./admin_menu.php'">
        管理者用画面へ
      </button>
    <?php endif ;?>
    <button class="btn" onclick="location.href='./employee_Regist.php'">新規社員登録</button>
    <button class="btn logout" onclick="location.href='./logout.php'">LOG OUT</button>
  </div>
</header>

<section class="filters">
  <form action="./employee_List.php" method="GET">
    <label>社員ID:
      <input type="number" name="emp_id" value="<?php echo isset($_GET['emp_id']) ? h($_GET['emp_id']) : ''; ?>">
    </label>
    <label>名前:
      <input type="text" name="ename" value="<?php echo isset($_GET['ename']) ? h($_GET['ename']) : ''; ?>">
    </label>
    <label>部署:
      <select name="d_id">
        <option value="">すべて</option>
        <?php foreach($department_List as $dep): ?>
          <option value="<?php echo h($dep['d_id']); ?>" <?php if(isset($_GET['d_id']) && $_GET['d_id'] === (string)$dep['d_id']) echo 'selected'; ?>><?php echo h($dep['dname']); ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>役職:
      <select name="p_id">
        <option value="">すべて</option>
        <?php foreach($post_List as $post): ?>
          <option value="<?php echo h($post['p_id']); ?>" <?php if(isset($_GET['p_id']) && $_GET['p_id'] === (string)$post['p_id']) echo 'selected'; ?>><?php echo h($post['pname']); ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>管理者権限:
      <select name="administrator">
        <option value="">すべて</option>
        <option value="0" <?php if(isset($_GET['administrator']) && $_GET['administrator'] === '0') echo 'selected'; ?>>一般</option>
        <option value="1" <?php if(isset($_GET['administrator']) && $_GET['administrator'] === '1') echo 'selected'; ?>>管理者</option>
      </select>
    </label>
    <button type="submit">絞り込み</button>
    <button type="button" onclick="location.href='./employee_List.php'">クリア</button>
  </form>
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