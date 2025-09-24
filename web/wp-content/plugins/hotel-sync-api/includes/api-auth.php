<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hàm kiểm tra quyền hạn chung cho các API.
 * @param WP_REST_Request $request
 * @return bool|WP_Error Trả về true nếu xác thực thành công, WP_Error nếu thất bại.
 */
function hotel_sync_verify_api_permission_callback(WP_REST_Request $request)
{
    // 1. Lấy token từ header Authorization
    $auth_header = $request->get_header('Authorization');
    if (empty($auth_header) || stripos($auth_header, 'Bearer ') !== 0) {
        return new WP_Error('rest_unauthorized', 'Missing or invalid Authorization header.', ['status' => 401]);
    }
    $provided_token = trim(substr($auth_header, 7));

    // 2. Lấy ID của site con từ request parameter
    $blog_id = $request->get_param('wp_id');
    if (!$blog_id) {
        // Lấy ID của site con từ tên miền hiện tại
        $current_domain = $_SERVER['HTTP_HOST'];
        $blog_id = get_blog_id_from_url($current_domain);
    }
    if (empty($blog_id) || !is_numeric($blog_id)) {
        return new WP_Error('rest_invalid_param', 'Missing or invalid subsite ID in request.', ['status' => 400]);
    }

    // 3. Lấy cấu hình token từ database của site chính (multisite)
    switch_to_blog(get_main_site_id());
    $config = get_option("hotel_sync_api_config", ['subsites' => []]);
    $site_conf = $config['subsites'][$blog_id] ?? null;
    restore_current_blog();

    // 4. Kiểm tra token và trạng thái kích hoạt
    if (!$site_conf || empty($site_conf['token']) || empty($site_conf['enabled']) || $site_conf['token'] !== $provided_token) {
        return new WP_Error('rest_forbidden', 'Invalid token or synchronization is disabled for this site.', ['status' => 403]);
    }

    // Nếu tất cả các kiểm tra đều thành công, cho phép request đi tiếp
    return true;
}
