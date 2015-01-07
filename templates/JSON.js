<?php
header("Content-type: application/json; charset=utf-8'");

$content['data'] = json_decode($site->Body);
$content['debug']['session'] = $_SESSION;
$content['debug']['server'] = $_SERVER;
$content['debug']['post'] = $_POST;
$content['debug']['get'] = $_GET;
$content['debug']['cookie'] = $_COOKIE;

echo json_encode($content);
?>
