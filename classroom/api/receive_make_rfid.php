<?php

date_default_timezone_set('Asia/Tehran');

require_once __DIR__ . '/../inc/db.php';
$conn->set_charset('utf8mb4');

header('Content-Type: text/plain; charset=utf-8');

$new_rfid = ''; 
 
if ($_POST['new_tagID']) {

    $rfid = trim($_POST['new_tagID'] ?? '');
    
     $query = "UPDATE new_rfid SET rfid = '".$rfid."' ";

    if ($conn->query($query) === TRUE) {
      echo "New record created successfully";
    } 
    echo'done';
}

?>