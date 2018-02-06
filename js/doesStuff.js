/*
* Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
*/

$(document).ready(function() {
	// Run AJAX to retrieve all tables
	$.ajax({
		dataType: 'json',
		type: 'POST',
		url: 'php_scripts/paperFunctions.php',
		data: {
			"action": "get"
		},
		success: function(data) {
			listTables(data);
		},
		// If any errors, throw to console
		error: function (textStatus, errorThrown) {
			console.log(textStatus);
			console.log(errorThrown);
		}
	});
	
	// On logout button click
	$("#logout").click(function(event) {
		// Prevent submitting empty form
		event.preventDefault();
		
		// Direct to logout page
		location.assign('php_scripts/logout.php');
	});
});

// List papers in a table given data from AJAX call
function listTables(data) {
	var html = "";
	
	html += "<table><tr><th>Title</th></tr>";
	
	// Iteratre through data object
	$.each(data, function(index, obj) {
		// If object exists
		if(obj.title != null) {
			html += "<tr><td>" + obj.title + "</td></tr>";
		}
	});
	html += "</table>";
	
	// Set html
	$("#content").html(html);
}

//found this sanitizeString method on stack overflow, seems to work ok
function sanitizeString(str){
	str = str.replace(/[^a-z0-9áéíóúñü \.,_-]/gim,"");
	return str.trim();
}

//simple function that logs user out
function logout(){
	$.ajax({
		dataType: 'text',
		type: 'POST',
		data: {
			action: "logout"		
		},
		url: 'php_scripts/logout.php',
		success: function(data) {					
			if( data == "true"){
				//if logout worked, redirect to index
				location.assign("index.php");
			}	
		}
	}); 
}