<?php
/*
Plugin Name: Add Contact Form Home
Description: Cho phép chọn nhiều danh mục dịch vụ từ trang làm trang chủ, sắp xếp thứ tự và hiển thị dịch vụ theo thứ tự đó.
Version: 1.1
Author: Tap Nguyen
Text Domain: hotel
*/
// Thêm meta box nhập shortcode form liên hệ cho page (chỉ cho trang chủ)
function hcf_add_meta_box()
{
    // Lấy ID trang chủ đang set
    $frontpage_id = get_option('page_on_front');
    if (!$frontpage_id) return;

    add_meta_box(
        'hcf_contact_form_meta_box',
        'Shortcode Form liên hệ cho Trang Chủ',
        'hcf_contact_form_meta_box_callback',
        'page',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'hcf_add_meta_box');

function hcf_contact_form_meta_box_callback($post)
{
    // Kiểm tra nonce
    wp_nonce_field('hcf_save_contact_form_meta_box', 'hcf_contact_form_meta_box_nonce');

    // Lấy giá trị đã lưu
    $value = get_post_meta($post->ID, '_hcf_contact_form_shortcode', true);

    // Chỉ hiển thị nếu đây là trang chủ
    if ($post->ID != get_option('page_on_front')) {
        echo '<p>Meta box này chỉ dùng cho trang chủ.</p>';
        return;
    }

    echo '<label for="hcf_contact_form_shortcode">Nhập shortcode Contact Form 7 (vd: [contact-form-7 id="123" title="Liên hệ"]):</label>';
    echo '<input type="text" id="hcf_contact_form_shortcode" name="hcf_contact_form_shortcode" value="' . esc_attr($value) . '" style="width:100%;">';
}

// Lưu dữ liệu meta box
function hcf_save_contact_form_meta_box_data($post_id)
{
    // Kiểm tra nonce
    if (!isset($_POST['hcf_contact_form_meta_box_nonce'])) return;
    if (!wp_verify_nonce($_POST['hcf_contact_form_meta_box_nonce'], 'hcf_save_contact_form_meta_box')) return;

    // Kiểm tra quyền
    if (!current_user_can('edit_post', $post_id)) return;

    // Chỉ lưu cho page
    if (get_post_type($post_id) != 'page') return;

    if (isset($_POST['hcf_contact_form_shortcode'])) {
        update_post_meta($post_id, '_hcf_contact_form_shortcode', sanitize_text_field($_POST['hcf_contact_form_shortcode']));
    }
}
add_action('save_post', 'hcf_save_contact_form_meta_box_data');
function hcf_append_contact_form_to_frontpage_content($content)
{
    if (!is_front_page() || !is_main_query()) {
        return $content;
    }

    $frontpage_id = get_option('page_on_front');
    $shortcode = get_post_meta($frontpage_id, '_hcf_contact_form_shortcode', true);
    if ($shortcode) {
        // Chèn shortcode form vào cuối content
        $content .= '<div class="homepage-contact-form" style="margin-top:30px;">' . do_shortcode($shortcode) . '</div>';
    }
    return $content;
}
function hcf_display_contact_form()
{
    if (!is_front_page()) return;

    $frontpage_id = get_option('page_on_front');
    $shortcode = get_post_meta($frontpage_id, '_hcf_contact_form_shortcode', true);
    if (!empty($shortcode)) {
        echo do_shortcode($shortcode);
    }
}
