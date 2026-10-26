<?php
// connect to db server
require_once 'connection.php';

// Create database
$sql = "CREATE DATABASE db5e";
if (mysqli_query($conn, $sql)) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>