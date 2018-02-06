<?php
	/*
	 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
	 */
	
	// Include the db connection code
	require_once("DB.class.php");
	$db = new DB();
	
	// Start session
	session_name("iste330");
	session_start();
	
	// Check the post array(after form submit or ajax) to call appropriate function
	if( $_POST["action"] == "get" ) {
		if( isset( $_POST["username"] ) ) {
			echo getUser( $_POST["username"] );
		} else {
			echo getUser(null);
		}
	} else if( $_POST["action"] == "add" ) {
		echo addUser( $_POST );
	} else if( $_POST["action"] == "remove" ) {
		echo removeUser( $_POST["id"] );
	} else if( $_POST["action"] == "edit" ) {
		echo editUser( $_POST["id"], $_POST );
	}
	
	/*
	 * Given a username, returns user information
	 * If no username given, returns all user information
	 */
	function getUser($username) {
		global $db;
		
		//check if keyword is present, if so, use it, if not, select all papers
		try {
			if( $username != null ){
				$stmt = $db->getConnection()->prepare("SELECT id, userName, fName, lName, email, role FROM users WHERE userName=:us");
				$stmt->execute( array(":us"=>$username) );
			} else {
				$stmt = $db->getConnection()->prepare("SELECT id, userName, fName, lName, email, role FROM users");
				$stmt->execute();
			}
		} catch(PDOException $ex) {
			return json_encode($ex->getMessage());
		}
		
		//returns the json representation 
		return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
	}
	
	/*
	 * Adds a user to the DB
	 */
	function addUser($data) {
		global $db;
		
		try {
			// Begin Transaction
			$db->getConnection()->beginTransaction();
			
			// Set parameters
			$params = array(":id" => $data["id"], 
							":userName" => $data["username"], 
							":fName" => $data["firstname"], 
							":lName" => $data["lastname"],
							":pass" => $data["password"],
							":email" => $data["email"],
							":role" => $data["role"]);
			
			// Update table with new data
			$stmt = $db->getConnection()->prepare("INSERT INTO users VALUES (:id, :userName, :fName, :lName, :pass, :email, :role)");
			$stmt->execute( $params );
			
			// Commit data
			$db->getConnection()->commit();
		} catch(PDOException $ex) {
			// Rollback transaction in case of error
			$db->getConnection()->rollBack();
			return json_encode($ex->getMessage());
		}
	}
	
	/*
	 * Given an id, remove user
	 */
	function removeUser($id) {
		global $db;
		
		try {
			// Begin Transaction
			$db->getConnection()->beginTransaction();
			
			// Set parameters
			$params = array(":id" => $id);
			
			// Remove paper from all tables
			$stmt = $db->getConnection()->prepare("DELETE FROM users WHERE id=:id");
			$stmt2 = $db->getConnection()->prepare("DELETE FROM authorship WHERE id=:id");
			$stmt->execute($params);
			$stmt2->execute($params);
			
			// Commit data
			$db->getConnection()->commit();
		} catch(PDOException $ex) {
			// Rollback transaction in case of error
			$db->getConnection()->rollBack();
			return json_encode($ex->getMessage());
		}
	}
	
	/*
	 * Update a user based on their id
	 */
	function editUser($id, $data) {
		global $db;
		
		try {
			// Begin Transaction
			$db->getConnection()->beginTransaction();
			
			// Set parameters
			$params = array(":id" => $data["id"], 
							":userName" => $data["username"], 
							":fName" => $data["firstname"], 
							":lName" => $data["lastname"],
							":pass" => $data["password"],
							":email" => $data["email"],
							":role" => $data["role"]);
			
			// Update table with new data
			$stmt = $db->getConnection()->prepare("UPDATE users SET userName=:userName, fName=:fName, lName=:lName, password=:pass, email=:email, role=:role WHERE id=:id)");
			$stmt->execute( $params );
			
			// Commit data
			$db->getConnection()->commit();
		} catch(PDOException $ex) {
			// Rollback transaction in case of error
			$db->getConnection()->rollBack();
			return json_encode($ex->getMessage());
		}
	}
?>