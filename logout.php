<?php
require_once __DIR__ . "/root.php";
session_start();
session_destroy();
header("Location: " . WEB_ROOT . "index.php");

?>