<?php
	/*
	 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
	 */
	
	// Include the db connection code
	require_once("DB.class.php");
	$db = new DB();
	
	// Start session
	session_start();
	
	// Check the post array(after form submit or ajax) to call appropriate function
	if( !empty($_POST["action"]) ){
		if( $_POST["action"] == "get" ) {
			if( !empty( $_POST["keyword"] ) ) {
				echo getPaper( $_POST["keyword"] );
			} else {
				echo getPaper(null);
			}
		} else if( $_POST["action"] == "add" ) {
			echo addPaper( $_POST );
		} else if( $_POST["action"] == "remove" ) {
			echo removePaper( $_POST["paperID"] );
		} else if( $_POST["action"] == "edit" ) {
			echo editPaper( $_POST["paperID"], $_POST );
		}
	}
	/*
	 * Given a keyword, returns all papers associated to that keyword, if no keyword present, returns all papers
	 */
	function getPaper($keyword){
		global $db;
		$conn = $db->getConnection();
		
		$keyword = filter_var($keyword, FILTER_SANITIZE_STRING);
		
		//check if keyword is present, if so, use it, if not, select all papers
		try {
			if( $keyword != null ){
				$stmt = $conn->prepare("SELECT * FROM papers INNER JOIN paper_keywords USING(id) WHERE keyword LIKE :kw GROUP BY id");
				$stmt->execute( array(":kw" => "%" . $keyword . "%" ) );
			} else {
				$stmt = $conn->prepare("SELECT * FROM papers");
				$stmt->execute();
			}
		} catch(PDOException $ex) {
			//if there is an error, we return a valid error message to the front end
			return json_encode(array("message"=>"There was an error when searching. Please contact system admin if problem persists."));
		}
		
		//returns the json representation 
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(sizeOF($results) < 1){
			//if no results are found, we return a valid error message to the front end
			$results = array("message"=>"No data was found based on your search.");
		}
		return json_encode($results);
	}
	
	/*
	 * Adds a paper to the DB
	 */
	function addPaper($data) {
		global $db;
		$conn = $db->getConnection();
		
		try {
			// Begin Transaction
			$conn->beginTransaction();
			
			// Set parameters
			$params = array(":tie" => $data["title"], 
							":abs" => $data["abstract"], 
							":cit" => $data["citation"], 
							":id" => $id);
							
			// !!!!!!! Need to add keywords and authorship !!!!!!!!!!!!
			
			// Update table with new data
			$stmt = $conn->prepare("INSERT INTO papers (title, abstract, citation) VALUES (:tie, :abs, :cit)");
			$stmt->execute( $params );
			
			// Commit data
			$conn->commit();
		} catch(PDOException $ex) {
			// Rollback transaction in case of error
			$conn->rollBack();
			return json_encode($ex->getMessage());
		}
	}
	
	/*
	 * Given a paper ID, removes said paper  
	 */
	function removePaper($id) {
		global $db;
		$conn = $db->getConnection();
		
		try {
			// Begin Transaction
			$conn->beginTransaction();
			
			// Set parameters
			$params = array(":id" => $id);
			
			// Remove paper from all tables
			$stmt = $conn->prepare("DELETE FROM papers WHERE id=:id");
			$stmt2 = $conn->prepare("DELETE FROM paper_keywords WHERE id=:id");
			$stmt3 = $conn->prepare("DELETE FROM authorship WHERE id=:id");
			$stmt->execute($params);
			$stmt2->execute($params);
			$stmt3->execute($params);
			
			// Commit data
			$conn->commit();
		} catch(PDOException $ex) {
			// Rollback transaction in case of error
			$conn->rollBack();
			return json_encode($ex->getMessage());
		}
	}
	
	/*
	 * Update a paper's contents based on paper id
	 */
	function editPaper($id, $data) {
		global $db;
		$conn = $db->getConnection();
		
		try {
			// Begin Transaction
			$conn->beginTransaction();
			
			// Set parameters
			$params = array(":tie" => $data["title"], 
							":abs" => $data["abstract"], 
							":cit" => $data["citation"], 
							":id" => $id);
			
			// Update table with new data
			$stmt = $conn->prepare("UPDATE papers SET title=:tie, abstract=:abs, citation=:cit WHERE id=:id");
			$stmt->execute( $params );
			
			// Commit data
			$conn->commit();
		} catch(PDOException $ex) {
			// Rollback transaction in case of error
			$conn->rollBack();
			return json_encode($ex->getMessage());
		}
	}
?>