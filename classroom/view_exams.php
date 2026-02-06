<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// تابع تبدیل اعداد فارسی به انگلیسی
function fa2en($str){
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($fa,$en,$str);
}

// فیلترها
$filter_lesson = intval($_GET['lesson_id'] ?? 0);
$filter_date   = fa2en($_GET['examDate'] ?? '');

// لیست دروس
$lessons_res = $conn->query("SELECT id, name FROM lessons ORDER BY name ASC");
$lessons = [];
while($l = $lessons_res->fetch_assoc()) $lessons[] = $l;

// ساخت کوئری
$query = "SELECT e.*, s.name AS student_name, s.id AS student_id, l.name AS lesson_name
          FROM exams e
          JOIN students s ON e.student_id = s.id
          JOIN lessons l ON e.lesson_id = l.id
          WHERE 1";

$params = [];
$types = "";

if($filter_lesson){
    $query .= " AND e.lesson_id=?";
    $params[] = $filter_lesson;
    $types .= "i";
}
if($filter_date){
    $query .= " AND e.exam_date=?";
    $params[] = $filter_date;
    $types .= "s";
}

$query .= " ORDER BY e.lesson_id, e.exam_date, s.id ASC";

$stmt = $conn->prepare($query);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// گروه‌بندی بر اساس درس و تاریخ
$exams_grouped = [];
while($r = $res->fetch_assoc()){
    $key = $r['lesson_name'] . '|' . $r['exam_date'];
    $exams_grouped[$key]['lesson_name'] = $r['lesson_name'];
    $exams_grouped[$key]['exam_date'] = $r['exam_date'];
    $exams_grouped[$key]['exam_image'] = !empty($r['exam_image']) ? $r['exam_image'] : 'images/exams/default_exam.jpg';
    $exams_grouped[$key]['students'][] = $r;
}

// رنگ نمره
$score_colors = [
    'خیلی خوب' => '#27ae60',
    'خوب' => '#82e0aa',
    'قابل قبول' => '#f39c12',
    'نیاز به تلاش بیشتر' => '#e74c3c',
    'غایب' => '#7f8c8d'
];
?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>📋 لیست امتحانات</title>
<link href="https://cdn.jsdelivr.net/npm/vazirmatn@33.003/font-face.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.css">
<style>
body {
    font-family: B Nazanin, sans-serif;
    font-weight:bold;
    background: #f0f4f8;
    direction: rtl;
    padding: 20px;
    margin: 0;
}
h2 {text-align: center; margin-bottom: 20px; color: #34495e;}
.filters {text-align: center; margin-bottom: 20px;}
.filters select, .filters input, .filters button {
    padding: 8px 12px;
    margin: 0 5px 10px 5px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}
.filters button {
    background: #2980b9;
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.2s;
}
.filters button:hover {background: #3498db;}

/* آکاردئون */
.accordion {
    background-color: white;
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}
.accordion-header {
    background-color: #3498db;
    color: white;
    padding: 15px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.accordion-header:hover {background-color: #2980b9;}
.accordion-body {
    display: none;
    background: #fff;
    padding: 15px;
}
.exam-image {
    width: 100%;
    max-width: 300px;
    border-radius: 10px;
    cursor: pointer;
    display: block;
    margin: 10px auto;
}
.students {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
}
.card {
    background: #f9f9f9;
    border-radius: 10px;
    padding: 10px;
    width: 150px;
    text-align: center;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
.card img {
    object-fit: cover;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid #ccc;
}
.card .name {font-weight: bold; margin: 6px 0 4px 0;}
.card .score {
    color: white;
    padding: 4px 8px;
    border-radius: 8px;
    font-weight: bold;
    display: inline-block;
    font-size: 13px;
}

/* مدال */
#modal {
    display: none;
    position: fixed;
    z-index: 999;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.8);
    justify-content: center;
    align-items: center;
}
#modal img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    box-shadow: 0 0 15px white;
    cursor: pointer;
}
</style>
</head>
<body>

<h2>📋 لیست امتحانات</h2>

<div class="filters">
    <select id="lessonFilter">
        <option value="">انتخاب درس</option>
        <?php foreach($lessons as $l): ?>
            <option value="<?= $l['id'] ?>" <?= $filter_lesson==$l['id']?'selected':'' ?>><?= htmlspecialchars($l['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" id="examDateFilter" placeholder="انتخاب تاریخ">
    <button id="applyFilter">اعمال فیلتر</button>
</div>
<? print_r($exams_grouped[1]); ?>
<?php foreach($exams_grouped as $key => $exam): ?>

<div class="accordion">
    <div class="accordion-header">
        <span>📘 <?= htmlspecialchars($exam['lesson_name']) ?> - 🗓️ <?= htmlspecialchars($exam['exam_date']) ?></span>
        <span>نمایش نمرات ▾</span>
    </div>
    <div class="accordion-body">
        <img src="<?= htmlspecialchars($exam['exam_image']) ?>" class="exam-image" alt="exam image">
        <div class="students">
            <?php foreach($exam['students'] as $r): 
                $img_path = "images/students/{$r['student_id']}.jpg";
                if(!file_exists($img_path)) $img_path = "images/students/default_student.png";
            ?>
            <div class="card">
                <img src="<?= $img_path ?>" alt="<?= htmlspecialchars($r['student_name']) ?>">
                <div class="name"><?= htmlspecialchars($r['student_name']) ?></div>
                <div class="score" style="background: <?= $score_colors[$r['score']] ?? '#999' ?>;">
                    <?= $r['score'] ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal -->
<div id="modal"><img id="modal-img" src="" alt=""></div>

<script>
$(function(){
    // تاریخ شمسی
    $("#examDateFilter").persianDatepicker({
        format: 'YYYY-MM-DD',
        autoClose: true,
        observer: true,
        altField: '#examDateFilter',
        altFormat: 'YYYY-MM-DD',
        initialValue: false
    });

    // فیلتر
    $("#applyFilter").on("click", function(){
        const lesson = $("#lessonFilter").val();
        const date = $("#examDateFilter").val();
        let url = "?";
        if(lesson) url += "lesson_id="+lesson+"&";
        if(date) url += "examDate="+encodeURIComponent(date);
        window.location.href = url;
    });

    // آکاردئون باز و بسته
    $(".accordion-header").on("click", function(){
        const body = $(this).next(".accordion-body");
        $(".accordion-body").not(body).slideUp();
        body.slideToggle();
    });

    // مدال عکس امتحان
    $(".exam-image").on("click", function() {
        $("#modal-img").attr("src", $(this).attr("src"));
        $("#modal").fadeIn().css("display", "flex");
    });
    
    $("#modal").on("click", function(){
        $(this).fadeOut();
    });
});
</script>

</body>
</html>
