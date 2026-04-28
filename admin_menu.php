<?php
require_once __DIR__ . "/def.php";
require_once __DIR__ . "/db.php";
session_start();
def_session_check();

$ename = $_SESSION["connect_user"]["ename"];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/管理者用 メニュー画面.css">
  <title>管理者用 メニュー画面</title>
</head>

<body>

<header>
  <h1>管理者用 メニュー画面</h1>
  <button class="logout-btn" ><a href="./logout.php">Logout</a></button>
</header>

<div class="container">

  <div class="top-row">
    <h2>操作を選択</h2>
    <span class="admin-name">ログイン中：<?php echo htmlspecialchars($ename); ?></span>
  </div>

  <div class="menu">
    <a href="./safe_Regist.php">
      <div class="menu-item">安否登録画面へ</div>
    </a>

    <a href="./safe_List.php">
      <div class="menu-item">安否一覧画面へ</div>
    </a>

    <a href="C.html">
      <div class="menu-item">社員一覧画面へ</div>
    </a>

    <a href="D.html">
      <div class="menu-item">安否情報削除画面へ</div>
    </a>
  </div>

</div>

</body>
</html>