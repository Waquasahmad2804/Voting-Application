<?php
$hostname = "localhost";
$username = "root";
$password = ""; // Use an empty string if there is no password
$dbname = "votingdetail";

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo "Connection successful";
} catch (PDOException $err) {
    echo "Connection unsuccessful: " . $err->getMessage();
}
?>
