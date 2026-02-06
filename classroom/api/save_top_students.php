<?php
require_once '../inc/db.php';
$data = json_decode(file_get_contents('php://input'), true);

$year = (int)($data['year'] ?? 0);
$month = (int)($data['month'] ?? 0);
$students = $data['students'] ?? [];

if(!$year || !$month || !$students){
    echo json_encode(['success'=>false,'msg'=>'داده‌های نامعتبر']);
    exit;
}

foreach($students as $s){
    $id = (int)($s['id']);
    $balance = (int)($s['balance']);
    $calculated_stars = (int)($s['calculated_stars']);
    $manual_stars = (int)($s['manual_stars']);
    $total_stars = (int)($s['total_stars']);

    $conn->query("
        INSERT INTO top_students_archive (student_id, year, month, balance, calculated_stars, manual_stars, total_stars)
        VALUES ($id,$year,$month,$balance,$calculated_stars,$manual_stars,$total_stars)
        ON DUPLICATE KEY UPDATE balance=$balance, calculated_stars=$calculated_stars, manual_stars=$manual_stars, total_stars=$total_stars
    ");
}

echo json_encode(['success'=>true]);
