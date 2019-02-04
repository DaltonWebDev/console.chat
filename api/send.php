<?php
session_start();
function autoBanned($string) {
	$string = str_replace(" ", "", $string);
	$badWords = array(
		"nigger",
        "fag",
        "faggot"
    );
    $matches = array();
    $matchFound = preg_match_all(
        "/(" . implode($badWords,"|") . ")/i", 
        $string, 
        $matches
    );
    if ($matchFound) {
    	return true;
    } else {
    	return false;
    }
}
function ban($identifier) {
	$banArray = [
		"time" => time()
	];
	file_put_contents("bans/$identifier.json", json_encode($banArray));
	$_SESSION["banned"] = true;
}
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
$domain = !empty($_REQUEST["domain"]) ? strtolower(strip_tags($_REQUEST["domain"])) : false;
$message = !empty($_REQUEST["message"]) ? strip_tags($_REQUEST["message"]) : false;
$time = time();
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$identifier = hash("sha256", $ip);
if (file_exists("last-sent/$identifier.json")) {
	$lastSentJson = json_decode(file_get_contents("last-sent/$identifier.json"), true);
	$lastSentTime = $lastSentJson["time"];
} else {
	$lastSentTime = false;
}
// maybe they cleared browser data
if (!isset($_SESSION["banned"])) {
	if (file_exists("bans/$identifier.json")) {
		$_SESSION["banned"] = true;
	}
}
if (file_exists("bans/$identifier.json")) {
	$error = "You have been banned!";
} else if ($_SESSION["banned"] === true) {
	$error = "Ban evasion detected!";
	ban($identifier);
} else if ($domain === false) {
	$error = "Please enter a domain";
} else if (!filter_var(gethostbyname($domain), FILTER_VALIDATE_IP)) {
	$error = "Invalid domain name";
} else if ($message === false) {
	$error = "Please enter a message";
} else if ($time < $lastSentTime + 5 || isset($_SESSION["last-sent"]) && $_SESSION["last-time"] < 5) {
	$error = "Please slow down! You can only send a message once every 5 seconds.";
} else if (strlen($message) > 500) {
	$error = "Message can't exceed 500 characters";
} else if (autoBanned($message)) {
	$error = "You were automatically banned!";
	ban($identifier);
} else {
	$messageContents = file_get_contents("messages/$domain.json");
	if ($messageContents === false) {
		$messageArray = [];
	} else {
		$messageArray = json_decode($messageContents, true);
	}
	$check = json_decode(file_get_contents("https://www.purgomalum.com/service/json?add=porn&text=" . urlencode($message)), true);
	$messageFiltered = $check["result"];
	$messageArray[] = [
		"identifier" => $identifier,
		"message" => $messageFiltered,
		"time" => $time
	];
	$logArray[] = [
		"identifier" => $identifier,
		"message" => $messageFiltered,
		"time" => $time
	];
	$directories[] = "messages";
	$directories[] = "last-sent";
	$directories[] = "identifiers";
	$directories[] = "bans";
	foreach ($directories as $directory) {
		if (!file_exists($directory)) {
    		mkdir($directory, 0777, true);
		}
	}
	file_put_contents("messages/$domain.json", json_encode($messageArray));
	$lastSentArray = [
		"time" => $time
	];
	file_put_contents("last-sent/$identifier.json", json_encode($lastSentArray));
	$_SESSION["last-sent"] = $time;
	$identifierArray = [
		"ip" => $ip
	];
	file_put_contents("identifiers/$identifier.json", json_encode($identifierArray));
	$error = false;
}
$outputArray = [
	"error" => $error
];
echo json_encode($outputArray);
?>