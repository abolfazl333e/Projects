<?php

session_start();

require_once myClass_DIR.'inc/jdf.php';

//// بررسی اینکه آیا کاربر وارد شده و ادمین است یا نه
$is_admin = isset($_SESSION['admin_id']);
$is_admin =  true;
?>

<div class="attendance">
<!-- ابرها -->
    <div class="cloud cloud1"></div>
    <div class="cloud cloud2"></div>

    <!-- کارت حضور و غیاب -->
    <div class="card">
        <h1>حضور و غیاب پایه پنجم (امروز: <?= jdate('d-m-Y') ?>)</h1>
        <div id="studentsList">در حال بارگذاری...</div>

        <?php
        if (@$is_admin): ?>
            <!-- فقط برای ادمین -->
            <div class="controls">
                <button class="btn btn-primary" id="finalizeBtn">نهایی کردن (ثبت غایبان)</button>
                <button class="btn btn-primary" id="refreshBtn">بروزرسانی</button>
                <span id="lastUpdated"></span>
                <button class="btn" id="reportBtn">
                    <a href="http://kingaboo.ir/classroom/report.php" target="_blank" class="">مشاهده گزارش ها</a>
                </button>
            </div>
        <?php else: ?>
            <!-- برای کاربران غیرادمین -->
            <div class="controls">
                <button class="btn btn-primary" id="refreshBtn">بروزرسانی</button>
                <span id="lastUpdated"></span>
            </div>
        <?php endif; ?>
    </div>

</div>