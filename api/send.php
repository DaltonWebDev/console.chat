<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$domain = !empty($_REQUEST["domain"]) ? strtolower(strip_tags($_REQUEST["domain"])) : false;
$message = !empty($_REQUEST["message"]) ? strip_tags($_REQUEST["message"]) : false;
$blacklistedWords = explode("\n", file_get_contents("etc/blacklisted-words.txt"));
$matches = array();
$matchFound = preg_match_all(
	"/\b(" . implode($blacklistedWords,"|") . ")\b/i", 
	$message, 
	$matches
);
$time = time();
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$identifier = hash("sha256", $ip);
if (file_exists("last-sent/$identifier.json")) {
	$lastSentJson = json_decode(file_get_contents("last-sent/$identifier.json"), true);
	$lastSentTime = $lastSentJson["time"];
} else {
	$lastSentTime = false;
}
$bannedIdentifiers = explode("\n", json_decode(file_get_contents("etc/banned-users.txt"), true));
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
} else if (strlen($message) > 500) {
	$error = "Message can't exceed 500 characters";
} else if ($matchFound) {
	$error = "Your message was blocked because my automated filters detected blacklisted word(s). If this was an error I apologize! Keep in mind this feature is for the greater good.";
} else {
	$messageContents = file_get_contents("messages/$domain.json");
	if ($messageContents === false) {
		$messageArray = [];
	} else {
		$messageArray = json_decode($messageContents, true);
	}
	$messageArray[] = [
		"identifier" => $identifier,
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