<?php
require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "BaseConfig.php";
$site->PageID = "Logout";
$admin->Logout();
?>

<h1>Logged out</h1>
<a href="/login.php">login again</a>
