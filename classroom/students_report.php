<?php
global $conn;
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// دریافت دروس
$lessons = [];
$res = $conn->query("SELECT id, name FROM lessons ORDER BY name");
while($row = $res->fetch_assoc()){
    $lessons[$row['id']] = $row['name'];
}

// دریافت دانش آموزان
$students = [];
$res = $conn->query("SELECT id, name FROM students ORDER BY id");
while($row = $res->fetch_assoc()){
    $students[$row['id']] = $row['name'];
}

// دریافت رفتارها
$behaviors = [];
$res = $conn->query("SELECT student_id, lesson_id, positive_count, negative_count FROM student_behavior");
while($row = $res->fetch_assoc()){
    $behaviors[$row['student_id']][$row['lesson_id']] = [
        'positive' => $row['positive_count'],
        'negative' => $row['negative_count']
    ];
}

// بیشترین مثبت هر درس
$maxPositives = [];
foreach($behaviors as $studentId => $lessonsData){
    foreach($lessonsData as $lessonId => $values){
        $maxPositives[$lessonId] = max($maxPositives[$lessonId] ?? 0, $values['positive']);
    }
}

// تابع محاسبه سطح
function getLevel($positive, $negative, $maxPositive) {
    if ($positive == 0 && $negative == 0) return ["بدون امتیاز", "#999999"];
    if ($negative > $positive) return ["نیاز به تلاش بیشتر", "#dc3545"];
    if ($maxPositive == 0) return ["بدون امتیاز", "#999999"];

    $score = ($positive / $maxPositive) * 100;
    if ($score >= 90) return ["خیلی خوب", "#28a745"];
    if ($score >= 70) return ["خوب", "#82c91e"];
    if ($score >= 50) return ["قابل قبول", "#ffc107"];
    return ["نیاز به تلاش بیشتر", "#dc3545"];
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>کارنامه دانش آموزان</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Vazirmatn', sans-serif;
    background:#f0f2f5;
    padding:20px;
    direction:rtl;
}
h2 { text-align:center; color:#333; margin-bottom:20px; }
#exportBtn {
    margin-bottom:20px;
    padding:12px 25px;
    background:#ff6f61;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
    font-weight:bold;
    transition:0.3s;
}
#exportBtn:hover { background:#ff3b2f; transform:translateY(-2px); }

.table-wrapper {
    overflow-x:auto; /* اسکرول افقی */
    overflow-y:auto; /* اختیاری: اسکرول عمودی */
    background:#fff;
    padding:15px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.1);
    max-height:80vh; /* اگر بخواهید اسکرول عمودی داشته باشد */
}

table {
    border-collapse: separate;
    border-spacing: 0 10px;
    width:max-content; /* جدول اندازه واقعی خودش را حفظ کند */
    min-width:900px;  /* حداقل عرض */
    table-layout:auto;
}

th {
    background: linear-gradient(135deg,#6a11cb,#2575fc);
    color:white;
    font-weight:bold;
    padding:12px 15px;
    position:sticky;
    top:0;
    z-index:2;
    border-radius:8px;
    white-space:nowrap;
}

td {
    background:#fafafa;
    padding:10px 12px;
    border-radius:8px;
    text-align:center;
    font-size:15px;
    transition:0.3s;
    box-shadow:0 2px 5px rgba(0,0,0,0.05);
    white-space:nowrap;
}

td:hover { background:#e3f2fd; transform:translateY(-2px); }

td .positive, td .negative { display:block; font-weight:bold; margin-bottom:4px; font-size:14px; }
td .positive { color:#28a745; }
td .negative { color:#dc3545; }
td .positive::before { content:"✅ "; }
td .negative::before { content:"❌ "; }
td .level { margin-top:4px; font-size:13px; font-weight:bold; color:white; padding:4px 6px; border-radius:6px; display:inline-block; }

td img { width:40px; height:40px; object-fit:cover; border-radius:50%; }
td.name-col { font-weight:bold; min-width:120px; }

tr { transition: transform 0.2s; }
tr:hover { transform:scale(1.02); }

/* هیچ تغییری روی فونت و جدول در موبایل اعمال نشود، فقط اسکرول افقی */
</style>
</head>
<body>

<h2>کارنامه دانش آموزان</h2>
<button id="exportBtn">دانلود جدول به صورت عکس</button>

<div class="table-wrapper" id="tableWrapper">
<table>
<thead>
<tr>
    <th>عکس</th>
    <th>نام دانش آموز</th>
    <?php foreach($lessons as $lesson): ?>
        <th><?php echo htmlspecialchars($lesson); ?></th>
    <?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($students as $sid => $sname): ?>
<tr>
    <td>
        <?php 
        $imgPath = "images/students/{$sid}.jpg";
        if(!file_exists($imgPath)) $imgPath = "images/students/default_student.png";
        ?>
        <img src="<?php echo $imgPath; ?>" alt="عکس دانش‌آموز">
    </td>

    <td class="name-col"><?php echo htmlspecialchars($sname); ?></td>

    <?php foreach($lessons as $lid => $lname): ?>
        <?php
        $positive = $behaviors[$sid][$lid]['positive'] ?? 0;
        $negative = $behaviors[$sid][$lid]['negative'] ?? 0;
        $maxPositive = $maxPositives[$lid] ?? 0;
        list($levelText, $levelColor) = getLevel($positive, $negative, $maxPositive);
        ?>
        <td>
            <div class="positive">مثبت: <?php echo $positive; ?></div>
            <div class="negative">منفی: <?php echo $negative; ?></div>
            <div class="level" style="background:<?php echo $levelColor ?>;">
                <?php echo $levelText ; ?>
            </div>
        </td>
    <?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.getElementById('exportBtn').addEventListener('click', function(){
    html2canvas(document.querySelector("#tableWrapper")).then(canvas=>{
        let link=document.createElement("a");
        link.download="report.png";
        link.href=canvas.toDataURL();
        link.click();
    });
});
</script>

</body>
</html>
