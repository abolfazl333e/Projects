<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

$lesson_id = intval($_GET['lesson_id'] ?? 0);
$exam_date = $_GET['exam_date'] ?? '';
if(!$lesson_id || !$exam_date) exit("پارامتر نامعتبر است.");

// گرفتن نمرات دانش‌آموزان برای این درس و تاریخ
$q = "SELECT s.name AS student_name, e.score, s.id AS student_id
      FROM exams e
      JOIN students s ON e.student_id = s.id
      WHERE e.lesson_id=? AND e.exam_date=?";
$stmt = $conn->prepare($q);
$stmt->bind_param("is", $lesson_id, $exam_date);
$stmt->execute();
$res = $stmt->get_result();

$score_colors = [
    'خیلی خوب' => '#27ae60',
    'خوب' => '#82e0aa',
    'قابل قبول' => '#f39c12',
    'نیاز به تلاش بیشتر' => '#e74c3c',
    'غایب' => '#7f8c8d'
];
?>

<table class="students-table">
<thead>
<tr>
    <th>عکس</th>
    <th>نام دانش‌آموز</th>
    <th>نمره</th>
</tr>
</thead>
<tbody>
<?php while($r = $res->fetch_assoc()):
    $img_path = "images/students/{$r['student_id']}.jpg";
    if(!file_exists($img_path)) $img_path = "images/students/default_student.png";
?>
<tr>
    <td><img src="<?= $img_path ?>" style="width:45px;height:45px;border-radius:50%;object-fit:cover;"></td>
    <td><?= htmlspecialchars($r['student_name']) ?></td>
    <td style="background: <?= $score_colors[$r['score']] ?? '#999' ?>; color:white; border-radius:5px;">
        <?= htmlspecialchars($r['score']) ?>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<hr>

<div class="exam-images">
<?php
// مسیر عکس سوالات مثلاً images/exams/{lesson_id}/{exam_date}/
$folder = "images/exams/{$lesson_id}/{$exam_date}";
if(is_dir($folder)){
    $files = glob("$folder/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    foreach($files as $file){
        echo "<img src='$file' alt='سوال'>";
    }
} else {
    echo "<p style='text-align:center;color:#888;'>عکسی برای این امتحان ثبت نشده است.</p>";
}
?>
</div>
