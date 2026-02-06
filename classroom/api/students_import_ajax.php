<?php
session_start();
require_once __DIR__ . '/../inc/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if(!isset($_SESSION['admin_id'])) throw new Exception('دسترسی غیرمجاز');

    if(!isset($_FILES['csv_file'])) throw new Exception('فایل CSV ارسال نشده');

    $tmp = $_FILES['csv_file']['tmp_name'];
    if(!file_exists($tmp) || !is_readable($tmp)) throw new Exception('فایل قابل خواندن نیست');

    // باز کردن
    if(($handle = fopen($tmp, "r")) === false) throw new Exception('خطا در باز کردن فایل CSV');

    // خالی کردن جدول با غیرفعال کردن موقت FK
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    $conn->query("TRUNCATE TABLE students");
    $conn->query("SET FOREIGN_KEY_CHECKS=1");

    $row = 0;
    $added = 0;
    $skipped = 0;
    $unique_counter = 1;

    // خواندن CSV؛ فرمت:
    // name, tag, class, job, username, password, must_change_password
    while(($data = fgetcsv($handle, 2000, ",")) !== false){
        // header skip
        if($row == 0 && stripos($data[0] ?? '', 'name') !== false){ $row++; continue; }

        $name = trim($data[0] ?? '') ?: '-';
        $tag  = trim($data[1] ?? '') ?: '-';
        $class = trim($data[2] ?? '') ?: '-';
        $job = trim($data[3] ?? '') ?: '-';
        $username = trim($data[4] ?? '');
        $password_plain = trim($data[5] ?? '');
        $must_change_password = intval($data[6] ?? 1);

        // اگر username خالی بود، مقدار یونیک بساز
        if(empty($username)) {
            $username = 'noid_' . $unique_counter++;
        }

        // اگر password خالی بود، از username به عنوان password استفاده کن (قابل تغییر بعد)
        if($password_plain === '') $password_plain = $username;

        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO students (name, rfid_tag, class, job, username, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if(!$stmt){
            // prepare failed; skip row
            $skipped++;
            $row++;
            continue;
        }
        $stmt->bind_param("ssssssi", $name, $tag, $class, $job, $username, $password_hash, $must_change_password);
        if($stmt->execute()){
            $added++;
        } else {
            $skipped++;
        }
        $row++;
    }
    fclose($handle);

    // بازگردانی لیست جدید، بر اساس id
    $students = [];
    $res = $conn->query("SELECT id, name, rfid_tag, class, job, username, (password_hash IS NOT NULL AND password_hash <> '') AS has_password, must_change_password FROM students ORDER BY id ASC");
    while($r = $res->fetch_assoc()) $students[] = $r;

    echo json_encode(['ok'=>true, 'added'=>$added, 'skipped'=>$skipped, 'students'=>$students], JSON_UNESCAPED_UNICODE);

} catch(Exception $e){
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
