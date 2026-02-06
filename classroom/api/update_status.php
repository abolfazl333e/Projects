<?php
session_start();
require_once '../inc/db.php';

header('Content-Type: application/json; charset=utf-8');

// فقط ادمین اجازه دارد
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['msg' => 'دسترسی غیرمجاز']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if (!$id || !$status) {
    echo json_encode(['msg' => 'اطلاعات ناقص است']);
    exit;
}

$today = date('Y-m-d');
$nowTime = date('H:i:s');

// ثبت مشابه receive_rfid.php
$stmt = $conn->prepare("
    INSERT INTO attendance (student_id, date, time, status)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        status = VALUES(status),
        time = IF(time IS NULL, VALUES(time), time),
        updated_at = NOW()
");
$stmt->bind_param("isss", $id, $today, $nowTime, $status);

if ($stmt->execute()) {
    echo json_encode(['msg' => 'وضعیت با موفقیت ثبت شد']);
} else {
    echo json_encode(['msg' => 'خطا در ثبت اطلاعات']);
}
