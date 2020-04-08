/**
  * @module mod_pastel/pastel_scripts
  */

  define(['jquery'], function($) {

  	var wsUri = "ws://la-pastel.univ-lemans.fr:8000/";
  	var output ;

  	var user_id;
  	var course_id;
  	var activity_id;

  	return {
  		init: function() {

	        // Put whatever you like here. $ is available
	        // to you as normal.;
	        // testWebSocket();
	        // testWebSocket();
	    }
	};

	// function testWebSocket()
	// {
	// 	websocket = new WebSocket(wsUri);
	// 	websocket.onopen = function(evt) { onOpen(evt) };
	// 	websocket.onclose = function(evt) { onClose(evt) };
	// 	websocket.onmessage = function(evt) { onMessage(evt) };
	// 	websocket.onerror = function(evt) { onError(evt) };
	// }

	// // function onOpen(evt)
	// // {
	// // 	writeToScreen("CONNECTED");
	// // 	authentifie(5, "etudiant", 14, 260);
	// // }

	// function onClose(evt)
	// {
	// 	writeToScreen("DISCONNECTED");
	// }

	// function onMessage(evt)
	// {
	// 	var message = JSON.parse(evt.data);
	// 	switch (message.action) {
	// 		case "transcription" : 
	// 		transcription(message.params);
	// 		break;
	// 		case "page" :
	// 		page(message.params);
	// 		break;
	// 		case "ressource" :
	// 		ressource(message.params);
	// 		break;
	// 		case "indicator" :
	// 		indicator(message.params);
	// 		break;
	// 		default :
	// 		console.log(message);
	// 		break;
	// 	}
 //    //writeToScreen('<span style="color: blue;">RESPONSE: ' + evt.data+'</span>');
	// }

	// // function transcription(message) {
	// // 	output = document.getElementById("transcription"); // last wrapper
	// // 	cible = querySelectorAll(".blocTranscript:last-child");
	// // 	console.log("should print");

	// // 	var pre = document.createElement("p");
	// // 	pre.style.wordWrap = "break-word";
	// // 	pre.innerHTML = message.text;
	// // 	cible.appendChild(pre);
	// // }

	// // APPELEE A CHAQUE CHANGEMENT DE SLIDE
	// // function page(message) {
	// // 	output = document.getElementById("page");
		
	// // 	var pre = document.createElement("p");
	// // 	pre.style.wordWrap = "break-word";
	// // 	// avant : message.page;
	// // 	//pre.innerHTML = "appel_AMD";
	// // 	//output.appendChild(pre);
	// // }

	
	// // function ressource(message) {
	// // 	output = document.getElementById("ressource");
		
	// // 	var pre = document.createElement("p");
	// // 	pre.style.wordWrap = "break-word";
	// // 	pre.innerHTML = message.url;
	// // 	output.appendChild(pre);
	// // }

	// // function indicator(message) {
	// // 	output = document.getElementById("info");
	// // 	var pre = document.createElement("p");
	// // 	pre.style.wordWrap = "break-word";
	// // 	pre.innerHTML = message;
	// // 	output.appendChild(pre);
	// // }

	// function onError(evt)
	// {
	// 	writeToScreen('<span style="color: red;">ERROR:</span> ' + evt.data);
	// }

	// function doSend(message)
	// {
	// 	writeToScreen("SENT: " + message);
	// 	websocket.send(message);
	// }

	// function writeToScreen(message)
	// {
	// 	output = document.getElementById("info");
	// 	var pre = document.createElement("p");
	// 	pre.style.wordWrap = "break-word";
	// 	pre.innerHTML = message;
	// 	output.appendChild(pre);
	// }

	// function authentifie(userid, role, course, activity)
	// {
	// 	user_id= userid;
	// 	course_id = course;
	// 	activity_id = activity;
	// 	var data = {
	// 		"action" : "update_status",
	// 		"params" : { "user_id" : userid, "status" : "online", "role":role, "course":course, "activity" : activity }
	// 	};

	// 	doSend(JSON.stringify(data));
	// }

	// function notifierAlerte(conteneur, obj, donnee) {
	// 	var data = {
	// 		"action" : "alerte",
	// 		"params" : { "user_id" : user_id, "container" : conteneur, "object":obj, "activity":activity_id, "course" : course_id, "data" : donnee }
	// 	};

	// 	doSend(JSON.stringify(data));
	// }

	


});