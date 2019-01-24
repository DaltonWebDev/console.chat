<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$username = !empty($_GET["username"]) ? strtolower(strip_tags($_GET["username"])) : false;
$time = time();
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$identifier = hash("sha256", $ip);
if (file_exists("last-sent/$identifier.json")) {
	$lastSentJson = json_decode(file_get_contents("last-sent/$identifier.json"), true);
	$lastSentTime = $lastSentJson["time"];
} else {
	$lastSentTime = false;
}
if ($username === false) {
	$error = "Please enter a username";
} else if (!ctype_alnum($username)) {
	$error = "Usernames can only contain alphanumeric characters.";
} else if (strlen($username) > 20) {
	$error = "Usernames can't exceed 20 characters in length.";
} else if (file_exists("identifiers/$identifier.json")) {
	$error = "You already set a username!";
} else if (file_exists("usernames/$username.json")) {
	$error = "This username already exists!";
} else {
	$usernameArray[] = [
		"identifier" => $identifier,
		"time" => $time
	];
	$directories[] = "usernames";
	$directories[] = "identifiers";
	foreach ($directories as $directory) {
		if (!file_exists($directory)) {
    		mkdir($directory, 0777, true);
		}
	}
	file_put_contents("usernames/$username.json", json_encode($usernameArray));
	$identifierArray = [
		"username" => $username
	];
	file_put_contents("identifiers/$identifier.json", json_encode($identifierArray));
	$error = false;
}
$outputArray = [
	"error" => $error
];
echo json_encode($outputArray);
?>