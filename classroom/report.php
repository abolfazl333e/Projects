<?php
global $conn;
require_once 'inc/db.php';
require_once 'inc/jdf.php'; // کتابخانه تاریخ شمسی

// اگر تاریخی انتخاب نشده، امروز شمسی را بگیر
$selected_date = isset($_GET['date']) ? $_GET['date'] : jdate("Y-m-d");

// تبدیل شمسی به میلادی برای جستجو در دیتابیس
function jalali_to_gregorian_date($jalali_date) {
    list($jy, $jm, $jd) = explode('-', $jalali_date);
    list($gy, $gm, $gd) = jalali_to_gregorian($jy, $jm, $jd);
    return sprintf("%04d-%02d-%02d", $gy, $gm, $gd);
}

$gregorian_date = jalali_to_gregorian_date($selected_date);

// گرفتن داده‌ها
$stmt = $conn->prepare("
    SELECT 
        s.id AS student_id,
        s.name,
        COALESCE(a.status, 'غایب') AS status,
        a.time
    FROM students s
    LEFT JOIN attendance a
        ON s.id = a.student_id AND a.date = ?
    ORDER BY s.name ASC
");
$stmt->bind_param("s", $gregorian_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>گزارش حضور و غیاب</title>
    <link rel="stylesheet" href="css/report.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

</head>
<body>

<h2>📅 گزارش حضور و غیاب</h2>

<form method="get" class="date-selector">
    <label>تاریخ (شمسی): </label>
    <input type="text" id="datepicker" name="date" value="<?php echo $selected_date; ?>">
    <button type="submit">نمایش</button>
</form>

<table>
    <thead>
    <tr>
        <th>نام دانش‌آموز</th>
        <th>وضعیت</th>
        <th>زمان ثبت</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $time_fa = $row['time'] ? jdate("H:i:s", strtotime($row['time'])) : '-';
            $status_fa = $row['status'] === 'حاضر' ? '✅ حاضر' : '❌ غایب';
            echo "<tr>
                <td>{$row['name']}</td>
                <td>{$status_fa}</td>
                <td>{$time_fa}</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>هیچ رکوردی برای این تاریخ وجود ندارد</td></tr>";
    }
    ?>
    </tbody>
</table>

<script src="js/report.js"></script>
</body>
</html>
