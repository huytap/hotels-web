<?php
/*
Plugin Name: Hotel Sync API
Description: Quản lý các endpoint API cho việc đồng bộ dữ liệu khách sạn với ứng dụng ngoài.
Version: 1.0
Author: Tap Nguyen
*/

// Ngăn chặn truy cập trực tiếp vào file
if (!defined('ABSPATH')) {
    exit;
}
// Định nghĩa đường dẫn file
define('HOTEL_SYNC_API_PATH', plugin_dir_path(__FILE__));

// Yêu cầu các file xử lý logic
require_once(HOTEL_SYNC_API_PATH . 'includes/api-auth.php');
require_once(HOTEL_SYNC_API_PATH . 'includes/json-process.php');
require_once(HOTEL_SYNC_API_PATH . 'includes/api-config.php');
require_once(HOTEL_SYNC_API_PATH . 'includes/api-call.php');
require_once(HOTEL_SYNC_API_PATH . 'includes/api-hotels.php');
require_once(HOTEL_SYNC_API_PATH . 'includes/api-rooms.php');
// 1. Đảm bảo header Authorization được truyền qua, đặc biệt trên Apache
add_filter('rest_pre_serve_request', function ($value) {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return $value;
    }

    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
        }
    }
    return $value;
});

// 2. Đăng ký endpoint API
function hotel_sync_register_rest_routes()
{
    // Endpoint để xác thực token từ Laravel
    register_rest_route('hotel-sync/v1', '/validate-token', [
        'methods' => 'GET',
        'callback' => 'validate_token_callback',
        'permission_callback' => 'hotel_sync_verify_api_permission_callback',
    ]);

    // Endpoint để Laravel lấy thông tin khách sạn
    register_rest_route('hotel-sync/v1', '/hotels', [
        'methods' => 'GET',
        'callback' => 'hotel_sync_get_hotels',
        'permission_callback' => 'hotel_sync_verify_api_permission_callback',
    ]);
    // Endpoint để Laravel lấy thông tin khách sạn
    register_rest_route('hotel-sync/v1', '/rooms', [
        'methods' => 'GET',
        'callback' => 'hotel_sync_get_rooms',
        'permission_callback' => 'hotel_sync_verify_api_permission_callback',
    ]);
}
add_action('rest_api_init', 'hotel_sync_register_rest_routes');
//Xác thực token của site con
function validate_token_callback(WP_REST_Request $request)
{
    // Phần xác thực đã được xử lý bởi permission_callback.
    // Nếu code đến được đây, request đã hợp lệ.
    return new WP_REST_Response([
        'message' => 'Token is valid.',
        'status' => 'success'
    ], 200);
}
// 3. Hàm xử lý API
//Lấy dữ liệu của khách sạn
// function hotel_sync_get_hotels(WP_REST_Request $request)
// {
//     // Lấy token từ header Authorization
//     $auth_header = $request->get_header('Authorization');
//     if (empty($auth_header) || stripos($auth_header, 'Bearer ') !== 0) {
//         return new WP_REST_Response([
//             'message' => 'Missing or invalid Authorization header.'
//         ], 401);
//     }
//     $provided_token = trim(substr($auth_header, 7));
//     // Lấy ID của site con từ tên miền hiện tại
//     $current_domain = $_SERVER['HTTP_HOST'];
//     $blog_id = get_blog_id_from_url($current_domain);
//     if (!$blog_id) {
//         return new WP_REST_Response([
//             'message' => 'Site not found for this domain.'
//         ], 404);
//     }

//     // Lấy cấu hình token từ database của site chính
//     $main_site_id = get_main_site_id();
//     switch_to_blog($main_site_id);

//     // Sử dụng đúng tên option key
//     $option_key = "hotel_sync_api_config";
//     $config = get_option($option_key, ['subsites' => []]);
//     $site_conf = $config['subsites'][$blog_id] ?? null;
//     restore_current_blog();
//     if (!$site_conf || empty($site_conf['token']) || empty($site_conf['enabled']) || $site_conf['token'] !== $provided_token) {
//         return new WP_REST_Response([
//             'message' => 'Invalid token or synchronization is disabled for this site.'
//         ], 403);
//     }

//     // Chuyển sang site con để lấy dữ liệu
//     switch_to_blog($blog_id);

//     // Lấy ID của khách sạn
//     $selected_hotel_id = (int) get_current_blog_id();
//     if ($selected_hotel_id === 0) {
//         restore_current_blog();
//         return new WP_REST_Response([
//             'message' => 'Hotel not found.'
//         ], 404);
//     }

//     // Kiểm tra plugin Polylang
//     if (!function_exists('pll_languages_list')) {
//         restore_current_blog();
//         return new WP_REST_Response([
//             'message' => 'Polylang is required to retrieve language-specific data.'
//         ], 500);
//     }

//     $languages = pll_languages_list();
//     $hotels = [];

//     // Lấy thông tin khách sạn theo từng ngôn ngữ
//     foreach ($languages as $lang) {
//         $hotels[] = [
//             'id' => $selected_hotel_id,
//             'lang' => $lang,
//             'name' => get_option("hotel_info_name_{$lang}", ''),
//             'address' => get_option("hotel_info_address_{$lang}", ''),
//             'phone' => get_option("hotel_info_phone_{$lang}", ''),
//             'email' => get_option("hotel_info_email_{$lang}", ''),
//             'map' => get_option("hotel_info_map_{$lang}", ''),
//         ];
//     }
//     $last_updated_date = get_lastpostmodified('gmt', 'post');
//     restore_current_blog();

//     return new WP_REST_Response([
//         'success' => true,
//         'hotels' => $hotels,
//         'wp_id' => $selected_hotel_id,
//         'last_updated' => $last_updated_date
//     ], 200);
// }
