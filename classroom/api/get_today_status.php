<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/jdf.php';

// اطمینان از تنظیم charset
$conn->set_charset("utf8mb4");

$today = date('Y-m-d');
$data = [];

$sql = "
SELECT 
    s.id, 
    CONVERT(s.name USING utf8mb4) AS name, 
    CONVERT(COALESCE(a.status, 'غایب') USING utf8mb4) AS status, 
    a.time
FROM students s
LEFT JOIN attendance a 
    ON s.id = a.student_id AND a.date = '$today'
ORDER BY s.id ASC
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => $conn->error], JSON_UNESCAPED_UNICODE);
}

$conn->close();
