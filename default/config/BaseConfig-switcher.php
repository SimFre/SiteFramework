<?php
// Logic here to select config depending on environment.

define("COMPUTERNAME", getenv("SERVER_NAME"));
if (COMPUTERNAME == "devServer1") {
   require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "libs" . DIRECTORY_SEPARATOR . "BaseConfig-dev.php";
}
else if (COMPUTERNAME == "testServer1") {
   require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "libs" . DIRECTORY_SEPARATOR . "BaseConfig-test.php";
}
else if (COMPUTERNAME == "liveServer1") {
   require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "libs" . DIRECTORY_SEPARATOR . "BaseConfig-live.php";
}
else {
   die("No suitable BaseConfig found. Device: " . COMPUTERNAME);
}

?>
