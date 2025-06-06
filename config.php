<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "shop_db";

// Create MySQLi connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better Unicode support
$conn->set_charset("utf8mb4");

?>
