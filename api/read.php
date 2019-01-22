<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$domain = !empty($_GET["domain"]) ? strtolower($_GET["domain"]) : false;
$messages = false;
if ($domain === false) {
	$error = "DOMAIN_MISSING";
} else if (!file_exists("messages/$domain.json")) {
	$error = "NOT_FOUND";
} else {
	$messages = json_decode(file_get_contents("messages/$domain.json"), true);
	$error = false;
}
$outputArray = [
	"error" => $error,
	"messages" => $messages
];
echo json_encode($outputArray);
?>