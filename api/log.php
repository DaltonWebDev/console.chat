<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$domain = !empty($_REQUEST["domain"]) ? strtolower($_REQUEST["domain"]) : false;
$messages = false;
if ($domain === false) {
	$error = "DOMAIN_MISSING";
} else if (!file_exists("messages/$domain/log.json")) {
	$error = "NOT_FOUND";
} else {
	$messages = json_decode(file_get_contents("messages/$domain/log.json"), true);
	$error = false;
}
$outputArray = [
	"error" => $error,
	"messages" => $messages
];
echo json_encode($outputArray);
?>