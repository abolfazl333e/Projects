<?php
global $wpdb, $exp;

$prefix = $wpdb->prefix;
$lessons_table = $prefix . "lessons";
$experiments_table = $prefix . "experiments";
$images_table = $prefix . "experiment_images";
$students_table = $prefix . "students"; // برای کلاس دانش‌آموزان

// کلاس‌های موجود
$classes = $wpdb->get_col("SELECT DISTINCT class FROM $students_table ORDER BY class");

// انتخاب درس و کلاس از GET
$selectedLesson = isset($_GET['lesson']) ? intval($_GET['lesson']) : 0;
$selectedClass  = isset($_GET['class']) ? sanitize_text_field($_GET['class']) : '';

// دروس
$lessons_raw = $wpdb->get_results("SELECT id, name FROM $lessons_table", ARRAY_A);
$lessons = [];
foreach ($lessons_raw as $row) {
    $lessons[$row['id']] = $row['name'];
}

// آزمایش‌ها
$where = [];
$params = [];

if ($selectedLesson > 0) {
    $where[] = "lesson_id = %d";
    $params[] = $selectedLesson;
}

if ($selectedClass) {
    $where[] = "class = %s";
    $params[] = $selectedClass;
}

$where_sql = '';
if(!empty($where)){
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$query = "SELECT * FROM $experiments_table $where_sql ORDER BY created_at DESC";
if(!empty($params)){
    $experiments = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);
} else {
    $experiments = $wpdb->get_results($query, ARRAY_A);
}

// اضافه کردن تصاویر هر آزمایش
foreach ($experiments as &$exp) {
    $exp_id = intval($exp['id']);
    $images = $wpdb->get_col(
        $wpdb->prepare("SELECT filename FROM $images_table WHERE experiment_id = %d", $exp_id )
    );
    $exp['images'] = $images;
}
unset($exp); // جلوگیری از ارجاع ناخواسته
?>

<div class="experiments_container">
    <h2>آزمایش‌ها</h2>
    <div class="filter-box">
        <form method="GET">
            <label for="lesson">انتخاب درس:</label>
            <select name="lesson" id="lesson" onchange="this.form.submit()">
                <option value="0">همه دروس</option>
                <?php foreach($lessons as $id=>$name): ?>
                    <option value="<?= esc_attr($id) ?>" <?= selected($id, $selectedLesson, false) ?>><?= esc_html($name) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="class">انتخاب کلاس:</label>
            <select name="class" id="class" onchange="this.form.submit()">
                <option value="">همه کلاس‌ها</option>
                <?php foreach($classes as $cls): ?>
                    <option value="<?= esc_attr($cls) ?>" <?= selected($cls, $selectedClass, false) ?>><?= esc_html($cls) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if(empty($experiments)): ?>
        <p class="no-exp">هیچ آزمایشی برای این درس و کلاس ثبت نشده است 🧪</p>
    <?php endif; ?>

    <?php foreach($experiments as $index=>$exp): ?>
        <div class="experiments_card">
            <h3>🧫 <?= esc_html($exp['title']) ?></h3>
            <p><strong>درس:</strong> <?= esc_html($lessons[$exp['lesson_id']] ?? '-') ?></p>
            <p><strong>کلاس:</strong> <?= esc_html($exp['class'] ?? '-') ?></p>
            <div class="images" data-exp="<?= esc_attr($index) ?>" data-title="<?= esc_attr($exp['title']) ?>">
                <?php foreach($exp['images'] as $img): ?>
                    <img src="<?= esc_url(myClass_UPLOADS . 'experiments/' . $img) ?>" alt="" class="experiment-img clickable-img">
                <?php endforeach; ?>
            </div>
            <p><strong>توضیح:</strong> <?= esc_html($exp['results']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="imgModal" class="modal">
    <span class="close">&times;</span>
    <span class="nav-btn next-btn">&#10094;</span>
    <img class="modal-content" id="modalImage">
    <span class="nav-btn prev-btn">&#10095;</span>
    <div class="caption" id="captionText"></div>
</div>
