<?php
	$host = "localhost";
	$user = "root";
	$pass = "";
	$db = "attendancemsystem";
	
	$conn = new mysqli("localhost","root","","attendancemsystem");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>