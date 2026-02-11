<?php
$servername = getenv('localhost');
$username = getenv('root');
$password = getenv('');
$dbname = getenv('pekar2');
$port = getenv('3306');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";
?>