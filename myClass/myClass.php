<?php
/**
 * Plugin Name:        myClass
 * Description:        Teachers plugin.
 * Version:            1.1.0
 * Requires PHP:       7.4
 * Author:             Abolfazl Emami
 */

defined('ABSPATH') || exit('<h1>Access Denied!</h1>');

session_start();

// بارگذاری فایل‌های ضروری
require_once ABSPATH . 'wp-admin/includes/file.php';
include_once plugin_dir_path(__FILE__) . 'Constants.php';
require_once myClass_INC_DIR. 'admin-menus.php';
require_once myClass_TEMPLATES. 'manage_experiments.php';
require_once myClass_TEMPLATES. 'set_jobs.php';
require_once myClass_TEMPLATES. 'students_import.php';
require_once myClass_TEMPLATES. 'behavior.php';
require_once myClass_TEMPLATES. 'students_report.php';

/************************************************************************************
 * ایجاد جداول دیتابیس در زمان فعال‌سازی افزونه
 ************************************************************************************/
function myClass_activation(): void{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [];

    $tables[] = "CREATE TABLE {$wpdb->prefix}admin (
        id int(11) NOT NULL AUTO_INCREMENT,
        username varchar(100) NOT NULL,
        password_hash varchar(255) NOT NULL,
        name varchar(200) NOT NULL,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY username (username)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}attendance (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        date date NOT NULL,
        time time DEFAULT NULL,
        status varchar(20) NOT NULL,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY student_date (student_id, date),
        KEY date (date)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}attendance_log (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        rfid_tag varchar(255) NOT NULL,
        timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        info varchar(255) NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // جدول experiments آپدیت شده
    $tables[] = "CREATE TABLE {$wpdb->prefix}experiments (
        id int(11) NOT NULL AUTO_INCREMENT,
        title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        lesson_id int(11) NOT NULL,
        results text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        class varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}experiment_images (
        id int(11) NOT NULL AUTO_INCREMENT,
        experiment_id int(11) NOT NULL,
        filename varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}lessons (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}students (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        rfid_tag varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        class varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        job varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
        username varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
        password_hash varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
        must_change_password tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY uq_students_username (username)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}student_behavior (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        lesson_id int(11) NOT NULL,
        positive_count int(11) DEFAULT 0,
        negative_count int(11) DEFAULT 0,
        date date NOT NULL,
        updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY student_lesson_unique (student_id, lesson_id),
        KEY lesson_id (lesson_id)
    ) $charset_collate;";

    // جدول exams آپدیت شده
    $tables[] = "CREATE TABLE {$wpdb->prefix}exams (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        lesson_id int(11) NOT NULL,
        score enum('نیاز به تلاش بیشتر', 'قابل قبول', 'خوب', 'خیلی خوب', 'غایب') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        exam_date date NOT NULL,
        weekday varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        class varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        exam_image text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
        created_at timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (id),
        KEY student_id (student_id),
        KEY lesson_id (lesson_id)
    ) $charset_collate;";

    // جدول groups آپدیت شده
    $tables[] = "CREATE TABLE {$wpdb->prefix}groups (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        type enum('ورزشی','درسی') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        class varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        group_number int(11) NOT NULL DEFAULT 1,
        image varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}student_group (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        group_id int(11) NOT NULL,
        PRIMARY KEY (id),
        KEY student_id (student_id),
        KEY group_id (group_id)
    ) $charset_collate;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}top_students_archive (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        year int(11) NOT NULL,
        month int(11) NOT NULL,
        balance int(11) DEFAULT 0,
        calculated_stars int(11) DEFAULT 0,
        manual_stars int(11) DEFAULT 0,
        total_stars int(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY year_month_student (year, month, student_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach ($tables as $sql) {
        dbDelta($sql);
    }

    if ( get_option('myClass_header_buttons') === false ) {
        add_option(
            'myClass_header_buttons',
            myClass_default_header_buttons()
        );
    }
}

function myClass_default_header_buttons() {
    return [
        'points',
        'top_students',
        'groups',
        'exams',
        'labs',
        'attendance',
    ];
}

function myClass_deactivation(): void {
   //nothing
}


function myClass_uninstall(): void {
    global $wpdb;

    // لیست جداول پلاگین
    $tables = [
        'admin',
        'attendance',
        'attendance_log',
        'experiments',
        'experiment_images',
        'lessons',
        'students',
        'student_behavior',
        'exams',
        'groups',
        'student_group',
        'top_students_archive'
    ];

    foreach ($tables as $table) {
        $table_name = $wpdb->prefix . $table;
        $wpdb->query("DROP TABLE IF EXISTS `$table_name`;");
    }
}

register_uninstall_hook(__FILE__, 'myClass_uninstall');

register_activation_hook(__FILE__, function () {
    myClass_activation();
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, 'myClass_deactivation');

/************************************************************************************
 * بارگذاری استایل‌ها و اسکریپت‌ها
 ************************************************************************************/
function load_myClass_assets(): void {
    $plugin_url = myClass_URL;

    // CSS
    $css_files = [
        'myClass_select2'   => 'select2.css',
        'myClass_mainCss'   => 'main.css',
        'myClass_bootstrap' => 'bootstrap.min.css',
    ];
    foreach ($css_files as $handle => $file) {
        $path = myClass_DIR . "assets/css/{$file}";
        $version = file_exists($path) ? filemtime($path) : '1.0';
        wp_enqueue_style($handle, $plugin_url . "assets/css/{$file}", [], $version);
    }

    // JS
    $js_files = [
        'myClass_select2'   => 'select2.min.js',
        'myClass_bootstrap' => 'bootstrap.bundle.min.js',
        'myClass_gsap'      => 'gsap.min.js',
        'myClass_main'      => 'main.js',
    ];
    foreach ($js_files as $handle => $file) {
        $path = myClass_DIR . "assets/js/{$file}";
        $version = file_exists($path) ? filemtime($path) : '1.0';
        wp_enqueue_script($handle, $plugin_url . "assets/js/{$file}", ['jquery'], $version, true);
    }

    wp_localize_script('myClass_main', 'myClass_object', [
        'rest_url' => esc_url(rest_url('myClass/v1/')),
        'assets'   => myClass_ASSETS,
        'uploads'  => myClass_UPLOADS,
        'temp'  => myClass_TEMPLATES,
        'myClass_url'  => myClass_URL,
        'student_dashboard_url'  => site_url('صفحه-دانش-آموز/'),
        'is_admin' => current_user_can('manage_options'),
    ]);
    
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'load_myClass_assets');

function load_fontawesome_for_stars() {
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'load_fontawesome_for_stars');


/************************************************************************************
 * فعال‌سازی مدیا وردپرس در بخش مدیریت
 ************************************************************************************/
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_media();

    wp_enqueue_script('jquery'); // jQuery وردپرس

    wp_enqueue_script('persian-date', 'https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js', ['jquery'], null, true);
    wp_enqueue_script('persian-datepicker', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.js', ['jquery','persian-date'], null, true);

    // حالا main.js بعد از همه
    wp_enqueue_script(
        'myClass_main',
        myClass_URL . 'assets/js/main.js',
        ['jquery', 'persian-date', 'persian-datepicker'], // dependencies
        filemtime(myClass_DIR . 'assets/js/main.js'),
        true
    );

    wp_enqueue_style('persian-datepicker-css', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.css');
    wp_enqueue_style(
        'myClass_main_css',
        myClass_URL . 'assets/css/main.css',
        [],
        filemtime(myClass_DIR . 'assets/css/main.css')
    );

    wp_localize_script('myClass_main', 'myClass_object', [
        'rest_url' => esc_url(rest_url('myClass/v1/')),
        'uploads'  => myClass_UPLOADS,
        'site_url' => esc_url(site_url()),
    ]);
});


/************************************************************************************
 * شورتکدها
 ************************************************************************************/
function myClass_shortcode(): string {
    ob_start();
    include myClass_DIR . 'body_html.php';
    return ob_get_clean();
}
add_shortcode('myClass', 'myClass_shortcode');

function myClass_students_stars_shortcode(): bool|string {
    ob_start();
    include myClass_TEMPLATES . 'students_stars.php';
    return ob_get_clean();
}
add_shortcode('students_stars', 'myClass_students_stars_shortcode');

function myClass_attendance_shortcode(): bool|string {
    ob_start();
    include myClass_TEMPLATES . 'attendance.php';
    return ob_get_clean();
}
add_shortcode('attendance', 'myClass_attendance_shortcode');

function myClass_experiments_shortcode(): bool|string {
    ob_start();
    include myClass_TEMPLATES . 'experiments.php';
    return ob_get_clean();
}
add_shortcode('experiments', 'myClass_experiments_shortcode');

function myclass_top_students_shortcode(): bool|string{
    ob_start();

    include myClass_TEMPLATES . 'top-students.php';

    return ob_get_clean();
}

add_shortcode('top_students', 'myclass_top_students_shortcode');


// شورتکد برای نمایش داشبورد
function myClass_dashboard_shortcode($atts): bool|string{
    ob_start(); ?>
    <div id="myClass-dashboard-container"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('student_dashboard', 'myClass_dashboard_shortcode');


function student_gallery_handler(): bool|string{
    ob_start();
    include myClass_TEMPLATES . 'students_gallery.php';
    return ob_get_clean();
}
add_shortcode('student_gallery', 'student_gallery_handler');


function view_group_handler(): bool|string{
    ob_start();
    include myClass_TEMPLATES . 'view_group.php';
    return ob_get_clean();
}
add_shortcode('view_group', 'view_group_handler');

function view_exams_handler(): bool|string {
    wp_enqueue_script('jquery'); // حتما
    wp_enqueue_script('persian-date', 'https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js', ['jquery'], null, true);
    wp_enqueue_script('persian-datepicker', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.js', ['jquery','persian-date'], null, true);
    wp_enqueue_style('persian-datepicker-css', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.css');
    wp_enqueue_style('vazirmatn-font', 'https://cdn.jsdelivr.net/npm/vazirmatn@33.003/font-face.css');

    // فایل JS خودت
    wp_enqueue_script('view-exams-script', plugin_dir_url(__FILE__).'js/view-exams.js', ['jquery','persian-date','persian-datepicker'], null, true);

    ob_start();
    include myClass_TEMPLATES . 'view_exams.php';
    return ob_get_clean();
}
add_shortcode('view_exams', 'view_exams_handler');


/************************************************************************************
 * REST API
 ************************************************************************************/
add_action('rest_api_init', function () {
    register_rest_route('myClass/v1', '/get_today_status', [
        'methods' => 'GET',
        'callback' => 'get_today_status',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('myClass/v1', '/finalize_absent', [
        'methods' => 'POST',
        'callback' => 'finalize_absent',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('myClass/v1', '/set_status', [
        'methods' => 'POST',
        'callback' => 'set_status_manual',
        'permission_callback' => '__return_true'
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1', '/get_experiments', [
        'methods' => 'GET',
        'callback' => 'myclass_get_experiments',
        'permission_callback' => '__return_true',
    ]);

    // می‌توان API های save و delete را هم مشابه ثبت کرد
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1', '/save_experiment', [
        'methods' => 'POST',
        'callback' => 'myclass_save_experiment',
        'permission_callback' => '__return_true',
        'args' => []
    ]);

    register_rest_route('myClass/v1', '/delete_experiment', [
        'methods' => 'POST',
        'callback' => 'myclass_delete_experiment',
        'permission_callback' => '__return_true',
        'args' => []
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1', '/top_students', [
        'methods' => 'GET',
        'callback' => 'myclass_get_top_students',
        'permission_callback' => '__return_true',
    ]);
    
    register_rest_route('myClass/v1', '/save_top_students', [
        'methods' => 'POST',
        'callback' => 'myclass_save_top_students',
        'permission_callback' =>  '__return_true',
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1', '/dashboard', [
        'methods' => 'GET',
        'callback' => 'myClass_get_dashboard_data',
        'permission_callback' => '__return_true'
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1', '/student-login', [
        'methods' => 'POST',
        'callback' => 'student_login_rest',
        'permission_callback' => '__return_true', // اجازه دسترسی به همه
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1', '/logout', [
        'methods' => 'POST',
        'callback' => 'myClass_student_logout',
        'permission_callback' => '__return_true',
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('myClass/v1','/save_jobs',[
    'methods'=>'POST',
    'callback'=>'myclass_save_jobs',
    'permission_callback'=>'__return_true' ,
    ]);
});

add_action('rest_api_init', function () {
    register_rest_route('myClass/v1', '/students', [
        'methods'  => 'GET',
        'callback' => 'myClass_get_students',
        'permission_callback' => '__return_true', // آزاد برای همه
    ]);

    register_rest_route('myClass/v1', '/students/import', [
        'methods'  => 'POST',
        'callback' => 'myClass_import_students_csv',
        'permission_callback' => '__return_true', // آزاد برای همه
    ]);

    register_rest_route('myClass/v1', '/students/update', [
        'methods'  => 'POST',
        'callback' => 'myClass_update_student',
        'permission_callback' => '__return_true', // آزاد برای همه
    ]);

    register_rest_route('myClass/v1', '/students/export', [
        'methods'  => 'GET',
        'callback' => 'myClass_export_students',
        'permission_callback' => '__return_true',
    ]);
});

add_action('rest_api_init', function(){
    register_rest_route('myClass/v1', '/behaviors/update', [
        'methods' => 'POST',
        'callback' => 'myClass_update_behaviors',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('myClass/v1', '/behaviors', [
        'methods' => 'GET',
        'callback' => 'myClass_get_behaviors',
        'permission_callback' => '__return_true',
    ]);
});

add_action('rest_api_init', function(){
    register_rest_route('myClass/v1','/save_exam',[
        'methods' => 'POST',
        'callback' => 'myclass_save_exam',
        'permission_callback' => '__return_true',
    ]);
});



function get_today_status() {
    global $wpdb;

    // تاریخ امروز
    $today = current_time('Y-m-d');

    // جدول‌ها
    $students_table   = $wpdb->prefix . 'students';
    $attendance_table = $wpdb->prefix . 'attendance';

    // کلاس فعال
    $active_class = get_option('myClass_active_class');
    if (empty($active_class)) {
        return rest_ensure_response([]);
    }

    // کوئری با شرط کلاس فعال و TRIM برای حذف فاصله‌های اضافی
    $sql = $wpdb->prepare("
        SELECT 
            s.id, 
            s.name, 
            COALESCE(a.status,'غایب') AS status, 
            a.time
        FROM {$students_table} s
        LEFT JOIN {$attendance_table} a 
            ON s.id = a.student_id AND a.date = %s
        WHERE TRIM(s.`class`) = %s
        ORDER BY s.id ASC
    ", $today, $active_class);

    $results = $wpdb->get_results($sql, ARRAY_A);

    return rest_ensure_response($results);
}

function finalize_absent() {
    global $wpdb;
    $today = current_time('Y-m-d');

    $students_table = $wpdb->prefix . 'students';
    $attendance_table = $wpdb->prefix . 'attendance';

    $students = $wpdb->get_col($wpdb->prepare("
        SELECT s.id
        FROM $students_table s
        LEFT JOIN $attendance_table a
        ON s.id = a.student_id AND a.date = %s
        WHERE a.student_id IS NULL
    ", $today));

    foreach ($students as $student_id) {
        $wpdb->insert($attendance_table, [
            'student_id' => $student_id,
            'date'       => $today,
            'status'     => 'غایب',
            'time'       => current_time('H:i:s')
        ]);
    }

    return rest_ensure_response(['msg' => 'دانش‌آموزان غایب ثبت شدند']);
}

function set_status_manual($request) {
    global $wpdb;

    $id     = intval($request['id']);
    $status = sanitize_text_field($request['status']);
    $date   = current_time('Y-m-d');
    $time   = current_time('H:i:s');

    $table = $wpdb->prefix . 'attendance';

    // آیا قبلاً برای امروز ثبت شده؟
    $exists = $wpdb->get_var($wpdb->prepare("
        SELECT id FROM $table WHERE student_id = %d AND date = %s
    ", $id, $date));

    if ($exists) {
        // آپدیت
        $wpdb->update($table, [
            'status' => $status,
            'time'   => $time
        ], [
            'id' => $exists
        ]);
    } else {
        // اینسرت
        $wpdb->insert($table, [
            'student_id' => $id,
            'date'       => $date,
            'status'     => $status,
            'time'       => $time
        ]);
    }

    return rest_ensure_response(['msg' => 'وضعیت ثبت شد']);
}

function myclass_get_experiments() {
    global $wpdb;

    $active_class = get_option('myClass_active_class'); // کلاس فعال
    if(empty($active_class)) $active_class = '';

    $table_lessons = $wpdb->prefix . 'lessons';
    $table_experiments = $wpdb->prefix . 'experiments';
    $table_images = $wpdb->prefix . 'experiment_images';

    // گرفتن درس‌ها (می‌تونی بخوای فقط درس‌های کلاس فعال رو هم فیلتر کنی)
    $lessons = $wpdb->get_results("SELECT id, name FROM $table_lessons ORDER BY name ASC", ARRAY_A);

    // گرفتن آزمایش‌ها بر اساس کلاس فعال
    $experiments = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_experiments WHERE class=%s ORDER BY created_at DESC", $active_class),
        ARRAY_A
    );

    foreach ($experiments as &$exp) {
        $exp['images'] = $wpdb->get_col($wpdb->prepare("SELECT filename FROM $table_images WHERE experiment_id=%d", $exp['id']));
    }

    return [
        'lessons' => $lessons,
        'experiments' => $experiments
    ];
}

function myclass_save_experiment($request) {
    global $wpdb;

    $table_experiments = $wpdb->prefix . 'experiments';
    $table_images = $wpdb->prefix . 'experiment_images';

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = sanitize_text_field($_POST['title'] ?? '');
    $lesson_id = intval($_POST['lesson_id'] ?? 0);
    $results = sanitize_textarea_field($_POST['results'] ?? '');
    $class = sanitize_text_field($_POST['activeClass'] ?? ''); // کلاس فعال


    if (!$title || !$lesson_id || !$class) {
        return rest_ensure_response(['msg' => 'عنوان، درس و کلاس الزامی هستند']);
    }

    // ذخیره یا بروزرسانی آزمایش
    if ($id) {
        $wpdb->update($table_experiments, [
            'title' => $title,
            'lesson_id' => $lesson_id,
            'results' => $results,
            'class' => $class
        ], ['id' => $id]);
    } else {
        $wpdb->insert($table_experiments, [
            'title' => $title,
            'lesson_id' => $lesson_id,
            'results' => $results,
            'class' => $class
        ]);
        $id = $wpdb->insert_id;
    }


    // آپلود تصاویر و حذف تصاویر مشابه قبل (مثل کد قبلی)
    $upload_dir = plugin_dir_path(__FILE__) . 'uploads/experiments/';
    wp_mkdir_p($upload_dir);

    if (!empty($_POST['removed_images'])) {
        foreach ($_POST['removed_images'] as $filename) {
            $wpdb->delete($table_images, ['experiment_id' => $id, 'filename' => $filename]);
            $file_path = $upload_dir . $filename;
            if (file_exists($file_path)) unlink($file_path);
        }
    }

    if (!empty($_FILES['images'])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            $tmp_name = $_FILES['images']['tmp_name'][$key];
            if (!$tmp_name) continue;

            $filename = time() . '_' . sanitize_file_name($name);
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($tmp_name, $destination)) {
                $wpdb->insert($table_images, [
                    'experiment_id' => $id,
                    'filename' => $filename
                ]);
            }
        }
    }

    return rest_ensure_response(['ok' => true, 'msg' => 'آزمایش با موفقیت ذخیره شد']);
}

function myclass_delete_experiment($request) {
    global $wpdb;

    $table_experiments = $wpdb->prefix . 'experiments';
    $table_images = $wpdb->prefix . 'experiment_images';

    $id = intval($_POST['id'] ?? 0);
    if (!$id) return rest_ensure_response(['msg' => 'شناسه معتبر نیست']);

    // حذف تصاویر از سرور
    $images = $wpdb->get_col($wpdb->prepare("SELECT filename FROM $table_images WHERE experiment_id=%d", $id));
    foreach ($images as $img) {
        $file_path = myClass_UPLOADS . '/' . $img;
        if (file_exists($file_path)) unlink($file_path);
    }

    // حذف رکوردها از دیتابیس
    $wpdb->delete($table_images, ['experiment_id' => $id]);
    $wpdb->delete($table_experiments, ['id' => $id]);

    return rest_ensure_response(['msg' => 'آزمایش با موفقیت حذف شد']);
}

function myclass_get_top_students($request) {
    global $wpdb;

    $students_table = $wpdb->prefix . 'students';
    $behavior_table = $wpdb->prefix . 'student_behavior';
    $archive_table  = $wpdb->prefix . 'top_students_archive';

    // لیست دانش‌آموزان
    $all_students = $wpdb->get_results("SELECT id, name FROM $students_table", ARRAY_A);
    $students_map = [];
    foreach ($all_students as $s) {
        $students_map[$s['id']] = $s['name'];
    }

    // لیست رفتارها
    $students = $wpdb->get_results("SELECT * FROM $behavior_table", ARRAY_A);

    // تابع تبدیل میلادی به شمسی
    function gregorian_to_jalali($gy, $gm, $gd) {
        $g_days_in_month = [31,28,31,30,31,30,31,31,30,31,30,31];
        $j_days_in_month = [31,31,31,31,31,31,30,30,30,30,30,29];
        if (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) {
            $g_days_in_month[1] = 29;
        }
        $gy -= 1600;
        $gm -= 1;
        $gd -= 1;
        $g_day_no = 365 * $gy + intval(($gy + 3) / 4) - intval(($gy + 99) / 100) + intval(($gy + 399) / 400);
        for ($i = 0; $i < $gm; $i++) $g_day_no += $g_days_in_month[$i];
        $g_day_no += $gd;
        $j_day_no = $g_day_no - 79;
        $j_np = intval($j_day_no / 12053);
        $j_day_no %= 12053;
        $jy = 979 + 33 * $j_np + 4 * intval($j_day_no / 1461);
        $j_day_no %= 1461;
        if ($j_day_no >= 366) { $jy += intval(($j_day_no - 1) / 365); $j_day_no = ($j_day_no - 1) % 365; }
        for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; $i++) $j_day_no -= $j_days_in_month[$i];
        $jm = $i + 1;
        $jd = $j_day_no + 1;
        return [$jy, $jm, $jd];
    }

    // سال و ماه شمسی فعلی
    list($current_year, $current_month) = gregorian_to_jalali(date('Y'), date('n'), date('j'));

    $students_by_month = [];

    // دسته‌بندی رفتارها بر اساس ماه شمسی
    foreach ($students as $s) {
        $ts = strtotime($s['date']);
        if (!$ts) continue;
        list($jy, $jm, $jd) = gregorian_to_jalali(date('Y',$ts), date('n',$ts), date('j',$ts));
        if ($jy != $current_year) continue;
        $students_by_month[$jy][$jm][] = $s;
    }

    // فقط ماه‌های ۷ تا ۱۲
    $months_order = range(7, 12);
    $result = [];

    foreach($months_order as $m) {

        $month_students = $students_by_month[$current_year][$m] ?? [];
        $month_top = [];
        $max_stars = 0;

        // --- اضافه کردن داده‌های behavior
        foreach ($month_students as $b) {
            $id = $b['student_id'];
            if (!isset($month_top[$id])) {
                $month_top[$id] = [
                    'id' => $id,
                    'balance' => 0,
                    'calculated_stars' => 0,
                    'manual_stars' => 0,
                    'total_stars' => 0,
                    'name' => $students_map[$id] ?? 'بدون نام'
                ];
            }
            $p = (int)($b['positive_count'] ?? 0);
            $n = (int)($b['negative_count'] ?? 0);

            if($b['lesson_id'] != 9){
                $month_top[$id]['balance'] += $p-$n;
                $month_top[$id]['calculated_stars'] = max(0,floor($month_top[$id]['balance']/10));
            } else {
                $month_top[$id]['manual_stars'] += $p;
            }
            $month_top[$id]['total_stars'] = $month_top[$id]['calculated_stars']+$month_top[$id]['manual_stars'];
        }

        // --- اضافه کردن داده‌های آرشیو
        $archive_students = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $archive_table WHERE year=%d AND month=%d
        ", $current_year, $m), ARRAY_A);

        foreach($archive_students as $a){
            $id = $a['student_id'];
            if(!isset($month_top[$id])){
                $month_top[$id] = [
                    'id' => $id,
                    'balance' => intval($a['balance']),
                    'calculated_stars' => intval($a['calculated_stars']),
                    'manual_stars' => intval($a['manual_stars']),
                    'total_stars' => intval($a['total_stars']),
                    'name' => $students_map[$id] ?? 'بدون نام'
                ];
            } else {
                // اگر قبلا محاسبه شده بود، مقدار آرشیو را جایگزین کن
                $month_top[$id]['balance'] = intval($a['balance']);
                $month_top[$id]['calculated_stars'] = intval($a['calculated_stars']);
                $month_top[$id]['manual_stars'] = intval($a['manual_stars']);
                $month_top[$id]['total_stars'] = intval($a['total_stars']);
            }
        }

        // پیدا کردن بیشترین تعداد ستاره
        foreach ($month_top as $s)
            if ($s['total_stars'] > $max_stars) $max_stars = $s['total_stars'];

        // انتخاب دانش‌آموزان برتر (حداقل یک ستاره)
        $top = [];
        foreach ($month_top as $id => $s) {
            if ($s['total_stars'] > 0 && $s['total_stars'] == $max_stars) {
                $top[$id] = $s;
            }
        }

        uasort($top, fn($a,$b)=>$b['balance']<=>$a['balance']);

        $result[] = [
            'month'=>$m,
            'students'=>array_values($top)
        ];
    }

    return rest_ensure_response([
        'year'=>$current_year,
        'data'=>$result
    ]);
}

function student_login_rest($request) {
    if( !session_id() ) session_start();

    global $wpdb;

    $username = trim($request->get_param('username') ?? '');
    $password = trim($request->get_param('password') ?? '');

    if(!$username || !$password) {
        return wp_send_json(['success'=>false, 'message'=>'لطفاً همه فیلدها را پر کنید 🌟']);
    }

    // استفاده از prepare برای امنیت
    $table = $wpdb->prefix . 'students'; // فرض می‌کنیم جدول students در دیتابیس وردپرس داریم
    $student = $wpdb->get_row(
        $wpdb->prepare("SELECT id, name, password_hash FROM $table WHERE username = %s LIMIT 1", $username),
        ARRAY_A
    );

    if($student && password_verify($password, $student['password_hash'])) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['name'];

        return wp_send_json(['success'=>true]);
    } else {
        return wp_send_json(['success'=>false, 'message'=>'نام کاربری یا رمز عبور اشتباه است 😕']);
    }
}

function myclass_save_top_students($request) {
    global $wpdb;

    $params = $request->get_json_params();
    $year = intval($params['year']);
    $month = intval($params['month']);

    if (!$year || !$month) {
        return new WP_Error('invalid_data', 'سال یا ماه نامعتبر است.', ['status' => 400]);
    }

    // دریافت نفرات برتر همان ماه
    $response = myclass_get_top_students(null);
    $data = $response->get_data();
    $top_students = [];

    foreach ($data['data'] as $m) {
        if ($m['month'] == $month) {
            $top_students = $m['students'];
            break;
        }
    }

    if (empty($top_students)) {
        return new WP_Error('no_students', 'دانش‌آموزی برای این ماه یافت نشد.', ['status' => 404]);
    }

    $table = $wpdb->prefix . 'top_students_archive';
    $count = 0;

    foreach ($top_students as $s) {
        $wpdb->replace(
            $table,
            [
                'student_id'       => intval($s['id']),
                'year'             => $year,
                'month'            => $month,
                'balance'          => intval($s['balance']),
                'calculated_stars' => intval($s['calculated_stars']),
                'manual_stars'     => intval($s['manual_stars']),
                'total_stars'      => intval($s['total_stars']),
            ],
            ['%d','%d','%d','%d','%d','%d','%d']
        );
        $count++;
    }

    return rest_ensure_response(['success' => true, 'message' => "تعداد {$count} نفر ثبت شد."]);
}

function myClass_get_dashboard_data($request){
    if(!isset($_SESSION['student_id'])){
        return wp_send_json(['success'=>false, 'message'=>'کاربر وارد نشده']);
    }

    global $wpdb;
    $student_id = $_SESSION['student_id'];

    $lesson_id = $request->get_param('lesson_id') ?? 0;

    // اطلاعات دانش‌آموز
    $student = $wpdb->get_row($wpdb->prepare("SELECT name, job FROM {$wpdb->prefix}students WHERE id=%d", $student_id), ARRAY_A);
    if(!$student) return wp_send_json(['success'=>false, 'message'=>'دانش‌آموز پیدا نشد']);

    // لیست دروس
    $lessons = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}lessons ORDER BY name ASC", ARRAY_A);

    // حضور امروز
    $today = date('Y-m-d');
    $attendance = $wpdb->get_row($wpdb->prepare("SELECT status, time FROM {$wpdb->prefix}attendance WHERE student_id=%d AND date=%s", $student_id, $today), ARRAY_A);
    $attendance = $attendance ?: ['status'=>'غایب', 'time'=>'-'];

    // رفتار درس انتخابی
    $behavior = null;
    if($lesson_id){
        $behavior = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT positive_count, negative_count FROM {$wpdb->prefix}student_behavior WHERE student_id=%d AND lesson_id=%d",
                $student_id, $lesson_id
            ),
            ARRAY_A
        );
    }

    return wp_send_json([
        'success'=>true,
        'student' => $student,
        'lessons' => $lessons,
        'attendance' => $attendance,
        'behavior' => $behavior,
        'selected_lesson_id' => $lesson_id
    ]);
}

function myClass_student_logout() {
    if(session_id()) {
        $_SESSION = [];
        session_destroy();
    }
    return ['success' => true, 'redirect' => site_url('/myclass')];
}

function myclass_save_jobs(WP_REST_Request $request){
    global $wpdb;

    // بررسی سطح دسترسی
    if(!current_user_can('manage_options')){
        return ['success'=>false, 'msg'=>'دسترسی مجاز نیست'];
    }

    $jobs = $request->get_param('job'); // آرایه job[student_id] => job_name

    if(empty($jobs) || !is_array($jobs)){
        return ['success'=>false, 'msg'=>'هیچ داده‌ای ارسال نشده است'];
    }

    $table = $wpdb->prefix . 'students';
    $updated_count = 0;

    foreach($jobs as $student_id => $job_name){
        $student_id = intval($student_id);
        $job_name = sanitize_text_field($job_name);

        if($student_id && $job_name !== ''){
            $res = $wpdb->update(
                $table,
                ['job' => $job_name],
                ['id' => $student_id],
                ['%s'],
                ['%d']
            );

            if($res !== false) $updated_count++;
        }
    }

    return [
        'success' => true,
        'msg' => "تعداد {$updated_count} دانش‌آموز بروزرسانی شد"
    ];
}

function myClass_get_students(){
    global $wpdb;
    $rows = $wpdb->get_results("SELECT id, name, rfid_tag, class, job, username, 
        (password_hash IS NOT NULL AND password_hash <> '') AS has_password, must_change_password 
        FROM {$wpdb->prefix}students ORDER BY id ASC", ARRAY_A);
    return ['ok'=>true, 'students'=>$rows];
}

function myClass_import_students_csv($request){
    if(empty($_FILES['csv_file']['tmp_name']))
        return ['ok'=>false, 'msg'=>'هیچ فایلی ارسال نشده است.'];

    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if(!$file) return ['ok'=>false, 'msg'=>'فایل CSV قابل خواندن نیست.'];

    global $wpdb;
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}students");
    $added = 0;

    while(($data = fgetcsv($file)) !== false){
        if(count($data) < 7) continue;
        [$name, $rfid, $class, $job, $username, $password, $must_change] = $data;
        $wpdb->insert("{$wpdb->prefix}students", [
            'name'=>$name,
            'rfid_tag'=>$rfid,
            'class'=>$class,
            'job'=>$job,
            'username'=>$username,
            'password_hash'=>password_hash($password, PASSWORD_BCRYPT),
            'must_change_password'=>$must_change ? 1 : 0
        ]);
        $added++;
    }
    fclose($file);
    return ['ok'=>true, 'added'=>$added];
}

function myClass_update_student($request){
    global $wpdb;
    $data = $request->get_json_params();
    $id = intval($data['id']);

    if(!$id) return ['ok'=>false, 'msg'=>'شناسه نامعتبر'];

    $update = [
        'name' => sanitize_text_field($data['name']),
        'class' => sanitize_text_field($data['class']),
        'rfid_tag' => sanitize_text_field($data['rfid_tag']),
        'job' => sanitize_text_field($data['job']),
        'username' => sanitize_text_field($data['username']),
        'must_change_password' => intval($data['must_change_password'])
    ];
    if(!empty($data['password']))
        $update['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);

    $wpdb->update("{$wpdb->prefix}students", $update, ['id'=>$id]);
    return ['ok'=>true];
}

function myClass_export_students(WP_REST_Request $req) {
    global $wpdb;
    $type = $req->get_param('type'); // csv or sql
    $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}students", ARRAY_A);

    if (empty($rows)) {
        return new WP_REST_Response(['message' => 'هیچ داده‌ای وجود ندارد.'], 404);
    }

    if ($type === 'csv') {
        $filename = "students_export_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$filename");
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($rows[0])); // headers
        foreach ($rows as $r) fputcsv($out, $r);
        fclose($out);
        exit;
    }

    if ($type === 'sql') {
        $filename = "students_export_" . date('Ymd_His') . ".sql";
        header('Content-Type: application/sql; charset=utf-8');
        header("Content-Disposition: attachment; filename=$filename");

        echo "-- Students Export (" . date('Y-m-d H:i:s') . ")\n\n";
        foreach ($rows as $r) {
            $cols = array_map(fn($v) => "`$v`", array_keys($r));
            $vals = array_map(fn($v) => $wpdb->_real_escape($v), array_values($r));
            $vals = array_map(fn($v) => "'" . str_replace("'", "\\'", $v) . "'", $vals);
            echo "INSERT INTO {$wpdb->prefix}students (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
        exit;
    }

    return new WP_REST_Response(['message' => 'فرمت خروجی نامعتبر است.'], 400);
}

function myClass_get_behaviors($request){
    global $wpdb;

    $lesson_id = intval($request->get_param('lesson_id'));
    if(!$lesson_id) return ['ok'=>false,'msg'=>'درس انتخاب نشده'];

    // کلاس فعال
    $active_class = get_option('myClass_active_class');
    if(!$active_class) return ['ok'=>false,'msg'=>'کلاس فعال مشخص نشده'];

    $students_table = $wpdb->prefix . 'students';
    $behavior_table = $wpdb->prefix . 'student_behavior';

    $query = "
        SELECT s.id, s.name,
               COALESCE(MAX(b.positive_count),0) as positive_count,
               COALESCE(MAX(b.negative_count),0) as negative_count
        FROM {$students_table} s
        LEFT JOIN {$behavior_table} b
            ON s.id = b.student_id AND b.lesson_id = %d
        WHERE TRIM(s.`class`) = %s
        GROUP BY s.id, s.name
        ORDER BY s.id ASC
    ";

    $students = $wpdb->get_results($wpdb->prepare($query, $lesson_id, $active_class), ARRAY_A);

    return ['ok'=>true, 'students'=>$students];
}

function myClass_update_behaviors($request){
    global $wpdb;

    $data = $request->get_json_params();
    $lesson_id = intval($data['lesson_id'] ?? 0);
    $positive = $data['positive'] ?? [];
    $negative = $data['negative'] ?? [];

    if(!$lesson_id) return ['ok'=>false,'msg'=>'درس انتخاب نشده'];

    $behavior_table = $wpdb->prefix . 'student_behavior';

    $today = current_time('mysql'); 
    // مثلا: 2025-11-16 13:45:00

    foreach($positive as $student_id => $p){
        $n = intval($negative[$student_id] ?? 0);

        $wpdb->replace(
            $behavior_table,
            [
                'student_id' => intval($student_id),
                'lesson_id' => $lesson_id,
                'positive_count' => intval($p),
                'negative_count' => intval($n),
                'date' => $today  // 👈 تاریخ امروز ثبت می‌شود
            ],
            ['%d','%d','%d','%d','%s']
        );
    }

    return ['ok'=>true,'msg'=>'ثبت شد'];
}

function myclass_save_exam(WP_REST_Request $request){
    global $wpdb;

    function fa_to_en($str){
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($fa, $en, $str);
    }

    $lesson_id = intval($request->get_param('lesson_id'));
    $examDate = fa_to_en($request->get_param('examDate'));
    $examWeekday = sanitize_text_field($request->get_param('examWeekday'));
    $class = sanitize_text_field($request->get_param('class'));

    if(empty($examWeekday)) $examWeekday = 'شنبه';
    if(!$lesson_id || !$examDate || !$examWeekday || !$class){
        return ['success'=>false,'message'=>'اطلاعات ناقص است'];
    }

    // ذخیره عکس‌ها
    $exam_image_paths = [];
    $upload_dir = wp_upload_dir();
    if(!empty($_FILES['exam_images'])){
        foreach($_FILES['exam_images']['tmp_name'] as $i=>$tmp){
            if($_FILES['exam_images']['error'][$i]===0){
                $ext = pathinfo($_FILES['exam_images']['name'][$i], PATHINFO_EXTENSION);
                $file_name = 'exam_'.time().'_'.$i.'.'.$ext;
                $target = $upload_dir['basedir'].'/myclass_exams/'.$file_name;
                if(!is_dir(dirname($target))) mkdir(dirname($target),0777,true);
                if(move_uploaded_file($tmp,$target)){
                    $exam_image_paths[] = $upload_dir['baseurl'].'/myclass_exams/'.$file_name;
                }
            }
        }
    }
    $exam_images_str = implode(',',$exam_image_paths);

    // ذخیره نمرات
    foreach($_POST as $k=>$v){
        if(strpos($k,'score_')===0){
            $student_id = intval(str_replace('score_','',$k));
            $score = sanitize_text_field($v);
            $wpdb->insert("{$wpdb->prefix}exams",[
                'student_id'=>$student_id,
                'lesson_id'=>$lesson_id,
                'score'=>$score,
                'exam_date'=>$examDate,
                'weekday'=>$examWeekday,
                'exam_image'=>$exam_images_str,
                'class'=>$class
            ]);
        }
    }

    return ['success'=>true,'message'=>'امتحان با موفقیت ثبت شد'];
}

function myClass_header_buttons_map() {
    return [
        'points' => [
            'label' => 'امتیازات',
            'url'   => '/امتیاز-ها'
        ],
        'top_students' => [
            'label' => 'نفرات برتر هر ماه',
            'url'   => '/نفرات-برتر/'
        ],
        'groups' => [
            'label' => 'گروه‌ها',
            'url'   => '/گروه-های-دانش-آموزی/'
        ],
        'exams' => [
            'label' => 'امتحانات',
            'url'   => '/لیست-امتحانات/'
        ],
        'labs' => [
            'label' => 'آزمایشات',
            'url'   => '/آزمایش-ها/'
        ],
        'attendance' => [
            'label' => 'حضور و غیاب',
            'url'   => '/حضوروغیاب/'
        ],
    ];
}


