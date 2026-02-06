<?php
require_once 'inc/db.php';
require_once 'inc/jdf.php';
$conn->set_charset('utf8mb4');

// گرفتن درس‌ها
$lessonsRes = $conn->query("SELECT id, name FROM lessons ORDER BY name ASC");
$lessons = [];
while($row = $lessonsRes->fetch_assoc()){
    $lessons[] = $row;
}

// درس انتخاب شده از GET
$selected_lesson = $_GET['lesson'] ?? null;

// گرفتن لیست دانش‌آموزان
$students = [];
if($selected_lesson){
    $stmt = $conn->prepare("
        SELECT s.id, s.name, 
               COALESCE(b.positive_count,0) as positive_count,
               COALESCE(b.negative_count,0) as negative_count
        FROM students s
        LEFT JOIN student_behavior b 
        ON s.id=b.student_id AND b.lesson_id=?
        ORDER BY s.id ASC
    ");
    $stmt->bind_param("i", $selected_lesson);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()){
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ثبت امتیاز دانش آموزان</title>
<style>
@font-face {
    font-family: 'B Nazanin';
    src: url('fonts/B_Nazanin.woff2') format('woff2'),
         url('fonts/B_Nazanin.woff') format('woff');
}
body {
    font-family: 'B Nazanin', sans-serif;
    font-weight: bold;
    background: linear-gradient(120deg,#a1c4fd,#c2e9fb);
    margin: 0;
    padding: 20px;
}
h2 {
    text-align: center;
    color: #264653;
    margin-bottom: 20px;
}
select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 16px;
    margin-bottom: 20px;
}
.table-container {
    max-width: 900px;
    margin: auto;
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
th, td {
    padding: 12px 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}
th {
    background: #007bff;
    color: #fff;
    font-size: 16px;
}
tr:nth-child(even) {
    background: #f0f8ff;
}
input[type=number] {
    width: 60px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    text-align: center;
}
.controls {
    text-align: center;
    margin-top: 20px;
}
.btn-save {
    padding: 10px 20px;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}
.btn-save:hover {
    background: #1e7e34;
}
@media (max-width: 600px) {
    th, td {
        padding: 10px 6px;
        font-size: 14px;
    }
    input[type=number] {
        width: 50px;
    }
    .btn-save {
        width: 100%;
    }
}
</style>
</head>
<body>

<h2>ثبت امتیاز دانش آموزان</h2>

<!-- انتخاب درس -->
<div style="text-align:center; margin-bottom:20px;">
<form method="get">
    <select name="lesson" onchange="this.form.submit()">
        <option value="">-- انتخاب درس --</option>
        <?php foreach($lessons as $lesson): ?>
            <option value="<?= $lesson['id'] ?>" <?= $selected_lesson==$lesson['id']?'selected':'' ?>>
                <?= htmlspecialchars($lesson['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>
</div>

<?php if($selected_lesson): ?>
<div class="table-container">
<form id="behaviorForm">
    <input type="hidden" name="lesson_id" value="<?= $selected_lesson ?>">
    <table>
        <thead>
            <tr>
                <th>نام دانش‌آموز</th>
                <th>مثبت‌ها</th>
                <th>منفی‌ها</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><input type="number" name="positive[<?= $s['id'] ?>]" value="<?= $s['positive_count'] ?>"></td>
                <td><input type="number" name="negative[<?= $s['id'] ?>]" value="<?= $s['negative_count'] ?>"></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="controls">
        <button type="button" class="btn-save" onclick="saveBehavior()">ثبت</button>
    </div>
</form>
</div>
<?php endif; ?>

<script>
function saveBehavior(){
    const form = document.getElementById('behaviorForm');
    if(!form) return;
    
    const lessonId = form.lesson_id.value;
    if(!lessonId){
        alert('لطفا ابتدا درس را انتخاب کنید');
        return;
    }

    let formData = new FormData(form);
    // console.log(formData.lesson_id)
    fetch('api/save_behavior.php', {
        method:'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
        if(data.ok){
            alert(data.msg || 'ثبت شد');
        } else {
            alert(data.msg || 'خطا در ثبت رفتار');
        }
      }).catch(err=>{
        alert('خطا در ثبت رفتار: ' + err);
      });
}
</script>

</body>
</html>
