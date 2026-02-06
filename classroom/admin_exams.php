<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// گرفتن لیست دانش‌آموزان و دروس
$students_res = $conn->query("SELECT id, name FROM students ORDER BY id ASC");
$students = [];
while($s = $students_res->fetch_assoc()){
    $students[] = $s;
}

$lessons_res = $conn->query("SELECT id, name FROM lessons ORDER BY name ASC");
$lessons = [];
while($l = $lessons_res->fetch_assoc()){
    $lessons[] = $l;
}

$score_labels = ['نیاز به تلاش بیشتر','قابل قبول','خوب','خیلی خوب','غایب'];
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
<title>ثبت نمرات امتحان</title>
<link href="https://cdn.jsdelivr.net/npm/vazirmatn@33.003/font-face.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.css">
<style>
body { font-family: B Nazanin, sans-serif; font-weight:bold; background: #f5f6fa; direction: rtl; padding: 20px; }
.container {max-width: 1000px; margin: auto;}
h2 {text-align: center; margin-bottom: 20px;}
.filters {text-align: center; margin-bottom: 20px;}
.filters select, .filters input, .filters button { padding: 5px 10px; margin: 0 5px; border-radius: 5px; border: 1px solid #ccc; }
.cards {display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;}
.card { background: #fff; border-radius: 10px; padding: 15px; width: 180px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center; transition: transform 0.2s; }
.card:hover {transform: translateY(-5px);}
.card img { width: 80px; height: 80px; object-fit: contain; border-radius: 50%; margin-bottom: 10px; border: 2px solid #ccc; }
.card .name {font-weight: bold; margin-bottom: 5px;}
.card .scores {display: flex; justify-content: space-between; cursor: pointer; margin-bottom: 5px;flex-direction:column;}
.card .score-box { flex: 1; margin: 2px; padding: 8px 0; color: white; font-weight: bold; border-radius: 5px; transition: transform 0.2s; }
.card .score-box.selected {transform: scale(1.1); box-shadow: 0 0 5px #000;}
.btns{margin:0 auto !important;display:flex;width:60%;justify-content:space-around;}
.btns button {font-family:B Nazanin !important;cursor: pointer;}
@media(max-width:600px){ 
    .card {width: 140px;} 
    .card img {width: 60px; height: 60px;}
}
</style>
</head>
<body>
<div class="container">
<h2>ثبت نمرات امتحان</h2>

<form id="examForm" enctype="multipart/form-data">
<div class="filters">
    <label>نام درس:</label>
    <select name="lesson_id" id="lessonSelect" required>
        <option value="">انتخاب درس</option>
        <?php foreach($lessons as $l): ?>
            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>تاریخ امتحان:</label>
    <input type="text" id="examDate" name="examDate" required>
    <input type="hidden" id="examWeekday" name="examWeekday">

    <!-- 🔹 آپلود عکس امتحان -->
    <label>عکس امتحان:</label>
    <input type="file" name="exam_image[]" id="exam_image" accept="image/*" multiple>
</div>

<div class="cards">
<?php foreach($students as $s):
    $img_path = "images/students/{$s['id']}.jpg";
    if(!file_exists($img_path)) $img_path = "images/students/default_student.png";
?>
<div class="card" data-student="<?= $s['id'] ?>">
    <img src="<?= $img_path ?>" alt="<?= htmlspecialchars($s['name']) ?>">
    <div class="name"><?= htmlspecialchars($s['name']) ?></div>
    <div class="scores">
        <?php foreach($score_labels as $sc): ?>
            <div class="score-box" data-score="<?= $sc ?>" style="background: <?= $score_colors[$sc] ?>;">
                <?= $sc ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<br>
<div class="btns">
    <button type="submit">ثبت نمرات</button>
    <button type="button" class="header-btn" onclick="window.open('admin_view_exams.php','_blank')">ویرایش</button>
</div>
</form>
</div>

<script>
$(function(){
    // datepicker
    $("#examDate").persianDatepicker({
        format: 'YYYY-MM-DD',
        autoClose: true,
        onSelect: function(unix){
            const days = ['شنبه','یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنج‌شنبه','جمعه'];
            const day = new persianDate(unix).day();
            $("#examWeekday").val(days[day]);
        }
    });

    // انتخاب نمره
    $(".score-box").on("click", function(){
        const parent = $(this).closest(".card");
        parent.find(".score-box").removeClass("selected");
        $(this).addClass("selected");
        if(parent.find("input.score-input").length==0){
            parent.append('<input type="hidden" class="score-input" name="score_'+parent.data("student")+'" value="'+$(this).data("score")+'">');
        } else {
            parent.find("input.score-input").val($(this).data("score"));
        }
    });

    // ارسال فرم
    $("#examForm").on("submit", function(e){
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: "api/save_exam.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res){
                alert(res.message);
                if(res.success && res.redirect) window.location.href = res.redirect;
            }
        });
    });
});
</script>
</body>
</html>
