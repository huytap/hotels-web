<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}
$current_host = $_SERVER['HTTP_HOST'] ?? '';
// Kiểm tra nếu đang chạy trên local environment
if (str_contains($current_host, '.local') || str_contains($current_host, 'localhost') ||
    str_contains(__FILE__, 'local') || str_contains(get_home_url(), '.local')) {
    // Môi trường local
    $api_url = 'http://api.2govietnam.local/api/v1/';
} else {
    // Môi trường production
    $api_url = 'http://api.hotels-web.com/api/v1/';
}
define('API_URL', $api_url);
