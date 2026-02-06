<?php
defined('ABSPATH') or die('No direct access');

global $wpdb;

// جدول‌ها
$table_exams    = $wpdb->prefix . 'exams';
$table_students = $wpdb->prefix . 'students';
$table_lessons  = $wpdb->prefix . 'lessons';

// تبدیل اعداد فارسی به انگلیسی
function fa2en($str){
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($fa,$en,$str);
}

// گرفتن کلاس‌های موجود
$classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_students ORDER BY class");

// فیلترها
$filter_lesson = intval($_GET['lesson_id'] ?? 0);
$filter_class  = sanitize_text_field($_GET['class'] ?? '');
$filter_date   = fa2en($_GET['examDate'] ?? '');

// لیست دروس
$lessons = $wpdb->get_results( "SELECT id, name FROM $table_lessons ORDER BY name ASC", ARRAY_A );

// ساخت کوئری
$query = "SELECT e.*, s.name AS student_name, s.id AS student_id, s.class AS student_class, l.name AS lesson_name
          FROM $table_exams e
          JOIN $table_students s ON e.student_id = s.id
          JOIN $table_lessons l ON e.lesson_id = l.id
          WHERE 1=1";

if($filter_lesson){
    $query .= $wpdb->prepare(" AND e.lesson_id=%d", $filter_lesson);
}
if($filter_class){
    $query .= $wpdb->prepare(" AND s.class=%s", $filter_class);
}
if($filter_date){
    $query .= $wpdb->prepare(" AND e.exam_date=%s", $filter_date);
}

$query .= " ORDER BY e.lesson_id, e.exam_date, s.id ASC";

$exams = $wpdb->get_results($query, ARRAY_A);

// گروه‌بندی بر اساس درس و تاریخ
$exams_grouped = [];
foreach($exams as $r){
    $key = $r['lesson_name'].'|'.$r['exam_date'].'|'.$r['student_class'];
    $exams_grouped[$key]['lesson_name'] = $r['lesson_name'];
    $exams_grouped[$key]['exam_date']   = $r['exam_date'];
    $exams_grouped[$key]['class']       = $r['student_class'];
    $exams_grouped[$key]['exam_image']  = !empty($r['exam_image']) ? $r['exam_image'] : site_url('wp-content/uploads/myclass_exams/default_exam.jpg');
    $exams_grouped[$key]['students'][]  = $r;
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

<div class="view-exam-container">

    <!-- فیلتر کلاس و درس -->
    <div class="view-exam-filters">
        <form method="get">
            <!-- حفظ page برای مدیریت وردپرس -->
            <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? '') ?>">

            <select name="lesson_id">
                <option value="">انتخاب درس</option>
                <?php foreach($lessons as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= $filter_lesson==$l['id']?'selected':'' ?>><?= esc_html($l['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="class">
                <option value="">انتخاب کلاس</option>
                <?php foreach($classes as $cls): ?>
                    <option value="<?= esc_attr($cls) ?>" <?= $filter_class==$cls?'selected':'' ?>><?= esc_html($cls) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">اعمال فیلتر</button>
        </form>
    </div>

    <!-- نمایش امتحانات -->
    <?php foreach($exams_grouped as $key => $exam): ?>
        <div class="view-exam-accordion">
            <div class="view-exam-accordion-header">
                <span>📘 <?= esc_html($exam['lesson_name']) ?> - 🗓️ <?= esc_html($exam['exam_date']) ?> - کلاس: <?= esc_html($exam['class']) ?></span>
                <span>نمایش نمرات ▾</span>
            </div>
            <div class="view-exam-accordion-body">
                <img src="<?= esc_url($exam['exam_image']) ?>" class="view-exam-image" alt="exam image">
                <div class="view-exam-students">
                    <?php foreach($exam['students'] as $r):
                        $img_path = myClass_UPLOADS . "images/students/{$r['student_id']}.jpg";
                        if(!file_exists(myClass_INC_DIR . "students/{$r['student_id']}.jpg")){
                            $img_path = myClass_UPLOADS . "students/default_student.png";
                        }
                        ?>
                        <div class="view-exam-card">
                            <img src="<?= esc_url($img_path) ?>" alt="<?= esc_html($r['student_name']) ?>">
                            <div class="view-exam-name"><?= esc_html($r['student_name']) ?></div>
                            <div class="view-exam-score" style="background: <?= esc_attr($score_colors[$r['score']] ?? '#999') ?>;">
                                <?= esc_html($r['score']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="view-exam-modal"><img id="view-exam-modal-img" src="" alt=""></div>
