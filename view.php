<?php

/*
 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
 */
	session_start();
	if( empty($_GET['paperId']) || empty($_SESSION['role'])) {
		header("location: index.php");
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
		//no data was found, this shouldnt ever happen 
		//TODO: add url param to trigger error message on search page when coming from here
		header("location: search.php");
	}

	$access = false;
		
	if(!empty($_SESSION['role']) && $_SESSION['role'] == "admin"){
		//if you are an admin you have access to edit
		$access = true;
	}else if(!empty($_SESSION['role']) && $_SESSION['role'] == "faculty" ){
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
	
	$bootstrapList = "";
	//If authors exist, create a list of them
	if(count($keywords) > 0){
		for($i = 0; $i < count($keywords); $i++){
			$bootstrapList .= $keywords[$i];
			if(array_key_exists($i+1,$keywords)) {
				//$i != count($keywords)-1
				$bootstrapList .= ", ";
			}
		}
	}
	
	$keywords = $bootstrapList;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>View Paper</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	
	<script src="https://cdn.rawgit.com/bootstrap-tagsinput/bootstrap-tagsinput/master/dist/bootstrap-tagsinput.min.js"></script>
	<link href="https://cdn.rawgit.com/bootstrap-tagsinput/bootstrap-tagsinput/master/dist/bootstrap-tagsinput.css" rel="stylesheet">
	<script src="js/chosen.jquery.min.js"></script>
	<link href="css/chosen.min.css" rel="stylesheet">
	
	<link href="css/custom.css" rel="stylesheet">
	<script src="js/doesStuff.js"></script>
</head>

<body>
<!-- Main Container -->
<div class="container">
	<!-- Header -->
	<div class="page-header">
		<h1>Research Paper View</h1>
	</div>	
	<div class="top-buttons">
		<button type="button" class="goBackBtn btn btn-warning">Go Back</button>
		<?php
			if($access) {
				//Display submit button
				echo '<button type="button" id="editBtn" class="btn btn-success">Edit</button>';
			} 
		?>
		<button type="button" id="logoutBtn" class="btn btn-primary">Logout</button>
	</div>
	
	<!-- Header End -->

	<!-- Title -->
	
	<div class="data-area">
		<h2>Title: </h2>
		<?php
			// Check if access is allowed
			if($access) {
				echo "<input type='text' id='titleField' value='$title' />";
			} else {
				echo "<p>$title</p>";
			}
		?>
	</div>
	
	<!-- Title End -->
	
	<!-- Authors -->
	<div class="data-area">
		<h2>Authors: </h2>
		<?php
			//Check if access is allowed
			if($access) {
				echo "<select multiple id='authorTags' disabled='true'>";
				// Check whether author is selected out of available
				foreach($availableAuthors as $author) {
					if(array_search($author, $authors) === false) {
						echo "<option value='" . $author['id'] . "'>" . $author['name'] . "</option>";
					} else {
						echo "<option value='" . $author['id'] . "' selected>" . $author['name'] . "</option>";
					}
				}
				echo "</select>";
			} else {
				// Check whether author is selected out of available
				$authorList = "";
				$first = true;
				foreach($availableAuthors as $author) {
					if(array_search($author, $authors) !== false) {
						if($first !=true){
							$authorList .= ', ';
						}
						//If authors exist, create a list of them
						$authorList .= $author['name'];
						$first = false;
					}
				}
				echo $authorList;
			}
		?>
	</div>
	<!-- Authors End -->
	
	<!-- Abstract -->
	<div class="data-area">
		<h2>Abstract:</h2>
		<?php
			// Check if access is allowed
			if($access) {
				echo "<textarea id='abstractField'>$abstract</textarea>";
			} else {
				echo "<p>$abstract</p>";
			}
		?>
	</div>
	<!-- Abstract End -->
	
	<!-- Citation -->
	<div class="data-area">
		<h2>Citation:</h2>
		<?php
			// Check if access is allowed
			if($access) {
				echo "<textarea id='citationField'>$citation</textarea>";
			} else {
				echo "<p>$citation</p>";
			}
		?>
	</div>
	<!-- Citation End -->
	
	<!-- Keywords -->
	<div class="data-area">
		<h2>Keywords: </h2>
		<?php
			//Check if access is allowed
			if($access) {
				echo "<input id='keywordTags' data-role='tagsinput' value='$keywords' />";
			} else {
				//echo "<input disabled id='keywordTags' data-role='tagsinput' value='$keywords' />";
				echo "<p>$keywords<p/>";
			}
		?>
	</div>
	<!-- Keywords End -->
	
	<div id="delete-error"></div>
	
	<br />
	<br />
		
	<div>
		<div class="top-buttons">
			<button type="button" class="goBackBtn btn btn-info btn-warning">Go Back</button>
			<?php
				if($access) {
					//Display submit button
					echo '<button type="button" class="btn btn-info" id="SubmitBtn" style="display:none">Submit</button>&nbsp';
					//Display delete paper button
					echo '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#areYouSure" id="deleteBtn" style="display:none">Delete Paper</button>';
				} 
			?>
		</div>
	</div>
	
	<!-- Modal -->
	<div class="modal fade" id="areYouSure" role="dialog">
		<div class="modal-dialog">

		  <!-- Modal content-->
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">&times;</button>
			  <h4 class="modal-title">Are You Sure?</h4>
			</div>
			<div class="modal-body">
			  <p>Deleting this paper will remove it from the database. You cannot undo these changes once made.</p>
			</div>
			<div class="modal-footer">
			  <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
			  <button type="button" id="deletePaper" class="btn btn-default" data-dismiss="modal">Yes</button>
			</div>
		  </div>
		  
		</div>
	</div>
	
</div>
<!-- Main Container End -->
<script>
$(function(){
	$("#logoutBtn").click(logout);
	$("#editBtn").click(enableEdit);
	$(".goBackBtn").click(goBack);
	$("#deletePaper").click(deletePaper);
	
	
	//attach plugins to given fields
	$("#authorTags").chosen();
	$("#keywordTags").tagsinput();
	
	//disabled everything when first entering the page
	$(".data-area").children().prop("disabled",true);
	
	$("#SubmitBtn").click(function() {
		// Get values for edit
		var title = sanitizeString($("#titleField").val());
		var abstractValues = sanitizeString($("#abstractField").val());
		var citation = sanitizeString($("#citationField").val());
		
		var authors = $("#authorTags").val();
		for (var i=0; i<authors.length; i++) {
			sanitizeString(authors[i]);
		}
		
		var keywords = $("#keywordTags").tagsinput('items');
		for (var i=0; i<keywords.length; i++) {
			sanitizeString(keywords[i]);
		}
		
		$.ajax({
			dataType: 'JSON',
			type: 'POST',
			data: {
				"action": "edit",
				"paperID": <?php echo $_GET['paperId']; ?>,
				"title": title,
				"authors": authors,
				"abstract": abstractValues,
				"citation": citation,
				"keywords": keywords
			},
			url: 'php_scripts/paperFunctions.php',
			success: function(data) {
				//console.log(data);
				if( data.success == "true"){
					//if logout worked, redirect to index
					location.assign("index.php");
				}	
			}
		}); 
	});
});


//On faculty/admin view papers, 
function enableEdit(){
	var button = this;
	//toggle the editable field on and off
	if($(".data-area").children().prop("disabled")){
		button.innerHTML="Stop Editing";
		$(".data-area").children().prop("disabled", false);
		$("#SubmitBtn").show();
		$("#deleteBtn").show();
		$("#authorTags").prop("disabled", false);
		$('#authorTags').trigger('chosen:updated');
	}else{
		button.innerHTML="Edit";
		$(".data-area").children().prop("disabled", true);
		$("#SubmitBtn").hide();
		$("#deleteBtn").hide();
		$("#authorTags").prop("disabled", true);
		$('#authorTags').trigger('chosen:updated');
		$("#delete-error").hide();
	}	
}

//function called when user selects "yes" in delete paper modal
function deletePaper(){
	console.log("test!!!");
	
	$.ajax({
		dataType: 'JSON',
		type: 'POST',
		data: {
			"action": "remove",
			"paperID": <?php echo $_GET['paperId']; ?>
		},
		url: 'php_scripts/paperFunctions.php',
		success: function(data) {
			console.log(data);
			if( data.success){
				//if paper was deleted
				location.assign("search.php");
				$("#delete-error").hide();
			}else{
				$("#delete-error").html("<p><span>&#8855;</span> "+data.message+"</p>")
				$("#delete-error").show();
			}
		}
	});
}


</script>
</body>
</html>
