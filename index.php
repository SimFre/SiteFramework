<?php
require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "BaseConfig.php";
$site->PageID = "Index";
$site->Template = "Basic.html";
$admin->RequireLogin();
?>

<h2>{|WELCOME|}</h2>
<p>
 This is a test page using Site Framwork
</p>

