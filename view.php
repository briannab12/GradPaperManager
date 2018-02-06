<?php
	//TODO: Move bulk of php code to seperate file
	
	if( empty($_GET['paperId']) || empty($_SESSION['role']) ){
		//header("location: index.php");
	}
	
	require_once "php_scripts/DB.class.php";
	$db = new DB();
	$conn = $db->getConnection();

	// Retrieve title, abstract, and citation of paper
	$stmt = $conn->prepare("SELECT title, abstract, citation FROM papers WHERE id=:id");
	$stmt->execute(array(":id" => $_GET['paperId']));
	
	//checks if data was found
	if( $stmt->rowCount() == 1 ){
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$title = $row['title'];
		$abstract = $row['abstract'];
		$citation = $row['citation'];

		// The SQL query to get the autors name as "firstname lastname" based on paper ID
		$stmt=$conn->prepare("select CONCAT(fName, ' ', lName) AS name, users.id from users inner join authorship on users.id = authorship.userId inner join papers on authorship.paperId = papers.id where papers.id = :id");
		$stmt->execute(array(":id" => $_GET['paperId']));
		$authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Retrieve all possible authors from users
		$stmt = $conn->prepare("SELECT CONCAT(fName, ' ', lName) AS name, id FROM users");
		$stmt->execute();
		$availableAuthors = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Retrieve keywords of paper
		$stmt = $conn->prepare("SELECT keyword FROM paper_keywords WHERE id=:id");
		$stmt->execute(array(":id" => $_GET['paperId']));
		$keywords = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	}else{
		//no data was found 
		//TODO: add url param to trigger error message on search page when coming from here
		header("location: search.php");
	}

	session_start();
	$access = false;
		
	if($_SESSION['role'] == "admin"){
		//if you are an admin you have access to edit
		$access = true;
	}else if( $_SESSION['role'] == "faculty" ){
		//In the case user is faculty, we must see if THIS paper being viewed belongs to said user/faculty member
		$stmt = $conn->prepare("SELECT userId FROM authorship WHERE paperId=:paperId AND userId=:userId");
		$stmt->execute( array(":paperId" => $_GET['paperId'], ":userId" => $_SESSION["userId"]) );
		$paper = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		
		if(sizeOf($paper) == 1){
			//The facultyId is an author on this paper
			$access = true;
		}else{
			//The facultyId is NOT an author on this paper
			$access = false;
		}
	}
	
	//TODO: needs to be revisited
	$bootstrapList = "";
	//If authors exist, create a list of them
	if(count($keywords) > 0){
		foreach($keywords as $key) {
			$bootstrapList .= $key . ", ";
		}
	}
	
	$keywords = $bootstrapList;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>View Papaer</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	
	<script src="js/chosen.jquery.min.js"></script>
	<link href="css/chosen.min.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet">
</head>

<body>
<!-- Main Container -->
<div class="container">
	<!-- Header -->
	<div class="page-header">
		<h1>Software Title</h1>
		<button id="logoutBtn" type="button" class="btn btn-primary">Logout</button>
	</div>
	<!-- Header End -->

	<!-- Title -->
	<div class="row">
		<section class="col-xs-12">
			<span>Title: </span>
			<?php
			// Check if access is allowed
			if($access) {
				echo "<input type='text' id='titleField' value='$title' />";
			} else {
				echo "<span>$title</span>";
			}
			?>
		</section>
	</div>
	<!-- Title End -->
	
	<!-- Authors -->
	<div class="row">
		<section class="col-xs-12">
			<span>Authors: </span>
			<?php
			//Check if access is allowed
			if($access) {
				echo "<select multiple id='authorTags'>";
			} else {
				echo "<select multiple id='authorTags' disabled>";
			}
			
			// Check whether author is selected out of available
			foreach($availableAuthors as $author) {
				if(array_search($author, $authors) === false) {
					echo "<option value='" . $author['id'] . "'>" . $author['name'] . "</option>";
				} else {
					echo "<option value='" . $author['id'] . "' selected>" . $author['name'] . "</option>";
				}
			}
			
			echo "</select>";
			?>
		</section>
	</div>
	<!-- Authors End -->
	
	<!-- Abstract -->
	<div class="row">
		<section class="col-xs-12">
			<span>Abstract:</span><br>
			<?php
			// Check if access is allowed
			if($access) {
				echo "<textarea id='abstractField'>$abstract</textarea>";
			} else {
				echo "<p>$abstract</p>";
			}
			?>
		</section>
	</div>
	<!-- Abstract End -->
	
	<!-- Citation -->
	<div class="row">
		<section class="col-xs-12">
			<span>Citation:</span>
			<?php
			// Check if access is allowed
			if($access) {
				echo "<input type='text' id='citationField' value='$citation' />";
			} else {
				echo "<span>$citation</span>";
			}
			?>
		</section>
	</div>
	<!-- Citation End -->
	
	<!-- Keywords -->
	<div class="row">
		<section class="col-xs-12">
			<span>Keywords: </span>
			<?php
			//Check if access is allowed
			if($access) {
				echo "<input id='keywordTags' data-role='tagsinput' value='$keywords' />";
			} else {
				echo "<input disabled id='keywordTags' data-role='tagsinput' value='$keywords' />";
			}
			?>
		</section>
	</div>
	<!-- Keywords End -->
</div>
<!-- Main Container End -->
<script>
$(function() {
	$("#logoutBtn").click(logout);
	$("#authorTags").chosen();
	$("#keywordTags").tagsinput();
	
	// Get values for edit
	var title = $("#titleField").val();
	var authors = $("#authorTags").val();
	var abstract = $("#abstractField").val();
	var citation = $("#citationField").val();
	var keywords = $("#keywordTags").tagsinput('items');
	
	console.log(title);
	console.log(authors);
	console.log(abstract);
	console.log(citation);
	console.log(keywords);
});
</script>
</body>
</html>
