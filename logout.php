<?php
require_once __DIR__ . "/def.php";
session_start();
session_destroy();
header("Location: " . WEB_ROOT . "index.php");

?>