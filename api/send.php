<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$domain = !empty($_GET["domain"]) ? strtolower(strip_tags($_GET["domain"])) : false;
$message = !empty($_GET["message"]) ? strip_tags($_GET["message"]) : false;
$time = time();
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
if (file_exists("last-sent/$ip.json")) {
	$lastSentJson = json_decode(file_get_contents("last-sent/$ip.json"), true);
	$lastSentTime = $lastSentJson["time"];
} else {
	$lastSentTime = false;
}
if ($domain === false) {
	$error = "Please enter a domain";
} else if (!filter_var(gethostbyname($domain), FILTER_VALIDATE_IP)) {
	$error = "Invalid domain name";
} else if ($message === false) {
	$error = "Please enter a message";
} else if ($time < $lastSentTime + 5) {
	$error = "Please slow down! You can only send a message once every 5 seconds.";
} else if (strlen($message) > 1000) {
	$error = "Message can't exceed 1,000 characters";
} else {
	$messageContents = file_get_contents("messages/$domain.json");
	if ($messageContents === false) {
		$messageArray = [];
	} else {
		$messageArray = json_decode($messageContents, true);
	}
	$messageArray[] = [
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
	file_put_contents("last-sent/$ip.json", json_encode($lastSentArray));
	$error = false;
}
$outputArray = [
	"error" => $error
];
echo json_encode($outputArray);
?>