<?php
	/*
	 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
	 * 
	 * Our login file containing login methods
	 */
	 
	//include the db connection code
	require_once("DB.class.php");
	$db = new DB();
	
	// Checks to see which action to perform
	if( !empty($_POST["action"]) && $_POST["action"] == "login" ) {
		// Attempt to login and echo return value back to the front end
		echo login($_POST["username"], $_POST["password"]); 
			
	}else if(!empty($_POST["action"]) && $_POST["action"] == "guest" ){
		//this is in the case ther user has continued as guest
		echo guestLogin();
	}
	
	/*
	 * Function called from above to validate the login for the given user and pw
	 */
	function login($user, $pw){
		global $db;
		
		//defaulted to error message
		$returnArray = array("message"=>"We are currently experiencing technical issues, please try again later.");
		
		if( $db ->getConnection() != null){
			// Sanitize user inputs
			$user = filter_var($user, FILTER_SANITIZE_STRING);
			$pw = filter_var($pw, FILTER_SANITIZE_STRING);
			
			// Create the statement, bind the params
			$stmt = $db->getConnection()->prepare("SELECT * FROM users WHERE userName=:user AND password=:pw");
			$stmt->execute( array(":user"=>$user, ":pw"=>$pw) );
			
			// Should only be 1 row returned
			if ( $stmt->rowCount() == 1 ){
				// Retrieve 1 row as an associative array
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				
				// Start Session
				session_start();
				
				// Sets session variables
				$_SESSION["userName"] = $row["userName"];
				$_SESSION["userId"] = $row["id"];
				$_SESSION["role"] = $row["role"];
				
				$returnArray = array("success"=>true);
			}else{
				$returnArray = array("message"=>"Invalid username or password, please try again.");
			}
		}
		
		// Credentials are not valid or an error occurred
		return json_encode($returnArray);
	}
	
	//function called when user wants to view site as guest. Simply sets the session role as guest for permissions validation 
	function guestLogin(){
		global $db;
		//defaulted to error message
		$returnArray = array("message"=>"We are currently experiencing technical issues, please try again later.");
		
		if( $db ->getConnection() != null){
			// Start Session
			session_start();
			// Set user role to Guest
			$_SESSION['role'] = "public";
			
			$returnArray = array("success"=>true);
		}
		
		return json_encode($returnArray);
	}
?>