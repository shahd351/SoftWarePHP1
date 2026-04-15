<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "root";
$dbname = "thamean";
$port = 8889;  // منفذ MAMP لـ MySQL

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Connected successfully to Thameen database.";
?>