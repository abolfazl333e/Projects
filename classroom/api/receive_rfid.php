<?php
// receive_rfid.php - نسخه ساده برای ESP8266
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Tehran');

// پاسخ ساده متن
header('Content-Type: text/plain; charset=utf-8');

// اتصال DB
require_once __DIR__ . '/../inc/db.php';
$conn->set_charset('utf8mb4');

// دریافت RFID
$rfid = trim($_POST['tagID'] ?? '');
if (empty($rfid)) {
    echo "off";
    exit;
}

// جستجوی دانش‌آموز
$stmt = $conn->prepare("SELECT id FROM students WHERE rfid_tag=? LIMIT 1");
$stmt->bind_param("s", $rfid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $student_id = (int)$student['id'];

    $today = date('Y-m-d');
    $nowTime = date('H:i:s');

    // ثبت حضور در جدول attendance
    $attStmt = $conn->prepare("
        INSERT INTO attendance (student_id, date, time, status)
        VALUES (?, ?, ?, 'حاضر')
        ON DUPLICATE KEY UPDATE
            status='حاضر',
            time=IF(time IS NULL, VALUES(time), time),
            updated_at=NOW()
    ");
    $attStmt->bind_param("iss", $student_id, $today, $nowTime);
    if ($attStmt->execute()) {
        echo "-"; // کارت معتبر
    } else {
        echo "off"; // خطای DB
    }
} else {
    // کارت ناشناس
    echo "off";
}
