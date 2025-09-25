<?php

/**
 * Hotel Theme Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
add_filter('allowed_themes', function ($allowed_themes) {
    $site_id = get_current_blog_id();

    if ($site_id == 1) {
        // Site chính: cho phép dùng tất cả theme (hoặc theme chính)
        // Trả về false để không giới hạn theme cho site chính
        return $allowed_themes; // hoặc bạn có thể return false để mặc định không giới hạn
    } else {
        // Site con: chỉ cho phép theme con (thay 'theme-con' bằng folder name theme con)
        return array(
            'theme-con' => true,
        );
    }
});

/**
 * Theme setup
 */
function hotel_theme_setup()
{
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('custom-logo');
    add_theme_support('customize-selective-refresh-widgets');

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'hotel-theme'),
        'footer' => __('Footer Menu', 'hotel-theme'),
    ));
}
add_action('after_setup_theme', 'hotel_theme_setup');

/**
 * Enqueue scripts and styles
 */
function hotel_theme_scripts()
{
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap', array(), null);

    // Bootstrap CSS (đường dẫn trong thư mục theme)
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.css', array(), '1.0');

    // Fonts CSS
    wp_enqueue_style('fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), '1.0');
    // Style chính với version cache busting
    wp_enqueue_style('theme-style', get_template_directory_uri() . '/style.css', array(), '001');
    wp_enqueue_script('core-js', get_template_directory_uri() . '/assets/js/core.min.js', array('jquery'), null, true);
    wp_enqueue_script('script', get_template_directory_uri() . '/assets/js/script.js', array('jquery'), null, true);
    // Theme JavaScript
    wp_enqueue_script(
        'hotel-theme-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize script for AJAX
    wp_localize_script('hotel-theme-script', 'hotel_theme', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hotel_theme_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'hotel_theme_scripts');

/**
 * Add cache control headers for better performance
 */
function hotel_theme_add_cache_headers()
{
    if (!is_admin()) {
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    }
}
add_action('init', 'hotel_theme_add_cache_headers');

/**
 * Customize excerpt length
 */
function hotel_theme_excerpt_length($length)
{
    return 30;
}
add_filter('excerpt_length', 'hotel_theme_excerpt_length');

/**
 * Customize excerpt more
 */
function hotel_theme_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'hotel_theme_excerpt_more');

/**
 * Add custom image sizes
 */
function hotel_theme_image_sizes()
{
    add_image_size('room-thumbnail', 400, 300, true);
    add_image_size('room-large', 800, 600, true);
    add_image_size('service-thumbnail', 300, 200, true);
    add_image_size('hero-slider', 1200, 600, true);
}
add_action('after_setup_theme', 'hotel_theme_image_sizes');

/**
 * Register widget areas
 */
function hotel_theme_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Sidebar', 'hotel-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'hotel-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 1', 'hotel-theme'),
        'id'            => 'footer-1',
        'description'   => __('Footer area 1', 'hotel-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 2', 'hotel-theme'),
        'id'            => 'footer-2',
        'description'   => __('Footer area 2', 'hotel-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 3', 'hotel-theme'),
        'id'            => 'footer-3',
        'description'   => __('Footer area 3', 'hotel-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 4', 'hotel-theme'),
        'id'            => 'footer-4',
        'description'   => __('Footer area 4', 'hotel-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'hotel_theme_widgets_init');

/**
 * Get hotel information from customizer
 */
function get_hotel_info($key, $default = '')
{
    return get_theme_mod($key, $default);
}

/**
 * Display hotel contact info
 */
function hotel_contact_info()
{
    $phone = get_hotel_info('hotel_phone');
    $email = get_hotel_info('hotel_email');
    $address = get_hotel_info('hotel_address');

    if ($phone || $email || $address) {
        echo '<div class="hotel-contact-info">';
        if ($phone) echo '<div class="contact-phone">📞 ' . esc_html($phone) . '</div>';
        if ($email) echo '<div class="contact-email">📧 ' . esc_html($email) . '</div>';
        if ($address) echo '<div class="contact-address">📍 ' . esc_html($address) . '</div>';
        echo '</div>';
    }
}

/**
 * Add body classes for better styling
 */
function hotel_theme_body_classes($classes)
{
    // Add class for multisite
    if (is_multisite()) {
        $classes[] = 'multisite';
    }

    // Add class for hotel management plugin
    if (class_exists('HotelManagementSystem')) {
        $classes[] = 'has-hotel-management';
    }

    return $classes;
}
add_filter('body_class', 'hotel_theme_body_classes');

/**
 * Enable shortcodes in widgets
 */
add_filter('widget_text', 'do_shortcode');

/**
 * Remove unnecessary WordPress features
 */
function hotel_theme_clean_wp()
{
    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Remove unnecessary meta tags
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
}
add_action('init', 'hotel_theme_clean_wp');

/**
 * Optimize WordPress for hotel theme
 */
function hotel_theme_optimize()
{
    // Disable file editing in admin
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }

    // Increase memory limit if needed
    if (!ini_get('memory_limit') || ini_get('memory_limit') < '256M') {
        ini_set('memory_limit', '256M');
    }
}
add_action('init', 'hotel_theme_optimize');

/**
 * Custom pagination function
 */
function hotel_theme_pagination()
{
    global $wp_query;

    $big = 999999999;

    echo paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages,
        'prev_text' => '&laquo; ' . __('Previous', 'hotel-theme'),
        'next_text' => __('Next', 'hotel-theme') . ' &raquo;',
    ));
}

/**
 * Breadcrumb function
 */
function hotel_theme_breadcrumb()
{
    if (!is_home()) {
        echo '<nav class="breadcrumb">';
        echo '<a href="' . home_url() . '">' . __('Home', 'hotel-theme') . '</a>';

        if (is_category() || is_single()) {
            echo ' &raquo; ';
            the_category(' &bull; ');
            if (is_single()) {
                echo ' &raquo; ';
                the_title();
            }
        } elseif (is_page()) {
            echo ' &raquo; ';
            echo the_title();
        }

        echo '</nav>';
    }
}
// ====== HOTEL THEME CUSTOMIZER ======
function hotel_theme_customize_register($wp_customize)
{
    // Panel chính
    $wp_customize->add_panel('hotel_settings_panel', [
        'title'       => __('Hotel Settings', 'hotel-theme'),
        'priority'    => 10,
    ]);

    // Section màu sắc
    $wp_customize->add_section('hotel_colors_section', [
        'title'    => __('Màu sắc', 'hotel-theme'),
        'panel'    => 'hotel_settings_panel',
    ]);

    // Header background
    $wp_customize->add_setting('hotel_header_bg', [
        'default'   => '#ffffff',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hotel_header_bg', [
        'label'   => __('Màu nền Header', 'hotel-theme'),
        'section' => 'hotel_colors_section',
    ]));

    // Footer background
    $wp_customize->add_setting('hotel_footer_bg', [
        'default'   => '#000000',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hotel_footer_bg', [
        'label'   => __('Màu nền Footer', 'hotel-theme'),
        'section' => 'hotel_colors_section',
    ]));

    // Section font
    $wp_customize->add_section('hotel_fonts_section', [
        'title'    => __('Font chữ', 'hotel-theme'),
        'panel'    => 'hotel_settings_panel',
    ]);

    // Font chữ
    $wp_customize->add_setting('hotel_font_family', [
        'default'   => 'Arial, sans-serif',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('hotel_font_family', [
        'label'   => __('Font chữ', 'hotel-theme'),
        'type'    => 'text',
        'section' => 'hotel_fonts_section',
        'description' => __('Nhập tên font, ví dụ: Arial, "Roboto", "Times New Roman"', 'hotel-theme')
    ]);

    // Font size
    $wp_customize->add_setting('hotel_font_size', [
        'default'   => '16px',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('hotel_font_size', [
        'label'   => __('Font size', 'hotel-theme'),
        'type'    => 'text',
        'section' => 'hotel_fonts_section',
        'description' => __('Nhập cỡ chữ (ví dụ: 16px, 1rem)', 'hotel-theme')
    ]);

    // Section Google Analytics
    // $wp_customize->add_section('hotel_ga_section', [
    //     'title'    => __('Google Analytics', 'hotel-theme'),
    //     'panel'    => 'hotel_settings_panel',
    //     'priority' => 30,
    // ]);

    // Setting Google Analytics
    $wp_customize->add_setting('hotel_google_analytics', [
        'default'   => '',
        'transport' => 'refresh',
    ]);

    // Control Google Analytics
    $wp_customize->add_control('hotel_google_analytics', [
        'label'       => __('Google Analytics Code', 'hotel-theme'),
        'type'        => 'textarea',
        'section'     => 'hotel_ga_section',
        'description' => __('Dán code Google Analytics (script) vào đây', 'hotel-theme')
    ]);

    // Section Booking bên thứ 3
    $wp_customize->add_section('hotel_booking_section', [
        'title'    => __('Third-party Booking', 'hotel-theme'),
        'panel'    => 'hotel_settings_panel',
        'priority' => 40,
    ]);

    // Setting Booking
    $wp_customize->add_setting('hotel_booking_code', [
        'default'   => '',
        'transport' => 'refresh',
    ]);

    // Control Booking
    $wp_customize->add_control('hotel_booking_code', [
        'label'       => __('Booking Code', 'hotel-theme'),
        'type'        => 'textarea',
        'section'     => 'hotel_booking_section',
        'description' => __('Dán mã Booking của bên thứ 3 vào đây', 'hotel-theme')
    ]);
}
add_action('customize_register', 'hotel_theme_customize_register');
function hotel_insert_ga_code()
{
    $ga_code = get_theme_mod('hotel_google_analytics', '');
    if ($ga_code) {
        echo $ga_code;
    }
}
add_action('wp_head', 'hotel_insert_ga_code');


// ====== INJECT CSS RA FRONTEND ======
function hotel_theme_customizer_css()
{
    $header_bg = get_theme_mod('hotel_header_bg', '#ffffff');
    $footer_bg = get_theme_mod('hotel_footer_bg', '#000000');
    $font_family = get_theme_mod('hotel_font_family', 'Arial, sans-serif');
    $font_size   = get_theme_mod('hotel_font_size', '16px');

    echo "<style>
        .rd-navbar-aside.rd-navbar-static.rd-navbar--is-stuck { background-color: {$header_bg}; }
        .footer-minimal { background-color: {$footer_bg}; }
        body { font-family: {$font_family}; font-size: {$font_size}; }
    </style>";
}
add_action('wp_head', 'hotel_theme_customizer_css');
//load text domain
function hotel_load_textdomain()
{
    load_theme_textdomain('hotel', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'hotel_load_textdomain');

//giới thiệu chung hạng phòng
function hotel_customize_register($wp_customize)
{
    $wp_customize->add_section('hotel_room_intro_section', array(
        'title'    => __('Phòng nghỉ', 'hotel'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('hotel_room_intro_text', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('hotel_room_intro_text', array(
        'label'   => __('Nội dung giới thiệu', 'hotel'),
        'section' => 'hotel_room_intro_section',
        'type'    => 'textarea',
    ));
}
add_action('customize_register', 'hotel_customize_register');

// Đăng ký chuỗi cho Polylang
function hotel_register_polylang_strings()
{
    $intro = get_theme_mod('hotel_room_intro_text', '');
    if ($intro) {
        pll_register_string('hotel_room_intro_text', $intro, 'Hotel');
    }
}
add_action('init', 'hotel_register_polylang_strings');
// Tự động tắt bình luận khi tạo post mới
add_action('wp_insert_post', function ($post_id, $post, $update) {
    // Chỉ áp dụng khi tạo bài viết mới (không áp dụng khi update)
    if ($update) return;

    // Chỉ áp dụng cho post (không áp dụng cho page hoặc CPT khác)
    if ($post->post_type === 'post') {
        // Tắt bình luận và trackback
        wp_update_post(array(
            'ID' => $post_id,
            'comment_status' => 'closed',
            'ping_status'    => 'closed'
        ));
    }
}, 10, 3);

// 1. Tắt hỗ trợ comment cho tất cả post types
function disable_comments_post_types_support()
{
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}
add_action('admin_init', 'disable_comments_post_types_support');

// 2. Ẩn menu Bình luận trong admin
function remove_comments_admin_menu()
{
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'remove_comments_admin_menu');

// 3. Hủy bỏ bình luận mới gửi
function disable_comments_post_open($open, $post_id)
{
    return false;
}
add_filter('comments_open', 'disable_comments_post_open', 20, 2);
add_filter('pings_open', 'disable_comments_post_open', 20, 2);

// 4. Xóa widget Bình luận gần đây trên dashboard
function remove_comments_dashboard_widget()
{
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}
add_action('admin_init', 'remove_comments_dashboard_widget');

// 5. Chặn truy cập trang bình luận qua URL trực tiếp
function disable_comments_redirect()
{
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'disable_comments_redirect');

// 6. Xóa tất cả comment hiện có (tuỳ chọn, nếu muốn)
function delete_all_comments()
{
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->comments");
}
// Ẩn thanh admin bar trên frontend cho tất cả người dùng
add_filter('show_admin_bar', '__return_false');

// Nếu muốn ẩn chỉ cho user không phải admin
add_filter('show_admin_bar', function ($show) {
    if (!current_user_can('administrator')) {
        return false; // ẩn thanh bar
    }
    return $show; // admin vẫn thấy
});
