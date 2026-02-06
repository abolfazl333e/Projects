<?php
if (!defined('ABSPATH')) exit;

function cm_enqueue_admin_assets() {
    wp_enqueue_style('cm-style', CM_URL . 'public/css/style.css');
    wp_enqueue_script('cm-script', CM_URL . 'public/js/script.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'cm_enqueue_admin_assets');

// اتصال به دیتابیس وردپرس
global $wpdb;
define('CM_TABLE_EXPERIMENTS', $wpdb->prefix . 'cm_experiments');
define('CM_TABLE_BEHAVIORS', $wpdb->prefix . 'cm_behaviors');

// نصب جداول در اولین بار
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $wpdb->query("CREATE TABLE IF NOT EXISTS " . CM_TABLE_EXPERIMENTS . " (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lesson VARCHAR(255),
        title VARCHAR(255),
        description TEXT,
        images TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;");

    $wpdb->query("CREATE TABLE IF NOT EXISTS " . CM_TABLE_BEHAVIORS . " (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        lesson_id INT,
        positive INT DEFAULT 0,
        negative INT DEFAULT 0,
        date DATE DEFAULT CURRENT_DATE
    ) $charset;");
});
