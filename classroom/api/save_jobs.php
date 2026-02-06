<?php
require_once '../inc/db.php';
header('Content-Type: application/json; charset=utf-8');

$jobs = $_POST['job'] ?? [];

foreach($jobs as $student_id => $job){
    $student_id = intval($student_id);
    $job = trim($job);

    $stmt = $conn->prepare("UPDATE students SET job=? WHERE id=?");
    $stmt->bind_param("si", $job, $student_id);
    $stmt->execute();
}

echo json_encode(['ok'=>true, 'msg'=>'شغل دانش‌آموزان ثبت شد']);
