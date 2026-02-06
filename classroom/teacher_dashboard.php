<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    // جلوگیری از دسترسی مستقیم بدون لاگین
    header("Location: teacher_login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>پنل مدیریت معلم</title>
<style>
@font-face {
    font-family: 'BNazanin';
    src: url('fonts/B_Nazanin.woff2') format('woff2'),
         url('fonts/B_Nazanin.woff') format('woff');
}

body {
    font-family: "B Nazanin" !important;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #2a9d8f, #e9c46a);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.panel-card {
    font-family: "B Nazanin" !important;
    background: #ffffff;
    border-radius: 20px;
    padding: 40px 30px;
    max-width: 700px;
    width: 90%;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    text-align: center;
}

h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #264653;
    font-size: 28px;
}

.logout {
    font-family: "B Nazanin" !important;
    position: absolute;
    top: 20px;
    left: 30px;
    font-size: 20px;
    text-decoration: none;
    color: #e63946;
    font-weight: bold;
    transition: color 0.3s;
}
.logout:hover {
    color: #b71c1c;
}

.options {
    font-family: "B Nazanin" !important;
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 30px;
}

.option-btn {
    font-family: "B Nazanin" !important;
    background: #264653;
    color: #fff;
    text-decoration: none;
    padding: 15px 20px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: bold;
    transition: background 0.3s, transform 0.2s;
}
.option-btn:hover {
    background: #1b3a4b;
    transform: scale(1.05);
}

@media (max-width: 480px) {
    .panel-card {
        padding: 30px 20px;
    }
    h2 {
        font-size: 24px;
    }
    .option-btn {
        font-size: 16px;
        padding: 12px 15px;
    }
}
</style>
</head>
<body>

<a class="logout" href="teacher_logout.php">خروج</a>

<div class="panel-card">
    <h2>سلام <?= htmlspecialchars($admin_name) ?></h2>
    <p>به پنل مدیریت خوش آمدید.</p>

   <div class="options">
    <a class="option-btn" href="attendance.php">حضور و غیاب</a>
    <a class="option-btn" href="set_jobs.php">تنظیم شغل‌ها</a>
    <a class="option-btn" href="behavior.php">ارزشیابی دروس</a>
    <a class="option-btn" href="manage_experiments.php"> مدیریت آزمایشات </a>
    <a class="option-btn" href="manage_groups.php"> مدیریت گروه ها </a>
        <a class="option-btn" href="admin_exams.php"> مدیریت امتحانات </a>
    <a class="option-btn" href="students_report.php"> کارنامه دانش آموزان </a>
    <a class="option-btn" href="api/students_import.php"> آپلود فایل لیست دانش آموزان </a>
    
</div>
</div>

</body>
</html>
