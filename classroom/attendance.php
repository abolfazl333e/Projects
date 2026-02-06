<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'inc/db.php';
require_once 'inc/jdf.php';

$is_admin = isset($_SESSION['admin_id']);
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>حضور و غیاب پایه پنجم</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>

    <div class="card">
        <h1>حضور و غیاب پایه پنجم (امروز: <?= jdate('Y/m/d') ?>)</h1>

        <div id="studentsList">در حال بارگذاری...</div>

        <div class="controls">
            <button class="btn btn-primary" id="refreshBtn">بروزرسانی</button>
            <span id="lastUpdated"></span>

            <?php if ($is_admin): ?>
                <button class="btn btn-primary" id="finalizeBtn">نهایی کردن (ثبت غایبان)</button>
                <a href="report.php" target="_blank" class="btn">مشاهده گزارش‌ها</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ارسال مقدار ادمین به جاوااسکریپت -->
    <script>
        const is_admin = <?= $is_admin ? 'true' : 'false' ?>;
    </script>

    <script src="js/attendance.js"></script>
</body>
</html>
