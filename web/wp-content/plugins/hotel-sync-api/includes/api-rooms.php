<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to clean and format amenity strings.
 * Chuyển các ký tự xuống dòng và dấu gạch chéo thành dấu phẩy.
 */
function format_amenities_string($input_string)
{
    if (!is_string($input_string)) {
        return '';
    }
    // Thay thế tất cả các ký tự xuống dòng và gạch chéo bằng dấu phẩy
    $cleaned_string = str_replace(['\r\n', '\n', ' / '], ', ', $input_string);
    // Tách chuỗi thành mảng, loại bỏ khoảng trắng thừa và các phần tử rỗng
    $parts = array_map('trim', explode(',', $cleaned_string));
    $filtered_parts = array_filter($parts);
    // Nối lại thành chuỗi phân cách bằng dấu phẩy và khoảng trắng
    return implode(', ', $filtered_parts);
}
/**
 * Đồng bộ một phòng cụ thể với Laravel API.
 * @param int $post_id ID của bài viết phòng.
 */
function hotel_sync_get_rooms($post_id)
{
    $post = get_post($post_id);
    if (!$post || 'room' !== $post->post_type) {
        return;
    }
    // Lấy ID đồng bộ chung từ post meta
    $sync_id = get_post_meta($post_id, '_hotel_room_sync_id', true);
    // Nếu chưa có ID đồng bộ, tạo một cái mới
    if (empty($sync_id)) {
        $sync_id = wp_generate_uuid4(); // Hoặc hàm tạo ID khác
        update_post_meta($post_id, '_hotel_room_sync_id', $sync_id);
    }
    $current_blog_id = get_current_blog_id();
    $last_updated = $post->post_modified_gmt;

    // Lấy ID của các bản dịch
    $languages = function_exists('pll_languages_list') ? pll_languages_list() : ['vi', 'en'];
    //khởi tạo đủ ngôn ngữ
    $room_translatable_data = [];
    foreach ($languages as $lang) {
        // Lấy dữ liệu từ ID bài viết đã được dịch
        $room_translatable_data[$lang] = [
            'title'  => '',
            'content' => '',
            'area'    => '',
            'adult'   => '',
            'children'   => '',
            'bed_type'   => '',
            'extrabed' => '',
            'amenities'   => '',
            'room_amenities' => '',
            'bathroom_amenities' => '',
            'view'    => '',
            'price'    => '',
        ];
    }
    $translations = function_exists('pll_get_post_translations') ? pll_get_post_translations($post_id) : [];
    $area     = '';
    $adult     = '';
    $children     = '';
    $extrabed = '';
    foreach ($translations as $lang => $translated_post_id) {
        $raw_content = get_the_content(null, false, $translated_post_id);
        // Loại bỏ khoảng trắng và xuống dòng thừa trong HTML
        $clean_content = trim(preg_replace('/\s+/', ' ', $raw_content));

        $area     = get_post_meta($translated_post_id, '_hotel_room_area', true);
        $adult     = get_post_meta($translated_post_id, '_hotel_room_adult', true);
        $children     = get_post_meta($translated_post_id, '_hotel_room_children', true);
        $extrabed = get_post_meta($translated_post_id, '_hotel_room_extrabed', true);
        $room_translatable_data[$lang] = [
            'wp_post_id'         => $translated_post_id,
            'title'              => get_the_title($translated_post_id),
            'content'            => $clean_content,
            'amenities'          => format_amenities_string(get_post_meta($translated_post_id, '_hotel_room_main_amenities', true)),
            'room_amenities'     => format_amenities_string(get_post_meta($translated_post_id, '_hotel_room_amenities', true)),
            'bathroom_amenities' => format_amenities_string(get_post_meta($translated_post_id, '_hotel_room_bathroom_amenities', true)),
            'view'               => get_post_meta($translated_post_id, '_hotel_room_view', true),
            'price'              => get_post_meta($translated_post_id, '_hotel_room_price', true),
            'bed_type' => get_post_meta($translated_post_id, '_hotel_room_bed_type', true)
        ];
    }

    // Lấy dữ liệu chung không dịch
    $featured_image_id = get_post_thumbnail_id($post_id);
    $gallery_ids_string = get_post_meta($post_id, '_hotel_room_gallery', true);
    $gallery_ids = array_map('trim', explode(',', $gallery_ids_string));
    $common_data = [
        'sync_id' => $sync_id, // Gửi ID đồng bộ
        'featured_image' => $featured_image_id ? wp_get_attachment_url($featured_image_id) : null,
        'gallery_images' => (is_array($gallery_ids) && !empty($gallery_ids)) ? array_map('wp_get_attachment_url', $gallery_ids) : [],
        'area' => $area,
        'adult' => $adult,
        'children' => $children,
        'extrabed' => $extrabed
    ];

    // Chuẩn bị dữ liệu để gửi
    $data = [
        'translatable' => $room_translatable_data,
        'common'  => $common_data,
    ];
    callApi('rooms', 'PUT', $data);
}
