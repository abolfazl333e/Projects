<?php
date_default_timezone_set('Asia/Tehran');

$servername = "localhost";
$username = "kingabo3_mapva";
$password = "F&4ijK9q8UKW";
$dbname = "kingabo3_school";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
