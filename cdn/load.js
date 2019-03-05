var domain = window.location.hostname;
function cc() {
	var enabled = localStorage.getItem('enabled');
	if (enabled === null) {
		localStorage.setItem('enabled', 'true');
	} else {
		localStorage.removeItem('enabled');
	}
	location.reload(true);
}
function welcome() {
	console.log('Welcome to console.chat! You\'re chatting with other people who visited ' + domain + ' and opened their browser\'s console! Type help() and hit enter to learn more.');
	console.log('Created by Dalton Edwards :) Source Code: https://github.com/DaltonWebDev/console.chat / Follow Me: https://twitter.com/DaltonEdwards');
}
function help() {
	console.log('HELP >\nSet Username: Type username("your username") and hit enter.\nSend Message: Type send("your message") and hit enter. You can also include a second parameter to change text color like this: send("your message", "254cf5") - enter any valid hex code without the # sign.\n\nDid you know you can also do send`your message`?');
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
						console.log('%c ' + data.messages[i].message, 'color: #' + data.messages[i].color);
  					//console.log(data.messages[i].message);
  					messageCount++;
  				}
    		}
  	});
}
function send(message, color = '000000') {
	var usernameLS = localStorage.getItem('username');
	var myRequest = new Request('https://console.chat/api/send.php');
	if (usernameLS === null) {
		var outputtedMessage = message;
	} else {
		var outputtedMessage = usernameLS + ': ' + message;
	}
	var data = {"domain": domain, "message": outputtedMessage, "color": color};
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
  		loadMessages();
			var enabled = localStorage.getItem('enabled');
			if (enabled === null) {
				localStorage.setItem('enabled', 'true');
				location.reload(true);
			}
  	}
  });
}
var enabled = localStorage.getItem('enabled');
if (enabled === null) {
	console.log(`Want to chat with other people who are browsing on ${domain}? Awesome! Type cc() and hit enter to enable console.chat / Send cc() again at any time to stop loading in messages.`);
} else {
	loadMessages();
	setInterval(loadMessages, 5000);
	setTimeout(welcome, 2000);
}
