<?php
	/*
	 * Group 14 - Joshua Lora, Jon Aurit, Brianna Buttaccio, Joseph Oh
	 *
	 * Simple class to wrap our connection object and connection params in an easy to use object
	 */
	 
	class DB {
		
		private $conn;
		
		/*
		 * Creates DB connection when this class is instantiated
		 */
		function __construct(){
			//file that contains the DB connection information !!!!NEEDS TO BE UPDATED TO WORK!!!!!!
			//require_once("../../../dbinfo.php");
			// Temporary:
			$host = "localhost";
			$user = "root";
			$pass = "";
			$db = "facresearchdb";
			// -- Temporary
			
			try{
				//open the connection
				$this->conn = new PDO("mysql:host=$host;dbname=$db",$user,$pass);
				
				//change to verbose error reporting
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){
				echo $e->getMessage();
				die();
			}
		}
		
		/*
		 * Returns the PDO connection object
		 */
		public function getConnection(){
			return $this->conn;
		}
	}