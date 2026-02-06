<?php
session_start();
require_once 'inc/db.php';
require_once 'inc/jdf.php';
$conn->set_charset('utf8mb4');

if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// گرفتن شغل دانش‌آموز
$student_res = $conn->query("SELECT job FROM students WHERE id=$student_id");
$student_row = $student_res->fetch_assoc();
$student_job = $student_row['job'] ?? 'وارد نشده';

// گرفتن لیست دروس
$lessons = [];
$res = $conn->query("SELECT id, name FROM lessons ORDER BY name ASC");
while($row = $res->fetch_assoc()) $lessons[] = $row;

// گرفتن حضور و غیاب امروز
$today = date('Y-m-d');
$attendance = $conn->query("SELECT status, time FROM attendance WHERE student_id=$student_id AND date='$today'");
$att = $attendance->fetch_assoc() ?? ['status'=>'غایب', 'time'=>'-'];

// درس انتخابی
$selected_lesson_id = $_GET['lesson_id'] ?? '';
$behavior = null;
if($selected_lesson_id){
    $stmt = $conn->prepare("SELECT positive_count, negative_count FROM student_behavior WHERE student_id=? AND lesson_id=?");
    $stmt->bind_param("ii", $student_id, $selected_lesson_id);
    $stmt->execute();
    $behavior = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>🎒 داشبورد دانش‌آموز</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        font-family: 'B Nazanin', sans-serif;
        background: linear-gradient(135deg, #a8edea, #fed6e3);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        user-select: none;
    }
    .dashboard {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        padding: 30px;
        width: 380px;
        text-align: center;
        position: relative;
        animation: pop 0.6s ease;
    }
    @keyframes pop {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .logout {
        position: absolute;
        top: 15px;
        left: 15px;
    }
    .logout a {
        text-decoration: none;
        color: #ff5c5c;
        font-weight: bold;
        font-size: 16px;
    }
    h2 {
        color: #007bff;
        font-size: 26px;
        margin-top: 10px;
    }
    .info {
        margin-top: 20px;
        font-size: 18px;
        color: #333;
    }
    select {
        margin-top: 15px;
        padding: 10px;
        border-radius: 10px;
        border: 2px solid #007bff;
        font-size: 16px;
        width: 80%;
        cursor: pointer;
        transition: 0.3s;
    }
    select:hover {
        background-color: #e7f1ff;
    }
    .status {
        background: #e9f7ef;
        color: #2e7d32;
        padding: 10px;
        border-radius: 10px;
        margin: 15px 0;
        display: inline-block;
        font-weight: bold;
    }
    .card {
        background: #fef3c7;
        border: 2px dashed #f59e0b;
        border-radius: 15px;
        padding: 15px;
        margin-top: 15px;
        text-align: center;
    }
    .positive {
        color: #28a745;
        font-weight: bold;
        font-size: 20px;
    }
    .negative {
        color: #dc3545;
        font-weight: bold;
        font-size: 20px;
    }
    .emoji {
        font-size: 35px;
        margin-bottom: 10px;
        animation: float 2s infinite ease-in-out;
    }
    @keyframes float {
        0% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
        100% { transform: translateY(0); }
    }
</style>
</head>
<body>

<div class="dashboard">
    <div class="logout"><a href="student_logout.php"><i class="fas fa-door-open"></i> خروج</a></div>
    
    <div class="emoji">🎓</div>
    <h2>سلام <?= htmlspecialchars($student_name) ?> 🌟</h2>
    <div class="info">شغل  ‌: <strong><?= htmlspecialchars($student_job) ?></strong></div>
    <div class="status">
        حضور امروز: <?= $att['status']=='حاضر' ? '✅ حاضر' : '❌ غایب' ?>  
        <br>⏰ <?= $att['time'] ?>
    </div>

    <form method="get">
        <label><strong>📘 انتخاب درس:</strong></label><br>
        <select name="lesson_id" onchange="this.form.submit()">
            <option value="">-- یکی از درس‌ها را انتخاب کن --</option>
            <?php foreach($lessons as $lesson): ?>
                <option value="<?= $lesson['id'] ?>" <?= $selected_lesson_id==$lesson['id']?'selected':'' ?>><?= htmlspecialchars($lesson['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if($behavior): ?>
        <div class="card">
            <p>تعداد مثبت‌ها: <span class="positive"><?= $behavior['positive_count'] ?></span> 🌞</p>
            <p>تعداد منفی‌ها: <span class="negative"><?= $behavior['negative_count'] ?></span> 🌧️</p>
        </div>
    <?php elseif($selected_lesson_id): ?>
        <div class="card">برای این درس هنوز امتیازی ثبت نشده است 📘</div>
    <?php endif; ?>
</div>

</body>
</html>
