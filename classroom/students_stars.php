<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// گرفتن لیست دانش‌آموزان
$students = [];
$res = $conn->query("SELECT id, name, class, job FROM students ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
    $students[$row['id']] = $row;
    $students[$row['id']]['balance'] = 0;
    $students[$row['id']]['calculated_stars'] = 0;
    $students[$row['id']]['manual_stars'] = 0;
}

// محاسبه تراز (مثبت-منفی) **به جز درس ستاره**
$behavior_res = $conn->query("
    SELECT student_id,
           SUM(positive_count) AS total_positive,
           SUM(negative_count) AS total_negative
    FROM student_behavior
    WHERE lesson_id != 9
    GROUP BY student_id
");
while ($b = $behavior_res->fetch_assoc()) {
    $student_id = $b['student_id'];
    $balance = $b['total_positive'] - $b['total_negative'];
    $students[$student_id]['balance'] = $balance;
    $students[$student_id]['calculated_stars'] = $balance > 0 ? floor($balance / 10) : 0;
}

// گرفتن ستاره‌های دستی از درس ستاره با id=9
$manual_res = $conn->query("
    SELECT student_id, SUM(positive_count) AS stars
    FROM student_behavior
    WHERE lesson_id = 9
    GROUP BY student_id
");
while ($m = $manual_res->fetch_assoc()) {
    $student_id = $m['student_id'];
    $students[$student_id]['manual_stars'] = $m['stars'];
}

// نمایش جدول
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title> امتیازات </title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-family: B Nazanin, sans-serif; padding: 20px; background: #f4f6f9; }
table { width: 90%; margin: auto; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
th, td { padding: 12px 15px; text-align: center; }
th { background: #007bff; color: #fff; }
tr:nth-child(even) { background: #f9f9f9; }
.stars { color: gold; }
</style>
</head>
<body>

<h1 style="text-align:center;">⭐️ امتیازات دانش آموزان ⭐️</h1>

<table>
<tr>
<th>ردیف</th>
<th>نام</th>
<th>کلاس</th>
<th>شغل</th>
<th>تراز مثبت-منفی</th>
<th>ستاره‌ها</th>
</tr>

<?php

usort($students, function($a, $b) {
    $totalA = ($a['calculated_stars'] ?? 0) + ($a['manual_stars'] ?? 0);
    $totalB = ($b['calculated_stars'] ?? 0) + ($b['manual_stars'] ?? 0);
    return $totalB <=> $totalA; // نزولی
});

$i = 1;
foreach ($students as $s) {
    $total_stars = $s['calculated_stars'] + $s['manual_stars'];
    echo "<tr>
        <td>{$i}</td>
        <td>".htmlspecialchars($s['name'])."</td>
        <td>".htmlspecialchars($s['class'])."</td>
        <td>".htmlspecialchars($s['job'])."</td>
        <td style='direction:ltr;'>".($s['balance'] ?? 0)."</td>
        <td class='stars'>";
    for ($j = 0; $j < $total_stars; $j++) {
        echo "<i class='fa fa-star'></i>";
    }
    echo "</td></tr>";
    $i++;
}
?>
</table>

</body>
</html>
