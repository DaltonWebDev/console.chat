var domain = window.location.hostname;
console.log('Welcome to console.chat! You\'re chatting with other people who visited ' + domain + ' and opened their browser\'s console!');
console.log('To send a message it must be in this format: send(\'your message here\')\nExample: If you want to say "Hey, what\'s up?" send(\'Hey, what\'s up?\')');
console.log('Created by @DaltonEdwards :)');
var messageCount = 0;
function loadMessages() {
	var request = new XMLHttpRequest();
	request.open('GET', 'https://console.chat/api/read.php?domain=' + domain, true);
	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			// Success!
			var json = JSON.parse(request.responseText);
    		for (i in json.messages) {
    			if (messageCount === 0 || i > messageCount) {
					console.log(json.messages[i].message);
					messageCount++;
				}
			}
  		} else {
    		console.error('Target server returned an error');
  		}
	};
	request.onerror = function() {
		console.error('Connection problem');
	};
	request.send();
}
function send(message) {
	var request = new XMLHttpRequest();
	request.open('GET', 'https://console.chat/api/send.php?domain=' + domain + '&message=' + message, true);
	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			var json = JSON.parse(request.responseText);
			if (json.error !== false) {
				console.error(json.error);
			} else {
				return true;
			}
  		} else {
    		// We reached our target server, but it returned an error
    		return false;
  		}
	};
	request.onerror = function() {
		// There was a connection error of some sort
		return false;
	};
	request.send();
}
loadMessages();
// load in new messages once every second
setInterval(loadMessages, 1000);