<?php
global $wpdb;

$table_groups = $wpdb->prefix . 'groups';
$table_student_group = $wpdb->prefix . 'student_group';
$table_students = $wpdb->prefix . 'students';

// گرفتن کلاس‌های موجود
$classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_students ORDER BY class");

// کلاس انتخاب شده از GET
$selectedClass = isset($_GET['class']) ? sanitize_text_field($_GET['class']) : '';

// گرفتن گروه‌ها
$where = '';
$params = [];
if ($selectedClass) {
    $where = "WHERE class = %s";
    $params[] = $selectedClass;
}

$query = "SELECT * FROM $table_groups $where ORDER BY 
    CASE 
        WHEN type = 'درسی' THEN 1
        WHEN type = 'ورزشی' THEN 2
        ELSE 3
    END,
    group_number ASC";

$groups = !empty($params) ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A) : $wpdb->get_results($query, ARRAY_A);
?>

<div class="view-group-container">
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

    <div class="group-grid">
        <?php foreach($groups as $g):
            // گرفتن اعضای گروه فقط برای کلاس انتخاب شده (اگر انتخاب شده)
            $members_query = "SELECT s.name 
            FROM $table_student_group sg 
            JOIN $table_students s ON sg.student_id = s.id 
            WHERE sg.group_id = %d";
            $members_params = [$g['id']];

            if ($selectedClass) {
                $members_query .= " AND s.class = %s";
                $members_params[] = $selectedClass;
            }

            $members_query .= " ORDER BY s.name";
            $members = $wpdb->get_col($wpdb->prepare($members_query, ...$members_params));
            ?>
            <div class="view-group-card">
                <?php if(!empty($g['image'])): ?>
                    <img src="<?php echo esc_url($g['image']); ?>" class="group-img">
                <?php endif; ?>
                <h3><?php echo esc_html($g['name']); ?></h3>
                <p>شماره گروه: <?php echo esc_html($g['group_number']); ?></p>
                <p>نوع گروه: <?php echo esc_html($g['type']); ?></p>
                <?php if(!empty($members)): ?>
                    <div class="view-group-members">
                        <strong>اعضا :</strong>
                        <ul>
                            <?php foreach($members as $m) echo "<li>".esc_html($m)."</li>"; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
