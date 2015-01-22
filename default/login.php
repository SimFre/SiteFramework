<?php
require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "BaseConfig.php";
$site->PageID = "Login";
?>

<form action="/" method="post">

 <div>
  Username: <input type="text" name="login_username" /><br />
  Password: <input type="password" name="login_password" />
  <label><input type="checkbox" name="login_remember" value="1" /> Remember me</label>
  <input type="submit" class="small-3 small-centered columns" />
 </div>

 <? if ($site->Request("reason")): ?>
  <p>
   <?= $site->Request("reason") ?>
  </p>
 <? endif; ?>

</form>

