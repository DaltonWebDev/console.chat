<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$domain = !empty($_GET["domain"]) ? strtolower(strip_tags($_GET["domain"])) : false;
$message = !empty($_GET["message"]) ? strip_tags($_GET["message"]) : false;
$time = time();
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$identifier = hash("sha256", $ip);
if (file_exists("last-sent/$identifier.json")) {
	$lastSentJson = json_decode(file_get_contents("last-sent/$identifier.json"), true);
	$lastSentTime = $lastSentJson["time"];
} else {
	$lastSentTime = false;
}
$bannedIdentifiers = [
	"6b89624d55c5babed7e2162010c620542547c00fd7251ca30ef35da623acf72a",
	"f191624533bd5ec2b3295a2e99e79f15a4457bcf0200d0ca6a60dfb4390d1468"
];
if (in_array($identifier, $bannedIdentifiers)) {
	$error = "You have been banned!";
} else if ($domain === false) {
	$error = "Please enter a domain";
} else if (!filter_var(gethostbyname($domain), FILTER_VALIDATE_IP)) {
	$error = "Invalid domain name";
} else if ($message === false) {
	$error = "Please enter a message";
} else if ($time < $lastSentTime + 10) {
	$error = "Please slow down! You can only send a message once every 10 seconds.";
} else if (strlen($message) > 200) {
	$error = "Message can't exceed 200 characters";
} else {
	$messageContents = file_get_contents("messages/$domain.json");
	if ($messageContents === false) {
		$messageArray = [];
	} else {
		$messageArray = json_decode($messageContents, true);
	}
	if (file_exists("identifiers/$identifier.json")) {
		$identifierJson = json_decode(file_get_contents("identifiers/$identifier.json"), true);
		$identifierToUsername = $identifierJson["username"];
	} else {
		$identifierToUsername = false;
	}
	$messageArray[] = [
		"identifier" => $identifier,
		"username" => $identifierToUsername,
		"message" => $message,
		"time" => $time
	];
	$directories[] = "messages";
	$directories[] = "last-sent";
	foreach ($directories as $directory) {
		if (!file_exists($directory)) {
    		mkdir($directory, 0777, true);
		}
	}
	file_put_contents("messages/$domain.json", json_encode($messageArray));
	// update last sent
	$lastSentArray = [
		"time" => $time
	];
	file_put_contents("last-sent/$identifier.json", json_encode($lastSentArray));
	$error = false;
}
$outputArray = [
	"error" => $error
];
echo json_encode($outputArray);
?>