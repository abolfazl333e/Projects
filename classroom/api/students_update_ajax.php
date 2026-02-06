<?php
session_start();
require_once __DIR__ . '/../inc/db.php';
header('Content-Type: application/json; charset=utf-8');

try{
    if(!isset($_SESSION['admin_id'])) throw new Exception('دسترسی غیرمجاز');

    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input) throw new Exception('داده‌ها معتبر نیست');

    $id = intval($input['id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $class = trim($input['class'] ?? '');
    $rfid_tag = trim($input['rfid_tag'] ?? '');
    $job = trim($input['job'] ?? '');
    $username = trim($input['username'] ?? '');
    $password_plain = $input['password'] ?? null; // may be empty string
    $must_change_password = intval($input['must_change_password'] ?? 1);

    if(!$id) throw new Exception('آی‌دی معتبر نیست');
    if($name === '' || $rfid_tag === '' || $username === '') throw new Exception('نام، کارت و نام کاربری نمی‌توانند خالی باشند');

    // اگر رمز جدید وارد شده، هش کن
    if(is_string($password_plain) && $password_plain !== ''){
        $password_hash_new = password_hash($password_plain, PASSWORD_DEFAULT);
    } else {
        // نگه داشتن هش فعلی
        $stmt = $conn->prepare("SELECT password_hash FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $prev = $stmt->get_result()->fetch_assoc();
        $password_hash_new = $prev['password_hash'] ?? '';
    }

    $stmt = $conn->prepare("UPDATE students SET name=?, class=?, rfid_tag=?, job=?, username=?, password_hash=?, must_change_password=? WHERE id=?");
    if(!$stmt) throw new Exception('خطا در آماده‌سازی پرس و جو: ' . $conn->error);
    $stmt->bind_param("ssssssii", $name, $class, $rfid_tag, $job, $username, $password_hash_new, $must_change_password, $id);

    if($stmt->execute()){
        echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('خطا در اجرا: ' . $stmt->error);
    }

} catch(Exception $e){
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
