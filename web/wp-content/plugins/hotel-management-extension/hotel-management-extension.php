<?php
/*
Plugin Name: Hotel Management Extension
Description: Mở rộng tính năng Booking, Room Rate, Promotion cho WordPress Multisite Hotel System
Version: 1.0
Author: Tap Nguyen
Network: true
*/

// Ngăn chặn truy cập trực tiếp vào file
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa đường dẫn file
define('HME_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HME_PLUGIN_URL', plugin_dir_url(__FILE__));

// Yêu cầu các file từ hotel-sync-api (dependency)
// if (!function_exists('callApi')) {
//     // Nếu hotel-sync-api chưa được load, thông báo lỗi
//     add_action('admin_notices', function () {
//         echo '<div class="notice notice-error"><p><strong>Hotel Management Extension:</strong> Requires Hotel Sync API plugin to be activated first.</p></div>';
//     });
//     return;
// }

// Include các file xử lý
require_once(HME_PLUGIN_PATH . 'includes/class-hotel-management-extension.php');
require_once(HME_PLUGIN_PATH . 'includes/class-booking-manager.php');
require_once(HME_PLUGIN_PATH . 'includes/class-room-rate-manager.php');
require_once(HME_PLUGIN_PATH . 'includes/class-promotion-manager.php');

// Khởi tạo plugin chính
new Hotel_Management_Extension();
