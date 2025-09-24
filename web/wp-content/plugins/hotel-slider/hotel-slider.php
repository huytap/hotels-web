<?php

/**
 * Plugin Name: Hotel Slider Manager
 * Description: Tạo custom post type "Slider" với số sao và nút CTA.
 * Version: 1.0
 * Author: Tap Nguyen
 */

// Đăng ký post type
add_action('init', 'hotel_register_slider_post_type');
function hotel_register_slider_post_type()
{
    $labels = array(
        'name' => 'Sliders',
        'singular_name' => 'Slider',
        'menu_name' => 'Slider Homepage',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'menu_icon' => 'dashicons-images-alt2',
        'menu_position'      => 2,
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => false,
        'rewrite' => array('slug' => 'slider'),
        'show_in_rest' => true, // nếu bạn dùng Gutenberg
    );

    register_post_type('slider', $args);
}

// Tạo meta box
add_action('add_meta_boxes', 'hotel_add_slider_meta_boxes');
function hotel_add_slider_meta_boxes()
{
    add_meta_box(
        'slider_extra_fields',
        'Thông tin bổ sung cho Slider',
        'hotel_slider_meta_box_callback',
        'slider',
        'normal',
        'high'
    );
}

// Nội dung meta box
function hotel_slider_meta_box_callback($post)
{
    // Nonce
    wp_nonce_field('hotel_slider_save_meta', 'hotel_slider_meta_nonce');

    $stars = get_post_meta($post->ID, '_hotel_slider_stars', true);
    $cta_text = get_post_meta($post->ID, '_hotel_slider_cta_text', true);
    $cta_link = get_post_meta($post->ID, '_hotel_slider_cta_link', true);
?>
    <p>
        <label for="hotel_slider_stars"><strong>Khách sạn mấy sao?</strong> (1-5):</label><br>
        <input type="number" min="1" max="5" name="hotel_slider_stars" id="hotel_slider_stars"
            value="<?php echo esc_attr($stars); ?>" />
    </p>
    <p>
        <label for="hotel_slider_cta_text"><strong>Nội dung nút CTA:</strong></label><br>
        <input type="text" name="hotel_slider_cta_text" id="hotel_slider_cta_text"
            value="<?php echo esc_attr($cta_text); ?>" style="width: 100%;" />
    </p>
    <p>
        <label for="hotel_slider_cta_link"><strong>Liên kết nút CTA:</strong></label><br>
        <input type="url" name="hotel_slider_cta_link" id="hotel_slider_cta_link"
            value="<?php echo esc_url($cta_link); ?>" style="width: 100%;" />
    </p>
<?php
}

// Lưu dữ liệu
add_action('save_post', 'hotel_save_slider_meta_boxes');
function hotel_save_slider_meta_boxes($post_id)
{
    if (
        !isset($_POST['hotel_slider_meta_nonce']) ||
        !wp_verify_nonce($_POST['hotel_slider_meta_nonce'], 'hotel_slider_save_meta')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['hotel_slider_stars'])) {
        update_post_meta($post_id, '_hotel_slider_stars', intval($_POST['hotel_slider_stars']));
    }

    if (isset($_POST['hotel_slider_cta_text'])) {
        update_post_meta($post_id, '_hotel_slider_cta_text', sanitize_text_field($_POST['hotel_slider_cta_text']));
    }

    if (isset($_POST['hotel_slider_cta_link'])) {
        update_post_meta($post_id, '_hotel_slider_cta_link', esc_url_raw($_POST['hotel_slider_cta_link']));
    }
}
