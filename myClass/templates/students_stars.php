<?php

global $wpdb;
$table_students = $wpdb->prefix . 'students';
$table_behavior = $wpdb->prefix . 'student_behavior';

// گرفتن کلاس‌های موجود
$classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_students ORDER BY class");

// کلاس انتخاب شده از GET
$selectedClass = isset($_GET['class']) ? sanitize_text_field($_GET['class']) : '';

// گرفتن لیست دانش‌آموزان با فیلتر کلاس (اگر انتخاب شده)
$where = '';
$params = [];
if ($selectedClass) {
    $where = "WHERE class = %s";
    $params[] = $selectedClass;
}

$query = "SELECT * FROM $table_students $where ORDER BY id ASC";
$students = !empty($params) ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A) : $wpdb->get_results($query, ARRAY_A);

foreach ($students as $index => $s) {
    $students[$index]['balance'] = 0;
    $students[$index]['calculated_stars'] = 0;
    $students[$index]['manual_stars'] = 0;
}

// گرفتن رفتار دانش‌آموزان
$behavior = $wpdb->get_results("
    SELECT student_id,
           SUM(positive_count) AS total_positive,
           SUM(negative_count) AS total_negative,
           SUM(CASE WHEN lesson_id = 9 THEN positive_count ELSE 0 END) AS manual_stars
    FROM (
        SELECT student_id, lesson_id, MAX(positive_count) AS positive_count, MAX(negative_count) AS negative_count
        FROM $table_behavior
        GROUP BY student_id, lesson_id
    ) AS b
    GROUP BY student_id
", ARRAY_A);

foreach ($behavior as $b) {
    foreach ($students as $index => $s) {
        if ($s['id'] == $b['student_id']) {
            $balance = $b['total_positive'] - $b['total_negative'] - $b['manual_stars'];
            $students[$index]['balance'] = $balance;
            $students[$index]['calculated_stars'] = $balance > 0 ? floor($balance / 10) : 0;
            $students[$index]['manual_stars'] = $b['manual_stars'];
        }
    }
}
unset($s); // پاک کردن ارجاع احتمالی

?>

<div class="students_stars">
    <h1 style="text-align:center;">⭐️ امتیازات دانش آموزان ⭐️</h1>

    <!-- فیلتر کلاس -->
    <div class="filter-box">
        <form method="GET">
            <label for="class">انتخاب کلاس:</label>
            <select name="class" id="class" onchange="this.form.submit()">
                <option value="">همه کلاس‌ها</option>
                <?php foreach($classes as $cls): ?>
                    <option value="<?= esc_attr($cls) ?>" <?= selected($cls, $selectedClass, false) ?>><?= esc_html($cls) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <table class="students_stars_table">
        <tr>
            <th>ردیف</th>
            <th>نام</th>
            <th>کلاس</th>
            <th>شغل</th>
            <th>تراز مثبت-منفی</th>
            <th>ستاره‌ها</th>
        </tr>

        <?php
        $i = 1;
        foreach ($students as $s) {
            $total_stars = $s['calculated_stars'] + $s['manual_stars'];
            echo "<tr>
                <td>{$i}</td>
                <td>".htmlspecialchars($s['name'])."</td>
                <td>".htmlspecialchars($s['class'])."</td>
                <td>".htmlspecialchars($s['job'])."</td>
                <td style='direction:ltr;'>".($s['balance'] ?? 0)."</td>
                <td class='stars'>";
            for ($j = 0; $j < $total_stars; $j++) {
                echo "<i class='fa fa-star'></i>";
            }
            echo "</td></tr>";
            $i++;
        }
        ?>
    </table>
</div>
