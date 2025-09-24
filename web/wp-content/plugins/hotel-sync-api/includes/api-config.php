<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}
$current_host = $_SERVER['HTTP_HOST'];
if (str_contains($current_host, '.local') || str_contains($current_host, 'localhost')) {
    // Môi trường local
    $api_url = 'http://api.2govietnam.local/api/sync/';
} else {
    // Môi trường production
    $api_url = 'http://api.hotels-web.com/api/sync/';
}
define('API_URL', $api_url);
