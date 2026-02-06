<?php

if (!defined('ABSPATH')) exit;
ob_start(); // شروع بافر خروجی

if ( ! is_admin() ) {
    return;
}

add_action('admin_menu', function(){
    add_menu_page(
        'مدیریت کلاس', 
        'مدیریت کلاس', 
        'manage_options', 
        'myclass_dashboard', 
        'myclass_render_behavior_page', 
        'dashicons-welcome-learn-more', 
        6
    );
    
    add_submenu_page(
        'myclass_dashboard', 
        'حضور و غیاب',      // عنوان صفحه
        'حضور و غیاب',      // عنوان منو
        'manage_options', 
        'myclass_attendance', 
        'myclass_render_attendance_page'
    );

    add_submenu_page(
        'myclass_dashboard', 
        'تنظیم شغل', 
        'تنظیم شغل', 
        'manage_options', 
        'myclass_jobs', 
        'myclass_render_students_job_page'
    );

    add_submenu_page(
        'myclass_dashboard', 
        'مدیریت آزمایش‌ها', 
        'مدیریت آزمایش‌ها', 
        'manage_options', 
        'myclass_experiments', 
        'myclass_render_experiments_page'
    );

    add_submenu_page(
        'myclass_dashboard', 
        'آپلود فایل دانش‌آموزان', 
        'آپلود فایل دانش‌آموزان', 
        'manage_options', 
        'myclass_upload_students', 
        'myclass_students_page'
    );

    add_submenu_page(
        'myclass_dashboard', 
        'کارنامه دانش‌آموزان', 
        'کارنامه', 
        'manage_options', 
        'myclass_report', 
        'myclass_render_student_report_page'
    );

    add_submenu_page(
        'myclass_dashboard', 
        'مدیریت گروه‌ها', 
        'مدیریت گروه‌ها', 
        'manage_options', 
        'myclass_manage_groups', 
        'myclass_render_manage_groups_page'
    );

    // ✅ اضافه کردن گزینه جدید: مدیریت امتحانات
    add_submenu_page(
        'myclass_dashboard',                  // والد
        'مدیریت امتحانات',                   // عنوان صفحه
        'مدیریت امتحانات',                   // عنوان منو
        'manage_options',                     // دسترسی
        'myclass_manage_exams',               // slug صفحه
        'myclass_render_manage_exams_page'    // callback
    );
    
    add_submenu_page(
        'myclass_dashboard', 
        'تنظیمات', 
        'تنظیمات', 
        'manage_options', 
        'myclass_settings', 
        'myclass_render_settings_page'
    );
    
    add_submenu_page(
        'myclass_dashboard',              
        'راهنما',                          // عنوان صفحه
        'راهنما',                          // عنوان منو در نوار کناری
        'manage_options',                  
        'myclass_help',                   
        'myclass_render_help_page'        
    );

});


add_action('admin_init', 'myclass_save_header_buttons_settings');

function myclass_save_header_buttons_settings() {

    if (
        isset($_POST['save_header_buttons']) &&
        isset($_POST['_wpnonce']) &&
        check_admin_referer('save_header_buttons_nonce')
    ) {
        $selected = isset($_POST['header_buttons'])
            ? array_map('sanitize_text_field', $_POST['header_buttons'])
            : [];

        update_option('myClass_header_buttons', $selected);

        add_action('admin_notices', function () {
            echo '<div class="updated notice"><p>تنظیمات منو ذخیره شد.</p></div>';
        });
    }
}



// ----------------------
// توابع مربوط به صفحات
// ----------------------


function myclass_render_attendance_page() {
    require_once myClass_TEMPLATES . 'admin_attendance.php';
}

function myclass_job_settings_callback() {
    echo '<div class="wrap">
            <h1>⚙️ تنظیم شغل دانش‌آموزان</h1>
           
        </div>';
}

function myclass_experiments_callback(): void{
    echo '<div class="wrap">
            <h1>🧪 مدیریت آزمایش‌ها</h1>
        </div>';
}

function myclass_upload_students_callback() {
//    echo '<div class="wrap"><h1>📁 آپلود فایل لیست دانش‌آموزان</h1><p>در این بخش فایل CSV یا Excel مربوط به دانش‌آموزان را آپلود کنید.</p></div>';
}

function myclass_render_student_report_page() {
    global $wpdb;

    $active_class = get_option('myClass_active_class');
    if (!$active_class) {
        echo '<div class="notice notice-warning">کلاسی فعال انتخاب نشده است.</div>';
        return;
    }

    echo '<div class="wrap"><h1>📊 کارنامه دانش‌آموزان - کلاس '.esc_html($active_class).'</h1></div>';

    $conn = $wpdb->dbh;
    $conn->set_charset('utf8mb4');

    // گرفتن دروس
    $lessons = [];
    $res = $conn->query("SELECT id, name FROM {$wpdb->prefix}lessons ORDER BY id");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $lessons[$row['id']] = $row['name'];
        }
    }

    // گرفتن دانش آموزان بر اساس کلاس فعال
    $students = [];
    $res = $conn->query("SELECT id, name FROM {$wpdb->prefix}students WHERE class='" . esc_sql($active_class) . "' ORDER BY id");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $students[$row['id']] = $row['name'];
        }
    }

    // گرفتن رفتارها
    $behaviors = [];
    $res = $conn->query("SELECT student_id, lesson_id, positive_count, negative_count FROM {$wpdb->prefix}student_behavior");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $behaviors[$row['student_id']][$row['lesson_id']] = [
                'positive' => $row['positive_count'],
                'negative' => $row['negative_count']
            ];
        }
    }

    // پیدا کردن بیشترین مثبت هر درس
    $maxPositives = [];
    foreach($behaviors as $studentId => $lessonsData){
        foreach($lessonsData as $lessonId => $values){
            $maxPositives[$lessonId] = max($maxPositives[$lessonId] ?? 0, $values['positive']);
        }
    }

    // تابع تعیین سطح
    function getLevel($positive, $negative, $maxPositive) {
        if ($maxPositive == 0) return ["بدون امتیاز", "#999999"];
        $effective = $positive - $negative;
        if ($effective <= 0) return ["نیاز به تلاش بیشتر", "#dc3545"];
        $score = ($effective / $maxPositive) * 100;
        if ($score >= 90) return ["خیلی خوب", "#28a745"];
        if ($score >= 70) return ["خوب", "#82c91e"];
        if ($score >= 50) return ["قابل قبول", "#ffc107"];
        return ["نیاز به تلاش بیشتر", "#dc3545"];
    }

    // CSS جدول
    echo '
<style>
.table-wrapper { 
    font-family: "B Nazanin", sans-serif !important;
    overflow-x:auto; 
    background:#fff; 
    padding:15px; 
    border-radius:12px; 
    box-shadow:0 6px 20px rgba(0,0,0,0.1); 
    margin-top:20px; 
}
table { 
    border-collapse: separate; 
    border-spacing: 0; 
    width:100%; 
    min-width:700px; 
    border: 1px solid #ddd;
}
th, td { 
    padding:12px 10px; 
    text-align:center; 
    font-size:15px; 
    border: 1px solid #ddd;
}
th { 
    background: linear-gradient(135deg,#6a11cb,#2575fc); 
    color:white; 
    font-weight:bold; 
}
td { 
    background:#fafafa; 
}
td .positive { color:#28a745; font-weight:bold; }
td .negative { color:#dc3545; font-weight:bold; }
td .level { margin-top:4px; font-size:13px; font-weight:bold; color:white; padding:4px 6px; border-radius:6px; display:inline-block; }
td img { width:40px; height:40px; object-fit:cover; border-radius:50%; }
td.name-col { font-weight:bold; min-width:120px; }
@media (max-width:900px){ 
    table{ min-width:100%; } 
    th, td{ padding:10px 6px; font-size:14px; } 
    td img { width:32px; height:32px; }
}
@media (max-width:600px){ 
    .table-wrapper{ padding:10px; } 
    th,td{ font-size:13px; padding:8px 5px; min-width: 100px; } 
}
</style>
    ';

    // جدول
    echo '<div class="table-wrapper"><table><thead><tr>';
    echo '<th>عکس</th><th>نام دانش آموز</th>';
    foreach($lessons as $lesson) {
        echo '<th>'.esc_html($lesson).'</th>';
    }
    echo '</tr></thead><tbody>';

    foreach($students as $sid => $sname) {
        echo '<tr>';

        // عکس دانش آموز
        $imgPath = myClass_DIR . "uploads/students/{$sid}.jpg";
        $imgUrl = file_exists($imgPath) ? myClass_UPLOADS . "students/{$sid}.jpg" : myClass_UPLOADS . "students/default_student.png";
        echo '<td><img src="'.esc_url($imgUrl).'" alt="عکس"></td>';

        echo '<td class="name-col">'.esc_html($sname).'</td>';

        // ستون درس‌ها
        foreach($lessons as $lid => $lname){
            $positive = $behaviors[$sid][$lid]['positive'] ?? 0;
            $negative = $behaviors[$sid][$lid]['negative'] ?? 0;
            $maxPositive = $maxPositives[$lid] ?? 0;
            list($levelText, $levelColor) = getLevel($positive, $negative, $maxPositive);
            echo '<td>
                <div class="positive">مثبت: '.$positive.'</div>
                <div class="negative">منفی: '.$negative.'</div>
                <div class="level" style="background:'.$levelColor.';">'.$levelText.'</div>
            </td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

function myclass_render_manage_groups_page() {
    global $wpdb;

    $table_groups = $wpdb->prefix . 'groups';
    $table_student_group = $wpdb->prefix . 'student_group';
    $table_students = $wpdb->prefix . 'students';

    // کلاس فعال پلاگین
    $active_class = get_option('myClass_active_class');

    // پیام وضعیت
    $notice = '';

    // ---------- افزودن گروه ----------
    if(isset($_POST['add_group'])){
        $name = sanitize_text_field($_POST['name']);
        $type = sanitize_text_field($_POST['type']);
        $group_number = intval($_POST['group_number']);
        $image_path = null;

        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = "group_" . time() . "." . $ext;
            $upload_dir = wp_upload_dir()['basedir'] . "/groups/";
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image_name);
            $image_path = wp_upload_dir()['baseurl'] . "/groups/" . $image_name;
        }

        $wpdb->insert($table_groups, [
            'name' => $name,
            'type' => $type,
            'group_number' => $group_number,
            'class' => $active_class, // کلاس فعال ذخیره می‌شود
            'image' => $image_path
        ]);

        $notice = 'گروه با موفقیت اضافه شد!';
    }

    // ---------- ویرایش گروه ----------
    if(isset($_POST['edit_group'])){
        $id = intval($_POST['group_id']);
        $name = sanitize_text_field($_POST['name']);
        $type = sanitize_text_field($_POST['type']);
        $group_number = intval($_POST['group_number']);
        $image_path = esc_url_raw($_POST['current_image']);

        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = "group_" . time() . "." . $ext;
            $upload_dir = wp_upload_dir()['basedir'] . "/groups/";
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image_name);
            $image_path = wp_upload_dir()['baseurl'] . "/groups/" . $image_name;
        }

        $wpdb->update($table_groups, [
            'name' => $name,
            'type' => $type,
            'group_number' => $group_number,
            'class' => $active_class, // کلاس فعال
            'image' => $image_path
        ], ['id' => $id]);

        $notice = 'گروه با موفقیت ویرایش شد!';
    }

    // ---------- حذف گروه ----------
    if(isset($_GET['delete_group'])){
        $id = intval($_GET['delete_group']);
        $wpdb->delete($table_groups, ['id' => $id]);
        $notice = 'گروه حذف شد!';
    }

    // ---------- افزودن اعضای جدید ----------
    if(isset($_POST['assign_multiple'])){
        $student_ids = array_map('intval', $_POST['student_ids']);
        $group_id = intval($_POST['group_id']);
        foreach($student_ids as $student_id){
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_student_group WHERE student_id=%d AND group_id=%d",
                $student_id, $group_id
            ));
            if(!$exists){
                $wpdb->insert($table_student_group, [
                    'student_id' => $student_id,
                    'group_id' => $group_id
                ]);
            }
        }
        $notice = 'اعضای گروه اضافه شدند!';
    }

    // ---------- حذف اعضای چندتایی ----------
    if(isset($_POST['remove_members'])){
        $ids = array_map('intval', $_POST['sg_ids']);
        if(!empty($ids)){
            $id_list = implode(',', $ids);
            $wpdb->query("DELETE FROM $table_student_group WHERE id IN ($id_list)");
        }
        $notice = 'اعضا حذف شدند!';
    }

    // دریافت اطلاعات گروه‌ها و ویرایش
    $edit_group = isset($_GET['edit_group']) ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_groups WHERE id=%d", $_GET['edit_group']), ARRAY_A) : null;

    // فقط گروه‌های کلاس فعال
    $groups = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_groups WHERE class=%s ORDER BY type, group_number",
        $active_class
    ), ARRAY_A);

    include myClass_TEMPLATES . 'manage_groups_html.php';
}

function myclass_render_manage_exams_page() {
    global $wpdb;

    $active_class = get_option('myClass_active_class');

    // گرفتن دانش آموزان کلاس فعال
    $students = $wpdb->get_results(
        $wpdb->prepare("SELECT id, name , username FROM {$wpdb->prefix}students WHERE class=%s ORDER BY id", $active_class),
        ARRAY_A
    );

    $lessons = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}lessons ORDER BY name ASC", ARRAY_A);

    include myClass_TEMPLATES . 'view_exams_admin.php';
}

function myclass_render_settings_page() {
    global $wpdb;

    $students_table = $wpdb->prefix . 'students';
    $behavior_table = $wpdb->prefix . 'student_behavior';

    // ذخیره کلاس انتخاب‌شده
    if ( isset($_POST['save_class']) && check_admin_referer('save_class_nonce') ) {
        $selected_class = sanitize_text_field($_POST['active_class']);
        update_option('myClass_active_class', $selected_class);

        echo '<div class="updated notice"><p><strong>کلاس فعال ذخیره شد.</strong></p></div>';
    }

    // ریست امتیازات
    if ( isset($_POST['reset_points']) ) {
        $wpdb->query("TRUNCATE TABLE $behavior_table");
        echo '<div class="updated notice"><p><strong>امتیازات این ماه ریست شد.</strong></p></div>';
    }

    $classes = $wpdb->get_col("
        SELECT DISTINCT class 
        FROM $students_table 
        WHERE class IS NOT NULL AND class != ''
        ORDER BY class ASC
    ");

    $active_class = get_option('myClass_active_class', '');

    if ( empty($active_class) && ! empty($classes) ) {
        $active_class = $classes[0];
        update_option('myClass_active_class', $active_class);
    }
    ?>

    <div class="wrap">
        <h1>⚙️ تنظیمات افزونه</h1>

        <!-- انتخاب کلاس -->
        <form method="post">
            <?php wp_nonce_field('save_class_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th>کلاس فعال</th>
                    <td>
                        <select name="active_class" required>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo esc_attr($class); ?>"
                                    <?php selected($active_class, $class); ?>>
                                    <?php echo esc_html($class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>

            <p>
                <input type="submit" name="save_class"
                       class="button button-primary"
                       value="ذخیره کلاس">
            </p>
        </form>

        <hr>

        <!-- ریست امتیازات -->
        <form method="post" style="padding-bottom: 15px">
            <p> <strong>ریست امتیازات:</strong> با کلیک روی دکمه زیر تمام امتیازات حذف می‌شود. </p>
            <input type="submit" name="reset_points" class="button button-danger" value="ریست امتیازات" onclick="return confirm('آیا مطمئن هستید؟');">
        </form>

        <hr>

        <h2>تنظیم نمایش منوی بالا</h2>

        <form method="post">
            <?php wp_nonce_field('save_header_buttons_nonce'); ?>

            <?php
            $all_buttons   = myClass_header_buttons_map();
            $activeButtons = get_option(
                'myClass_header_buttons',
                myClass_default_header_buttons()
            );
            ?>

            <table class="form-table">
                <?php foreach ($all_buttons as $key => $btn): ?>
                    <tr>
                        <th><?php echo esc_html($btn['label']); ?></th>
                        <td>
                            <input type="checkbox"
                                   name="header_buttons[]"
                                   value="<?php echo esc_attr($key); ?>"
                                <?php checked(in_array($key, $activeButtons)); ?>>
                            نمایش داده شود
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p>
                <input type="submit"
                       name="save_header_buttons"
                       class="button button-primary"
                       value="ذخیره تنظیمات منو">
            </p>
        </form>
    </div>
    <?php
}

function myclass_render_help_page() {
    ?>
    <div class="wrap">
        <h1>📘 راهنمای استفاده از افزونه myClass</h1>
        <p>در این صفحه شورتکدهای هر برگه نمایش داده شده است.</p>

        <table class="widefat striped" style="max-width:950px; margin-top:20px;">
            <thead>
                <tr>
                    <th>نام برگه</th>
                    <th>شورتکد</th>
                    <th>توضیحات</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>myClass</td>
                    <td><code>[myClass]</code></td>
                    <td>صفحه اصلی افزونه شامل داشبورد و بخش‌های مختلف مدیریت کلاس</td>
                </tr>
                <tr>
                    <td>آزمایش ها</td>
                    <td><code>[experiments]</code></td>
                    <td>ثبت فعالیت‌ها و نتایج آزمایش‌ها</td>
                </tr>
                <tr>
                    <td>امتیاز ها</td>
                    <td><code>[students_stars]</code></td>
                    <td>نمایش امتیازهای دانش آموزان </td>
                </tr>
                <tr>
                    <td>حضوروغیاب</td>
                    <td><code>[attendance]</code></td>
                    <td>فرم ثبت حضور و غیاب دانش‌آموزان</td>
                </tr>
                <tr>
                    <td>صفحه دانش آموز</td>
                    <td><code>[student_dashboard]</code></td>
                    <td>داشبورد اختصاصی دانش‌آموز شامل عملکرد و نمرات</td>
                </tr>
                <tr>
                    <td>گالری</td>
                    <td><code>[student_gallery]</code></td>
                    <td> نمایش نقاشی دانش آموزان </td>
                </tr>
                <tr>
                    <td>گروه های دانش آموزی</td>
                    <td><code>[view_group]</code></td>
                    <td>مشاهده گروه‌ها و اعضای هر گروه</td>
                </tr>
                <tr>
                    <td>لیست امتحانات</td>
                    <td><code>[view_exams]</code></td>
                    <td>نمایش و مدیریت امتحانات ثبت شده</td>
                </tr>
                <tr>
                    <td>نفرات برتر</td>
                    <td><code>[top_students]</code></td>
                    <td>نمایش برترین دانش‌آموزان بر اساس ستاره‌ها</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:30px; padding:15px; background:#f9f9f9; border:1px solid #ddd; border-radius:8px;">
            <p><strong>📌 نکته:</strong> با استفاده از شورتکدهای بالا می‌توانید محتوای هر بخش را در برگه‌های وردپرس نمایش دهید.</p>
        </div>
    </div>
    <?php
}

ob_end_flush(); // ارسال بافر خروجی
