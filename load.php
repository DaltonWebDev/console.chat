<?php
header("Content-Type: application/javascript; charset=utf-8");
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
$js = file_get_contents("cdn/load.js");
echo $js;
?>
