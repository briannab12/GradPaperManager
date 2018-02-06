<?php
/*
 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
 */
 
	session_start();
	if(empty($_SESSION['role'])) {
		header("location: index.php");
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Search Papaer</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	
	<script src="js/doesStuff.js"></script>
	<link href="css/custom.css" rel="stylesheet">
</head>

<body>
<!-- Main Container -->
<div class="container">
	<!-- Header -->
	<div class="page-header">
		<h1>Research Paper Database Search</h1>
		<button id="logoutBtn" type="button" class="btn btn-primary">Logout</button>
	</div>
	<!-- Header End -->

	<!-- New Search Area -->
	
	<div class="search-area">
		<div class="form-group">					
			<input class="form-control" type="text" id="searchText" placeholder="Enter a keyword to search against or leave blank to view all papers..."></textarea>
		</div>
		<div class="form-group text-right">
			<button type="button" class="login-button btn btn-info" id="searchBtn">Search</button>
		</div>
		<div id="login-error" >
			<p><span>&#8855;</span> Invalid username or password, please try again.</p>
		</div>

	</div>

	
	<!-- Search Area -->
	<!--
	<div class="row">
		<section id="searchDiv" class="col-xs-12">
			<input type="text" id="searchText" placeholder="Enter a keyword to search against or leave blank to view all papers..." />
			<button type="button" id="searchBtn">Search</button>
		</section>
		
	</div>
	
	<div id="search-error"></div>
	-->
	<!-- Search Area End -->
	
	<!-- Table -->
	<div class="row"  id="results-section" style="display: none;">
		<section id="tableDiv" class="col-xs-12">
			<table>
				<tr>
					<th>Paper Title</th>
					<th>View</th>
				</tr>
				<tbody id="populateArea">
				
				</tbody>
			</table>
		</section>
	</div>
	<!-- Table End -->
</div>
<!-- Main Container End -->

<script>
	$(function() {
		$("#searchBtn").click(searchText);
		$("#logoutBtn").click(logout);
	});

	//simple keyup to set the enter key to trigger login
	$("#searchText").keyup(function(event){
		if(event.keyCode == 13){
			searchText();
		}
	});

	// On search
	function searchText() {
		var searchBy = sanitizeString($("#searchText").val());	
		
		// Search for papers based on text
		$.ajax({
			data: {
				"action": "get",
				"keyword": searchBy
			},
			dataType: "json",
			method: "POST",
			url: "php_scripts/paperFunctions.php",
			success: function(data) {
				//check for valid data
				if( data.message === undefined ){
					//Get papers and populate table
					var html = "";
					
					$.each(data, function(index, obj) {
						html += "<tr>";
						html += "<td>" + obj.title + "</td>";
						html += "<td><button type='button' class='viewBtn' onclick='redirectView(this)' data-id='" + obj.id + "'>View</button></td>";
						html += "</tr>";
					});
					
					$("#populateArea").html(html);
					$("#search-error").hide();
					$("#results-section").show();
				}else{
					//handles the case where there was an error serverside/no results
					$("#results-section").hide();
					$("#populateArea").html("");
					
					$("#search-error").html("<p>"+data.message+"</p>")
					$("#search-error").show();
				}
			}
		});
	}
	
	//function called when user clicks view button. Redirects to view page with paper id passed as a param
	function redirectView(obj) {
		var id = $(obj).data("id");
		location.assign("view.php?paperId=" + id);
	}
</script>
</body>
</html>
