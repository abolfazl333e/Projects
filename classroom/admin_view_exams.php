<?php
require_once 'inc/db.php';
require_once 'inc/jdf.php';
$conn->set_charset("utf8mb4");

// دریافت فیلترهای کاربر
$filter_lesson = intval($_GET['lesson_id'] ?? 0);
$filter_date   = $_GET['examDate'] ?? '';

// گرفتن لیست دروس برای select
$lessons_res = $conn->query("SELECT id, name FROM lessons ORDER BY name ASC");
$lessons = [];
while($l = $lessons_res->fetch_assoc()){
    $lessons[] = $l;
}

// کوئری اصلی با فیلتر
$query = "
SELECT e.*, s.name AS student_name, l.name AS lesson_name
FROM exams e
JOIN students s ON e.student_id = s.id
JOIN lessons l ON e.lesson_id = l.id
WHERE 1
";

$params = [];
$types = "";

// اگر درس انتخاب شده باشد
if($filter_lesson){
    $query .= " AND e.lesson_id=?";
    $params[] = $filter_lesson;
    $types .= "i";
}

// اگر تاریخ انتخاب شده باشد
if($filter_date){
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $filter_date_en = str_replace($fa,$en,$filter_date);

    $query .= " AND e.exam_date=?";
    $params[] = $filter_date_en;
    $types .= "s";
}

// مرتب سازی بر اساس ID دانش آموز
$query .= " ORDER BY e.student_id ASC";

$stmt = $conn->prepare($query);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>مدیریت امتحانات</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">

<!-- jQuery و Persian Datepicker -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.css">

<style>
body {font-family: Vazirmatn, sans-serif; direction: rtl; text-align: center; margin: 30px; background: #f8f9fa;}
table {width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; margin-top: 20px;}
th, td {padding: 10px; border: 1px solid #ccc;}
th {background: #0d6efd; color: white;}
a.btn {background: #198754; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;}
a.btn:hover {background: #157347;}
select, input {padding: 5px; border-radius: 5px; border: 1px solid #ccc; margin: 0 5px;}
button {padding: 6px 12px; border-radius: 5px; border: none; background: #0d6efd; color: white; cursor: pointer;}
button:hover {background: #0b5ed7;}
.filter-form {margin-bottom: 15px;}
</style>
</head>
<body>
<h2>⚙️ مدیریت امتحانات</h2>

<form method="get" class="filter-form">
    <label>درس:</label>
    <select name="lesson_id">
        <option value="">همه دروس</option>
        <?php foreach($lessons as $l): ?>
            <option value="<?= $l['id'] ?>" <?= $filter_lesson==$l['id']?'selected':'' ?>><?= htmlspecialchars($l['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>تاریخ:</label>
    <input type="text" id="examDate" name="examDate" value="<?= htmlspecialchars($filter_date) ?>" placeholder="YYYY-MM-DD">

    <button type="submit">نمایش</button>
</form>

<table>
<tr>
  <th>نام دانش‌آموز</th>
  <th>درس</th>
  <th>نمره / وضعیت</th>
  <th>تاریخ</th>
  <th>روز هفته</th>
  <th>عملیات</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['student_name']) ?></td>
  <td><?= htmlspecialchars($row['lesson_name']) ?></td>
  <td><?= htmlspecialchars($row['score']) ?></td>
  <td><?= htmlspecialchars($row['exam_date']) ?></td>
  <td><?= htmlspecialchars($row['weekday']) ?></td>
  <td><a href="api/edit_exam.php?id=<?= $row['id'] ?>" class="btn">ویرایش</a></td>
</tr>
<?php endwhile; ?>
</table>

<script>
$(function(){
    // اضافه کردن Persian Datepicker برای فیلتر تاریخ
    $("#examDate").persianDatepicker({
        format: 'YYYY-MM-DD',
        autoClose: true
    });
});
</script>
</body>
</html>
