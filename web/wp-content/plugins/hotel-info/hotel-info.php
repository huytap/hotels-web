<?php
/*
Plugin Name: Hotel Info Manager
Description: Plugin để nhập và hiển thị thông tin khách sạn theo ngôn ngữ (hỗ trợ Polylang).
Version: 1.1
Author: Tap Nguyen
Text Domain: hotel
*/

// Thêm menu quản trị
add_action('admin_menu', function () {
    add_menu_page(
        'Thông tin Khách sạn',
        'Thông tin KS',
        'manage_options',
        'hotel-info-settings',
        'hotel_info_settings_page',
        'dashicons-building',
        2
    );
});

// Trang cài đặt thông tin khách sạn
function hotel_info_settings_page()
{
    if (!function_exists('pll_languages_list')) {
        echo '<div class="notice notice-error"><p>Plugin cần Polylang để hoạt động.</p></div>';
        return;
    }

    $languages = pll_languages_list(); // ['vi', 'en', ...]
    $active_lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : pll_default_language();
    if (!in_array($active_lang, $languages)) {
        $active_lang = pll_default_language();
    }

    // Xử lý lưu dữ liệu
    if (isset($_POST['hotel_info_nonce']) && wp_verify_nonce($_POST['hotel_info_nonce'], 'save_hotel_info')) {
        update_option("hotel_info_name_{$active_lang}", sanitize_text_field($_POST['hotel_info_name']));
        update_option("hotel_info_address_{$active_lang}", sanitize_text_field($_POST['hotel_info_address']));
        update_option("hotel_info_phone_{$active_lang}", sanitize_text_field($_POST['hotel_info_phone']));
        update_option("hotel_info_email_{$active_lang}", sanitize_email($_POST['hotel_info_email']));
        update_option("hotel_info_map_{$active_lang}", wp_kses($_POST['hotel_info_map'], array(
            'iframe' => array(
                'src'             => true,
                'width'           => true,
                'height'          => true,
                'frameborder'     => true,
                'allowfullscreen' => true,
                'loading'         => true,
                'referrerpolicy'  => true,
            )
        )));

        // Gọi hàm đồng bộ dữ liệu sau khi lưu thành công
        if (function_exists('sync_hotel_data_to_laravel')) {
            sync_hotel_data_to_laravel();
        }
        echo '<div class="updated"><p>Đã lưu thông tin cho ngôn ngữ: <strong>' . esc_html($active_lang) . '</strong></p></div>';
    }

    // Lấy dữ liệu
    $name    = get_option("hotel_info_name_{$active_lang}", '');
    $address = get_option("hotel_info_address_{$active_lang}", '');
    $phone   = get_option("hotel_info_phone_{$active_lang}", '');
    $email   = get_option("hotel_info_email_{$active_lang}", '');
    $map     = get_option("hotel_info_map_{$active_lang}", '');

    echo '<div class="wrap">';
    echo '<h1>Thông tin khách sạn</h1>';

    // Tabs chọn ngôn ngữ
    echo '<h2 class="nav-tab-wrapper">';
    foreach ($languages as $lang_code) {
        $class = ($lang_code === $active_lang) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $url = admin_url('admin.php?page=hotel-info-settings&lang=' . $lang_code);
        echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">' . strtoupper($lang_code) . '</a>';
    }
    echo '</h2>';

    echo '<form method="post">';
    wp_nonce_field('save_hotel_info', 'hotel_info_nonce');
?>
    <table class="form-table">
        <tr>
            <th><label for="hotel_info_name">Tên khách sạn</label></th>
            <td><input type="text" name="hotel_info_name" id="hotel_info_name" class="regular-text" value="<?php echo esc_attr($name); ?>"></td>
        </tr>
        <tr>
            <th><label for="hotel_info_address">Địa chỉ</label></th>
            <td><input type="text" name="hotel_info_address" id="hotel_info_address" class="regular-text" value="<?php echo esc_attr($address); ?>"></td>
        </tr>
        <tr>
            <th><label for="hotel_info_phone">Số điện thoại</label></th>
            <td><input type="text" name="hotel_info_phone" id="hotel_info_phone" class="regular-text" value="<?php echo esc_attr($phone); ?>"></td>
        </tr>
        <tr>
            <th><label for="hotel_info_email">Email</label></th>
            <td><input type="email" name="hotel_info_email" id="hotel_info_email" class="regular-text" value="<?php echo esc_attr($email); ?>"></td>
        </tr>
        <tr>
            <th><label for="hotel_info_map">Iframe Google Map</label></th>
            <td><textarea name="hotel_info_map" id="hotel_info_map" class="large-text code" rows="5"><?php echo esc_textarea($map); ?></textarea></td>
        </tr>
    </table>
<?php
    submit_button('Lưu thông tin');
    echo '</form></div>';
}
// Hàm hiển thị frontend theo ngôn ngữ
function hotel_info_display()
{
    $lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';

    $name    = get_option("hotel_info_name_{$lang}", '');
    $address = get_option("hotel_info_address_{$lang}", '');
    $phone   = get_option("hotel_info_phone_{$lang}", '');
    $email   = get_option("hotel_info_email_{$lang}", '');
    $map     = get_option("hotel_info_map_{$lang}", '');

    if (!$name && !$address && !$phone && !$email && !$map) return;

    if ($name) echo '<h3>' . esc_html($name) . '</h3>';

    echo '<ul class="list-unstyled contact-list">';
    if ($address) {
        echo '<li><strong>' . esc_html__('Địa chỉ:', 'hotel-info-plugin') . '</strong> ' . esc_html($address) . '</li>';
    }
    if ($phone) {
        echo '<li><strong>' . esc_html__('Điện thoại:', 'hotel-info-plugin') . '</strong> <a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></li>';
    }
    if ($email) {
        echo '<li><strong>' . esc_html__('Email:', 'hotel-info-plugin') . '</strong> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></li>';
    }
    echo '</ul>';

    if ($map) {
        echo $map;
    }
}

// Shortcode gọi từ trình soạn thảo: [hotel_info]
add_shortcode('hotel_info', function () {
    ob_start();
    hotel_info_display();
    return ob_get_clean();
});
