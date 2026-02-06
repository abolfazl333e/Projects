<?php
require_once '../inc/db.php';
$conn->set_charset('utf8mb4');

$id = $_GET['id'] ?? 0;

// حذف تصاویر از پوشه و جدول
$imgRes = $conn->query("SELECT filename FROM experiment_images WHERE experiment_id=".$id);
while($img = $imgRes->fetch_assoc()){
    @unlink('../experiments_images/'.$img['filename']);
}
$conn->query("DELETE FROM experiment_images WHERE experiment_id=".$id);

// حذف خود آزمایش
$conn->query("DELETE FROM experiments WHERE id=".$id);

echo json_encode(['ok'=>true]);
