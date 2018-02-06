<?php
	/*
	 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
	 */
	if( !empty($_POST["action"]) && $_POST["action"] == "logout" && logout() ) {
		echo "true";
	}
	
	//Simply grabs the correct session, destroys it, and redirects to the login page
	function logout(){
		session_start();
		session_unset(); 
		session_destroy();
		
		return true;
	}
	
	
?>