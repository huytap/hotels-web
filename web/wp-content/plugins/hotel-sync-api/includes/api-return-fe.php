<?php
// OLD ENDPOINT - COMMENTED OUT TO AVOID CONFLICT WITH NEW MULTILINGUAL VERSION
// The new multilingual endpoint is now in api-hotels.php

// // Đăng ký REST API route
// add_action('rest_api_init', function () {
//     register_rest_route('hotel-info/v1', '/domain-config', [
//         'methods'  => 'GET',
//         'callback' => 'hotel_info_get_domain_config',
//         'permission_callback' => '__return_true', // xử lý IP check riêng
//     ]);
// });

// /**
//  * Callback API domain-config (OLD VERSION - REPLACED WITH MULTILINGUAL VERSION)
//  */
// function hotel_info_get_domain_config(WP_REST_Request $request)
// {
//     // --- Hạn chế IP ---
//     $allowed_ips = [
//         '127.0.0.1',
//         //'192.168.1.10',
//         //'203.113.100.50',
//     ];
//     $request_ip = $_SERVER['REMOTE_ADDR'];
//     if (!in_array($request_ip, $allowed_ips)) {
//         return new WP_Error('rest_forbidden_ip', __('Bạn không có quyền truy cập API này.', 'hotel-info'), ['status' => 403]);
//     }

//     // --- Nhận domain từ request ---
//     $domain = sanitize_text_field($request->get_param('domain'));
//     if (empty($domain)) {
//         return new WP_Error('missing_domain', __('Thiếu tham số domain.', 'hotel-info'), ['status' => 400]);
//     }
//     $sites = get_sites(['domain' => $domain, 'number' => 1]);
//     if (empty($sites)) {
//         return new WP_Error('domain_not_found', __('Không tìm thấy site với domain này.', 'hotel-info'), ['status' => 404]);
//     }
//     $site   = $sites[0];
//     $wp_id  = (int) $site->blog_id;
//     // --- Lấy cấu hình từ options của main site (blog_id = 1) ---
//     switch_to_blog(1); // sang site chính
//     $all_config = get_option('hotel_sync_api_config', []); // dữ liệu bạn lưu ở admin
//     restore_current_blog();

//     $config = $all_config['subsites'][$wp_id] ?? null;

//     if (!$config || empty($config['enabled'])) {
//         return new WP_Error('config_not_found', __('Không tìm thấy cấu hình hoặc site chưa được bật.', 'hotel-info'), ['status' => 404]);
//     }

//     return [
//         'domain'     => $domain,
//         'wp_id'      => $wp_id,
//         'hotel_name' => $config['hotel_name'],
//         'token'      => $config['token'],
//         'enabled'    => $config['enabled'],
//     ];
// }
