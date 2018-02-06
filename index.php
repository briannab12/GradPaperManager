<?php
/*
 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
 */

	session_start();
	
	if( isset($_SESSION['userName']) ){
		header("location: search.php");
	}
 
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ISTE 330 - Group 14 Project</title>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		
		<link rel="stylesheet" type="text/css" href="css/custom.css">
		<script src="js/doesStuff.js"></script>
		
	</head>
	<body id="login-page">
		<div class="container">
			<div class="login-area" id="login-but">
				<h2 class="text-center" >Academic Paper Database</h2>
				<br />

				<div class="form-group">
					<label for="username">Username:</label>
					<input class="form-control" type="text" name="username" id="username"></textarea>
				</div>
				<div class="form-group">					
					<label for="password">Password:</label>
					<input class="form-control" type="password" name="password" id="password"></textarea>
				</div>
				<div class="form-group text-center">
					<button type="button" value="login" class="login-button btn btn-primary">Login</button><br />
					or<br />
					<button type="button" value="guest" class="login-button btn btn-primary">Continue as Guest</button>
				</div>
				<div id="login-error" >
					<!-- <p id="error-message"><span>&#8855;</span> </p> -->
				</div>

			</div>
		</div>
		
		<script>		
			$(function(){
				//simple keyup to set the enter key to trigger login
				$("#login-but").keyup(function(event){
					if(event.keyCode == 13){
						checkLogin("login");
					}
				});
				
				//onlick to fire when user clicks on our login/guest buttons
				$( ".login-button" ).click(function(e) {
					checkLogin( e.currentTarget.value );
				});
			});
			
			//function called on button press
			function checkLogin(action){
				//console.log("test ");
				var user = sanitizeString( $("#username").val());
				var pw = sanitizeString( $("#password").val() );
				
				//check to catch case where login is attempted with no values
				if(action == "login" && (user == "" || pw =="")){
					$('#login-error').html("<p><span>&#8855;</span> Please enter a valid credentials.</p>");
					$('#login-error').show(); //Invalid username or password, please try again.
					return false;
				}
					
				for(var i=0;i<1000;i++){
					pw = CryptoJS.MD5(pw).toString();
				}
				
				$.ajax({
					dataType: 'json',
					type: 'POST',
					url: 'php_scripts/login.php',
					data: {
						action: action,
						username: user,
						password: pw
					},
					success: function(data) {					
						//date is valid 
						if(data.success){
							location.assign("search.php");
						}else{
							$('#login-error').html("<p><span>&#8855;</span> "+data.message+"</p>");
							$('#login-error').show();
						}
						
						return true;
					}
				}); 
				
				return false;
			}
		</script>
	</body>
</html>