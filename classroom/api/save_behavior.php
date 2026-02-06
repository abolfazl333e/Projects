<?php
require_once '../inc/db.php';
header('Content-Type: application/json; charset=utf-8');

$lesson_id = intval($_POST['lesson_id'] ?? 0);
$positives = $_POST['positive'] ?? [];
$negatives = $_POST['negative'] ?? [];

if(!$lesson_id){
    echo json_encode(['ok'=>false, 'msg'=>'درس انتخاب نشده است']);
    exit;
}

// مطمئن شو جدول student_behavior کلید ترکیبی (student_id + lesson_id) دارد
// ALTER TABLE student_behavior ADD UNIQUE KEY student_lesson_unique (student_id, lesson_id);

foreach($positives as $student_id => $pos){
    $student_id = intval($student_id);
    $pos = intval($pos);
    $neg = intval($negatives[$student_id] ?? 0);

    $stmt = $conn->prepare("
        INSERT INTO student_behavior (student_id, lesson_id, positive_count, negative_count, date)
        VALUES (?, ?, ?, ?, CURDATE())
        ON DUPLICATE KEY UPDATE 
            positive_count=VALUES(positive_count), 
            negative_count=VALUES(negative_count),
            updated_at=NOW()
    ");
    $stmt->bind_param("iiii", $student_id, $lesson_id, $pos, $neg);
    $stmt->execute();
}

echo json_encode(['ok'=>true, 'msg'=>'رفتار دانش‌آموزان ثبت شد']);
