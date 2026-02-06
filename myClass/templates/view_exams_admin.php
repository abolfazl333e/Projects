<?php
defined('ABSPATH') or die('No direct access');

$score_labels = ['نیاز به تلاش بیشتر','قابل قبول','خوب','خیلی خوب','غایب'];
$score_colors = [
    'خیلی خوب' => '#27ae60',
    'خوب' => '#82e0aa',
    'قابل قبول' => '#f39c12',
    'نیاز به تلاش بیشتر' => '#e74c3c',
    'غایب' => '#7f8c8d'
];
?>

<div class="view-exams-admin-container">
    <h2>ثبت نمرات امتحان (کلاس: <?= esc_html($active_class) ?>)</h2>
    <form id="view-exams-admin-examForm" enctype="multipart/form-data">
        <div class="myclass-filters">
            <label>نام درس:</label>
            <select name="lesson_id" id="myclass-lessonSelect" required>
                <option value="">انتخاب درس</option>
                <?php foreach($lessons as $l): ?>
                    <option value="<?= esc_attr($l['id']) ?>"><?= esc_html($l['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>تاریخ امتحان:</label>
            <input type="text" id="myclass-examDate" name="examDate" required>
            <input type="hidden" id="myclass-examWeekday" name="examWeekday">

            <label>عکس امتحان:</label>
            <input type="file" name="exam_images[]" id="myclass-exam_image" accept="image/*" multiple>
        </div>

        <div class="view-exams-admin-cards">
            <?php foreach ($students as $s):

                $student_file = myClass_DIR . "uploads/students/{$s['username']}.jpg";
                $student_url  = myClass_UPLOADS . "students/{$s['username']}.jpg";

                $img_path = file_exists($student_file) ? $student_url : myClass_UPLOADS . "students/default_student.png";

                ?>
                <div class="view-exams-admin-card" data-student="<?= esc_attr($s['id']) ?>">
                    <img src="<?= esc_url($img_path) ?>" alt="<?= esc_html($s['name']) ?>">
                    <div class="view-exams-admin-name"><?= esc_html($s['name']) ?></div>
                    <div class="view-exams-admin-scores">
                        <?php foreach($score_labels as $sc): ?>
                            <div class="view-exams-admin-score-box" data-score="<?= esc_attr($sc) ?>" style="background: <?= esc_attr($score_colors[$sc]) ?>;">
                                <?= esc_html($sc) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <input type="hidden" name="class" value="<?= esc_attr($active_class) ?>">

        <div class="view-exams-admin-btns">
            <button type="submit" id="submitExam">ثبت نمرات</button>
        </div>

    </form>
</div>
