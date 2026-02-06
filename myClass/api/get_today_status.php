<?php

add_action('wp_ajax_get_today_status', 'myclass_get_today_status');

echo '<pre>';
var_dump(get_option('myClass_active_class'));
echo '</pre>';
die();

function myclass_get_today_status() {
    global $wpdb;

    $active_class = get_option('myClass_active_class');

    if ( empty($active_class) ) {
        wp_send_json([]);
    }

    $today = current_time('Y-m-d');

    $students_table   = $wpdb->prefix . 'students';
    $attendance_table = $wpdb->prefix . 'attendance';

    $sql = $wpdb->prepare("
        SELECT 
            s.id,
            s.name,
            COALESCE(a.status, 'غایب') AS status,
            a.time
        FROM {$students_table} s
        LEFT JOIN {$attendance_table} a
            ON s.id = a.student_id
            AND a.date = %s
        WHERE s.`class` = %s
        ORDER BY s.id ASC
    ", $today, $active_class);

    $results = $wpdb->get_results($sql, ARRAY_A);

    wp_send_json($results);
}
