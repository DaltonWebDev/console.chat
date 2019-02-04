var domain = window.location.hostname;
function welcome() { 
	console.log('Welcome to console.chat! You\'re chatting with other people who visited ' + domain + ' and opened their browser\'s console! Type help() and hit enter to learn more.');
	console.log('To send a message it must be in this format: send("your message here")\nExample: If you want to say Hey everybody! type send("Hey everybody!") and hit enter.');
	console.log('Created by Dalton Edwards :) Source Code: https://github.com/DaltonWebDev/console.chat / Follow Me: https://twitter.com/DaltonEdwards');
}
function help() {
	console.log('HELP >\nSet Username: Type username("your username") and hit enter.\nSend Message: Type send("your message") and hit enter.\n\nDid you know you can also do send`your message`?');
}
function username(x) {
	localStorage.setItem('username', x);
	console.log('Username set to: ' + x + '!');
}
var messageCount = 0;
function loadMessages() {
	var myRequest = new Request('https://console.chat/api/read.php?domain=' + domain);
	fetch(myRequest)
  		.then(function(response) { return response.json(); })
  		.then(function(data) {
  			for (i in data.messages) {
  				if (messageCount === 0 || i > messageCount) {
  					console.log(data.messages[i].message);
  					messageCount++;
  				}
    		}
  	});
}
function send(message) {
	var usernameLS = localStorage.getItem('username');
	var myRequest = new Request('https://console.chat/api/send.php');
	if (usernameLS === null) {
		var outputtedMessage = message;
	} else {
		var outputtedMessage = usernameLS + ': ' + message;
	}
	var data = {"domain": domain, "message": outputtedMessage};
	var formData  = new FormData();
  	for (var name in data) {
    	formData.append(name, data[name]);
 	}
	fetch(myRequest, {
		method: 'POST', 
		body: formData
	})
  	.then(function(response) { return response.json(); })
  	.then(function(data) {
  		if (data.error !== false) {
  			console.error(data.error);
  		} else {
  			console.log('Message sent!');
  		}
  	});
}
loadMessages();
setInterval(loadMessages, 1000);
setTimeout(welcome, 5000);