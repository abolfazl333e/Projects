<?php
require_once '../inc/db.php';
$conn->set_charset('utf8mb4');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$exam_id = intval($_GET['id'] ?? 0);
if (!$exam_id) die('شناسه امتحان مشخص نشده است');

$stmt = $conn->prepare("
    SELECT e.*, s.name AS student_name, l.name AS lesson_name
    FROM exams e
    JOIN students s ON e.student_id = s.id
    JOIN lessons l ON e.lesson_id = l.id
    WHERE e.id=?
");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$res = $stmt->get_result();
$exam = $res->fetch_assoc();
if (!$exam) die('امتحان پیدا نشد');

$scores = ['نیاز به تلاش بیشتر','قابل قبول','خوب','خیلی خوب','غایب'];

// تبدیل اعداد انگلیسی به فارسی برای نمایش
function en_to_fa($str){
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return str_replace($en, $fa, $str);
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>ویرایش امتحان</title>
<link href="https://cdn.jsdelivr.net/npm/vazirmatn@33.003/font-face.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.css">
<style>
body {font-family: Vazirmatn, sans-serif; background: #f5f6fa; padding: 20px; direction: rtl;}
.container {max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc;}
h3 {text-align: center; margin-bottom: 20px;}
label {display: block; margin-top: 10px; margin-bottom: 5px; font-weight: bold;}
select, input {width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc; font-size: 14px;}
button {margin-top: 15px; width: 100%; background: #27ae60; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;}
button:hover {background: #2ecc71;}
.header-btn {margin-bottom: 15px; background: #0d6efd; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;}
.header-btn:hover {background: #0b5ed7;}
</style>
</head>
<body>
<div class="container">
<button class="header-btn" onclick="location.href='admin_view_exams.php'">بازگشت به لیست امتحانات</button>

<h3>ویرایش امتحان</h3>

<p><strong>دانش‌آموز:</strong> <?= htmlspecialchars($exam['student_name']) ?></p>
<p><strong>درس:</strong> <?= htmlspecialchars($exam['lesson_name']) ?></p>

<form id="editForm">
    <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">

    <label>نمره / وضعیت:</label>
    <select name="score" required>
        <?php foreach($scores as $s): ?>
            <option value="<?= $s ?>" <?= $exam['score']==$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
    </select>

    <label>تاریخ امتحان:</label>
    <input type="text" id="examDate" name="examDate" required value="<?= en_to_fa($exam['exam_date']) ?>">
    <input type="hidden" id="examWeekday" name="examWeekday" value="<?= $exam['weekday'] ?>">

    <button type="submit">ثبت تغییرات</button>
</form>
</div>

<script>
$(function(){
    // datepicker شمسی
    $("#examDate").persianDatepicker({
        format: 'YYYY-MM-DD',
        autoClose: true,
        initialValue: true,
        initialValueType: 'gregorian',
        onSelect: function(unix){
            const days = ['شنبه','یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنج‌شنبه','جمعه'];
            const day = new persianDate(unix).day();
            $("#examWeekday").val(days[day]);
        }
    });

    // ارسال AJAX
   $("#editForm").on("submit", function(e){
        e.preventDefault();
        $.post("save_exam.php", $(this).serialize(), function(res){
            alert(res.message);
            if(res.success && res.redirect){
                window.location.href = res.redirect; // هدایت به صفحه مدیریت امتحانات
            }
        }, "json");
    });

});
</script>
</body>
</html>
