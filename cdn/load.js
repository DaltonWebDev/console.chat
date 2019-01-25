var username = false;
var domain = window.location.hostname;
function welcome() { 
	console.log('Welcome to console.chat! You\'re chatting with other people who visited ' + domain + ' and opened their browser\'s console! Type help() and hit enter to learn more.');
	console.log('To send a message it must be in this format: send(\'your message here\')\nExample: If you want to say "Hey, what\'s up?" type send(\'Hey, what\'s up?\') and hit enter.');
	console.log('Created by Dalton Edwards :) Source Code: https://github.com/DaltonWebDev/console.chat / Follow Me: https://twitter.com/DaltonEdwards');
	console.log('Watch out for impersonators! Anybody can easily set their username to anything this is just a fun place to chat.');
}
function help() {
	console.log("HELP >\nSet Username: Type var username = 'your username' and hit enter.\nSend Message: Type send('your message') and hit enter.\n\nDid you know you can also do send`your message`?");
}
var messageCount = 0;
function loadMessages() {
	var request = new XMLHttpRequest();
	request.open('POST', 'https://console.chat/api/read.php', true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
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
	request.send('domain=' + domain);
}
function send(message) {
	var request = new XMLHttpRequest();
	request.open('POST', 'https://console.chat/api/send.php', true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			var json = JSON.parse(request.responseText);
			if (json.error !== false) {
				console.error(json.error);
			} else {
				// success
				console.log('Message sent!');
			}
  		} else {
    		// We reached our target server, but it returned an error
    		console.log('The server is returning an error! Please try again later.');
  		}
	};
	request.onerror = function() {
		// There was a connection error of some sort
		console.log('Encountered a connection problem.');
	};
	if (username === false) {
		request.send('domain=' + domain + '&message=' + message);
	} else {
		request.send('domain=' + domain + '&message=' + username + ': ' + message);
	}
}
loadMessages();
// say welcome after 5 seconds
setTimeout(welcome, 5000);
// load in new messages once every second
setInterval(loadMessages, 1000);