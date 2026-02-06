<?php
require_once '../inc/db.php';
header('Content-Type: application/json');

$today = date('Y-m-d');

$sql = "
INSERT INTO attendance (student_id, date, status)
SELECT s.id, '$today', 'غایب' 
FROM students s
LEFT JOIN attendance a ON a.student_id=s.id AND a.date='$today'
WHERE a.id IS NULL
";
if (@$conn->query($sql)) {
    echo json_encode(['ok'=>true,'msg'=>'غایبان ثبت شدند.']);
} else {
    echo json_encode(['ok'=>false,'msg'=>'خطا']);
}
