<?php


require_once __DIR__ . '/inc/db.php';
$conn->set_charset('utf8mb4');

$res = $conn->query("SELECT * FROM new_rfid");

if($row = $res->fetch_assoc()){
    $new_rfid = $row['rfid'];
}

?>

<label>
    آیدی کارت جدید
    <input type="text" value="<?php echo $new_rfid; ?>">
</label>