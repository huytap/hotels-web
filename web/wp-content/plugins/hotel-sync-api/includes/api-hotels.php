<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Endpoint để lấy thông tin khách sạn.
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function hotel_sync_get_hotels(WP_REST_Request $request)
{
    // Hàm permission_callback đã xử lý việc xác thực token,
    // nên bạn không cần kiểm tra lại ở đây nữa.

    // ID của site con đã được xác thực
    $current_domain = $_SERVER['HTTP_HOST'];
    $blog_id = get_blog_id_from_url($current_domain);

    // Kiểm tra và chuyển blog
    if (!$blog_id) {
        return new WP_REST_Response(['message' => 'Site not found for this domain.'], 404);
    }
    switch_to_blog($blog_id);

    // Lấy ID của khách sạn
    $selected_hotel_id = (int) get_current_blog_id();
    if ($selected_hotel_id === 0) {
        restore_current_blog();
        return new WP_REST_Response(['message' => 'Hotel not found.'], 404);
    }

    // Kiểm tra plugin Polylang
    if (!function_exists('pll_languages_list')) {
        restore_current_blog();
        return new WP_REST_Response(['message' => 'Polylang is required to retrieve language-specific data.'], 500);
    }

    $languages = pll_languages_list();
    $hotels = [];

    // Lấy thông tin khách sạn theo từng ngôn ngữ
    foreach ($languages as $lang) {
        $hotels[] = [
            'id' => $selected_hotel_id,
            'lang' => $lang,
            'name' => get_option("hotel_info_name_{$lang}", ''),
            'address' => get_option("hotel_info_address_{$lang}", ''),
            'phone' => get_option("hotel_info_phone_{$lang}", ''),
            'email' => get_option("hotel_info_email_{$lang}", ''),
            'map' => get_option("hotel_info_map_{$lang}", ''),
        ];
    }

    $last_updated_date = get_lastpostmodified('gmt', 'post');
    restore_current_blog();

    return new WP_REST_Response([
        'success' => true,
        'hotels' => $hotels,
        'wp_id' => $selected_hotel_id,
        'last_updated' => $last_updated_date
    ], 200);
}
// Hàm mới: Đồng bộ dữ liệu khách sạn với Laravel
function sync_hotel_data_to_laravel()
{
    // Lấy dữ liệu khách sạn
    $data = [];
    $languages = function_exists('pll_languages_list') ? pll_languages_list() : ['en'];

    foreach ($languages as $lang) {
        $data[] = [
            'lang' => $lang,
            'name' => get_option("hotel_info_name_{$lang}", ''),
            'address' => get_option("hotel_info_address_{$lang}", ''),
            'phone' => get_option("hotel_info_phone_{$lang}", ''),
            'email' => get_option("hotel_info_email_{$lang}", ''),
            'map' => get_option("hotel_info_map_{$lang}", ''),
        ];
    }

    callApi('hotels', 'PUT', $data);
}
