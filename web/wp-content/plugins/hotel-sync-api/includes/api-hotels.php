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

    // Lấy thông tin KHÔNG theo ngôn ngữ (các trường chung cho tất cả ngôn ngữ)
    $common_fields = [
        'phone' => get_option("hotel_info_phone", ''),
        'email' => get_option("hotel_info_email", ''),
        'map' => get_option("hotel_info_map", ''),
        'domain_name' => get_option("hotel_info_domain_name", ''),
        // Thêm các trường chung khác
        'fax' => get_option("hotel_info_fax", ''),
        'website' => get_option("hotel_info_website", ''),
        'tax_code' => get_option("hotel_info_tax_code", ''),
        'business_license' => get_option("hotel_info_business_license", ''),
        'star_rating' => get_option("hotel_info_star_rating", ''),
        'established_year' => get_option("hotel_info_established_year", ''),
        'total_rooms' => get_option("hotel_info_total_rooms", ''),
        'check_in_time' => get_option("hotel_info_check_in_time", '14:00'),
        'check_out_time' => get_option("hotel_info_check_out_time", '12:00'),
        'currency' => get_option("hotel_info_currency", 'VND'),
        'timezone' => get_option("hotel_info_timezone", 'Asia/Ho_Chi_Minh'),
    ];

    // Lấy thông tin khách sạn theo từng ngôn ngữ
    foreach ($languages as $lang) {
        $multilingual_fields = [
            'name' => get_option("hotel_info_name_{$lang}", ''),
            'address' => get_option("hotel_info_address_{$lang}", ''),
            'policy' => get_option("hotel_info_policy_{$lang}", ''),
            // Thêm các trường đa ngôn ngữ khác
            'description' => get_option("hotel_info_description_{$lang}", ''),
            'short_description' => get_option("hotel_info_short_description_{$lang}", ''),
            'amenities' => get_option("hotel_info_amenities_{$lang}", ''),
            'facilities' => get_option("hotel_info_facilities_{$lang}", ''),
            'services' => get_option("hotel_info_services_{$lang}", ''),
            'nearby_attractions' => get_option("hotel_info_nearby_attractions_{$lang}", ''),
            'transportation' => get_option("hotel_info_transportation_{$lang}", ''),
            'dining_options' => get_option("hotel_info_dining_options_{$lang}", ''),
            'room_features' => get_option("hotel_info_room_features_{$lang}", ''),
            'cancellation_policy' => get_option("hotel_info_cancellation_policy_{$lang}", ''),
            'terms_conditions' => get_option("hotel_info_terms_conditions_{$lang}", ''),
            'special_notes' => get_option("hotel_info_special_notes_{$lang}", ''),
        ];

        $hotels[] = array_merge([
            'id' => $selected_hotel_id,
            'lang' => $lang,
        ], $multilingual_fields, $common_fields);
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
/**
 * Đồng bộ dữ liệu khách sạn với Laravel API
 *
 * CẤU TRÚC DỮ LIỆU:
 *
 * 🌐 TRƯỜNG ĐA NGÔN NGỮ (lưu theo format: hotel_info_[field]_[lang]):
 * - name: Tên khách sạn
 * - address: Địa chỉ
 * - policy: Quy định check-in/check-out
 * - description: Mô tả chi tiết
 * - short_description: Mô tả ngắn
 * - amenities: Tiện nghi
 * - facilities: Cơ sở vật chất
 * - services: Dịch vụ
 * - nearby_attractions: Điểm tham quan gần đó
 * - transportation: Phương tiện di chuyển
 * - dining_options: Lựa chọn ăn uống
 * - room_features: Đặc điểm phòng
 * - cancellation_policy: Chính sách hủy phòng
 * - terms_conditions: Điều khoản & điều kiện
 * - special_notes: Ghi chú đặc biệt
 *
 * 🔄 TRƯỜNG CHUNG (lưu theo format: hotel_info_[field]):
 * - phone: Số điện thoại
 * - email: Email
 * - map: Google Maps iframe
 * - domain_name: Tên miền
 * - fax: Số fax
 * - website: Website chính thức
 * - tax_code: Mã số thuế
 * - business_license: Giấy phép kinh doanh
 * - star_rating: Xếp hạng sao
 * - established_year: Năm thành lập
 * - total_rooms: Tổng số phòng
 * - check_in_time: Giờ check-in
 * - check_out_time: Giờ check-out
 * - currency: Tiền tệ
 * - timezone: Múi giờ
 */
function sync_hotel_data_to_laravel()
{
    // Lấy dữ liệu khách sạn
    $data = [];
    $languages = function_exists('pll_languages_list') ? pll_languages_list() : ['vi', 'en'];

    // Lấy thông tin KHÔNG theo ngôn ngữ (các trường chung cho tất cả ngôn ngữ)
    $common_fields = [
        'phone' => get_option("hotel_info_phone", ''),
        'email' => get_option("hotel_info_email", ''),
        'map' => get_option("hotel_info_map", ''),
        'domain_name' => get_option("hotel_info_domain_name", ''),
        // Thêm các trường chung khác nếu có
        'fax' => get_option("hotel_info_fax", ''),
        'website' => get_option("hotel_info_website", ''),
        'tax_code' => get_option("hotel_info_tax_code", ''),
        'business_license' => get_option("hotel_info_business_license", ''),
        'star_rating' => get_option("hotel_info_star_rating", ''),
        'established_year' => get_option("hotel_info_established_year", ''),
        'total_rooms' => get_option("hotel_info_total_rooms", ''),
        'check_in_time' => get_option("hotel_info_check_in_time", '14:00'),
        'check_out_time' => get_option("hotel_info_check_out_time", '12:00'),
        'currency' => get_option("hotel_info_currency", 'VND'),
        'timezone' => get_option("hotel_info_timezone", 'Asia/Ho_Chi_Minh'),
    ];

    // Lấy thông tin THEO ngôn ngữ cho từng ngôn ngữ
    foreach ($languages as $lang) {
        $multilingual_fields = [
            'name' => get_option("hotel_info_name_{$lang}", ''),
            'address' => get_option("hotel_info_address_{$lang}", ''),
            'policy' => get_option("hotel_info_policy_{$lang}", ''),
            // Thêm các trường đa ngôn ngữ khác
            'description' => get_option("hotel_info_description_{$lang}", ''),
            'short_description' => get_option("hotel_info_short_description_{$lang}", ''),
            'amenities' => get_option("hotel_info_amenities_{$lang}", ''),
            'facilities' => get_option("hotel_info_facilities_{$lang}", ''),
            'services' => get_option("hotel_info_services_{$lang}", ''),
            'nearby_attractions' => get_option("hotel_info_nearby_attractions_{$lang}", ''),
            'transportation' => get_option("hotel_info_transportation_{$lang}", ''),
            'dining_options' => get_option("hotel_info_dining_options_{$lang}", ''),
            'room_features' => get_option("hotel_info_room_features_{$lang}", ''),
            'cancellation_policy' => get_option("hotel_info_cancellation_policy_{$lang}", ''),
            'terms_conditions' => get_option("hotel_info_terms_conditions_{$lang}", ''),
            'special_notes' => get_option("hotel_info_special_notes_{$lang}", ''),
        ];

        $data[] = array_merge([
            'lang' => $lang,
        ], $multilingual_fields, $common_fields);
    }
    callApi('hotels', 'PUT', $data);
}

/**
 * Endpoint mới cho React frontend: domain → wp_id + token + config
 * Endpoint: GET /wp-json/hotel-info/v1/domain-config?domain=localhost:5177&language=vi
 */
function get_hotel_config_by_domain(WP_REST_Request $request)
{
    $domain = $request->get_param('domain');
    $requested_language = $request->get_param('language'); // Ngôn ngữ yêu cầu từ frontend

    if (!$domain) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Domain parameter is required'
        ], 400);
    }

    // Sử dụng cấu trúc hiện tại từ api-return-fe.php
    $sites = get_sites(['domain' => $domain, 'number' => 1]);
    if (empty($sites)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Không tìm thấy site với domain này.'
        ], 404);
    }
    $site = $sites[0];
    $blog_id = (int) $site->blog_id;

    // Switch to the hotel's blog
    if ($blog_id !== get_current_blog_id()) {
        switch_to_blog($blog_id);
    }

    // Phát hiện ngôn ngữ hiện tại từ WordPress admin
    $current_admin_language = 'vi'; // Default

    // Method 1: Kiểm tra từ Polylang (nếu có)
    if (function_exists('pll_current_language')) {
        $pll_current = pll_current_language();
        if ($pll_current) {
            $current_admin_language = $pll_current;
        }
    }

    // Method 2: Kiểm tra từ WPML (nếu có)
    if (function_exists('icl_get_current_language')) {
        $wpml_current = icl_get_current_language();
        if ($wpml_current) {
            $current_admin_language = $wpml_current;
        }
    }

    // Method 3: Kiểm tra từ WordPress locale
    $wp_locale = get_locale();
    if ($wp_locale) {
        // Convert locale to language code
        $locale_map = [
            'vi' => 'vi',
            'vi_VN' => 'vi',
            'en_US' => 'en',
            'en_GB' => 'en',
            'zh_CN' => 'zh',
            'ja' => 'ja',
            'ko_KR' => 'ko'
        ];
        if (isset($locale_map[$wp_locale])) {
            $current_admin_language = $locale_map[$wp_locale];
        }
    }

    // Sử dụng ngôn ngữ được yêu cầu hoặc ngôn ngữ hiện tại
    $language = $requested_language ?: $current_admin_language;

    // Lấy danh sách ngôn ngữ có sẵn từ Polylang
    $available_languages = [];

    // Kiểm tra Polylang
    if (function_exists('pll_languages_list')) {
        $pll_languages = pll_languages_list();
        if (!empty($pll_languages)) {
            $available_languages = $pll_languages;
        }
    }

    // Nếu không có Polylang hoặc không có ngôn ngữ, fallback về mặc định
    if (empty($available_languages)) {
        $available_languages = ['vi']; // Chỉ Vietnamese mặc định
    }

    // Lấy cấu hình từ options của main site (blog_id = 1) - theo cấu trúc hiện tại
    switch_to_blog(1); // sang site chính
    $all_config = get_option('hotel_sync_api_config', []); // dữ liệu từ admin
    restore_current_blog();

    $config = $all_config['subsites'][$blog_id] ?? null;

    if (!$config || empty($config['enabled'])) {
        // Restore blog if switched
        if ($blog_id !== get_current_blog_id()) {
            restore_current_blog();
        }
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Không tìm thấy cấu hình hoặc site chưa được bật.'
        ], 404);
    }

    // Sử dụng wp_id và token từ cấu trúc hiện tại
    $wp_id = $blog_id;
    $api_token = $config['token'];

    // Sử dụng thông tin từ cấu trúc hiện tại
    $hotel_name = $config['hotel_name'] ?? 'Hotel Name';

    // Get multilingual data từ options (nếu có)
    $hotel_name_lang = get_option("hotel_info_name_{$language}", $hotel_name);
    $address = get_option("hotel_info_address_{$language}", 'Hotel Address');
    $policy = get_option("hotel_info_policy_{$language}", '');

    // Get non-multilingual data
    $phone = get_option("hotel_info_phone", '+84 28 1234 5678');
    $email = get_option("hotel_info_email", 'info@hotel.com');
    $domain_name = get_option("hotel_info_domain_name", $domain);

    $hotel_config = [
        // THEO ngôn ngữ
        'hotel_name' => $hotel_name_lang,
        'address' => $address,
        'description' => get_option("hotel_info_description_{$language}", 'Hotel description'),
        'cancellation_policy' => get_option("hotel_info_cancellation_{$language}", 'Free cancellation'),
        'check_in_out_policy' => $policy, // Quy định check-in/check-out theo ngôn ngữ

        // KHÔNG theo ngôn ngữ
        'domain' => $domain_name,
        'currency' => 'VND',
        'timezone' => 'Asia/Ho_Chi_Minh',
        'language' => $language,
        'available_languages' => $available_languages,
        'current_admin_language' => $current_admin_language,
        'theme' => [
            'primary_color' => '#e11d48',
            'logo' => get_hotel_logo_url($domain)
        ],
        'contact' => [
            'phone' => $phone,     // KHÔNG đổi theo ngôn ngữ
            'email' => $email,     // KHÔNG đổi theo ngôn ngữ
            'address' => $address  // THEO ngôn ngữ
        ],
        'policies' => [
            'check_in' => '14:00',
            'check_out' => '12:00',
            'check_in_out_policy' => $policy, // Chi tiết quy định check-in/check-out
            'cancellation' => get_option("hotel_info_cancellation_{$language}", 'Free cancellation')
        ],
        'api_base_url' => 'http://localhost:8000/api'
    ];

    // Restore blog if switched
    if ($blog_id !== get_current_blog_id()) {
        restore_current_blog();
    }

    return new WP_REST_Response([
        'success' => true,
        'wp_id' => $wp_id,
        'api_token' => $api_token,
        'config' => $hotel_config,
        'language_info' => [
            'current_language' => $language,
            'admin_language' => $current_admin_language,
            'available_languages' => $available_languages,
            'requested_language' => $requested_language
        ]
    ], 200);
}

/**
 * Helper function to get hotel logo URL from various sources
 * Converts URL to use correct subdomain
 */
function get_hotel_logo_url($target_domain = null)
{
    $logo_url = '';

    // Method 1: Custom logo from WordPress Customizer
    if (function_exists('get_custom_logo')) {
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        }
    }

    // Method 2: Site logo from WordPress 5.8+
    if (empty($logo_url) && function_exists('get_site_icon_url')) {
        $site_logo_id = get_theme_mod('site_logo');
        if ($site_logo_id) {
            $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
        }
    }

    // Method 3: Site icon (favicon) as fallback
    if (empty($logo_url)) {
        $site_icon_url = get_site_icon_url();
        if ($site_icon_url) {
            $logo_url = $site_icon_url;
        }
    }

    // Method 4: Custom option from hotel plugin
    if (empty($logo_url)) {
        $logo_url = get_option('hotel_logo_url', '');
    }

    // Method 5: Theme mod for logo
    if (empty($logo_url)) {
        $logo_url = get_theme_mod('logo_url', '');
    }

    // Method 6: Header image as last resort
    if (empty($logo_url)) {
        $header_image = get_header_image();
        if ($header_image) {
            $logo_url = $header_image;
        }
    }

    // Convert URL to use correct subdomain if target_domain is provided
    if (!empty($logo_url) && !empty($target_domain)) {
        $logo_url = convert_url_to_subdomain($logo_url, $target_domain);
    }

    return $logo_url;
}

/**
 * Convert URL from main domain to subdomain
 */
function convert_url_to_subdomain($url, $target_domain)
{
    if (empty($url) || empty($target_domain)) {
        return $url;
    }

    // Parse the original URL
    $parsed_url = parse_url($url);
    if (!$parsed_url || !isset($parsed_url['host'])) {
        return $url;
    }

    // Check if it's already using the correct domain
    if ($parsed_url['host'] === $target_domain) {
        return $url;
    }

    // Replace the host with target domain
    $new_url = $parsed_url['scheme'] . '://' . $target_domain;

    if (isset($parsed_url['port'])) {
        $new_url .= ':' . $parsed_url['port'];
    }

    if (isset($parsed_url['path'])) {
        $new_url .= $parsed_url['path'];
    }

    if (isset($parsed_url['query'])) {
        $new_url .= '?' . $parsed_url['query'];
    }

    if (isset($parsed_url['fragment'])) {
        $new_url .= '#' . $parsed_url['fragment'];
    }

    return $new_url;
}
