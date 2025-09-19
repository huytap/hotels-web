<?php
function myhotel_enqueue_scripts()
{
    wp_enqueue_style('tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
    wp_enqueue_style('hotel-style', get_stylesheet_uri());
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'hotel-dropdown',
        get_template_directory_uri() . '/assets/js/script.js',
        [],
        filemtime(get_template_directory() . '/assets/js/script.js'),
        true // true = load trước </body>
    );
}
add_action('wp_enqueue_scripts', 'myhotel_enqueue_scripts');
function hotels_register_footer_widgets()
{
    register_sidebar(array(
        'name'          => __('Footer Column 1', 'hotel'),
        'id'            => 'footer-1',
        'description'   => __('Widget cột 1 của footer.', 'hotel'),
        'before_widget' => '<div class="space-y-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Column 2', 'hotel'),
        'id'            => 'footer-2',
        'description'   => __('Widget cột 2 của footer.', 'hotel'),
        'before_widget' => '<div class="space-y-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="font-semibold text-lg">',
        'after_title'   => '</div>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Column 3', 'hotel'),
        'id'            => 'footer-3',
        'description'   => __('Widget cột 3 của footer.', 'hotel'),
        'before_widget' => '<div class="space-y-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Column 4', 'hotel'),
        'id'            => 'footer-4',
        'description'   => __('Widget cột 4 của footer.', 'hotel'),
        'before_widget' => '<div class="space-y-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'hotels_register_footer_widgets');
// Ẩn thanh admin bar trên frontend cho tất cả người dùng
add_filter('show_admin_bar', '__return_false');

// Nếu muốn ẩn chỉ cho user không phải admin
add_filter('show_admin_bar', function ($show) {
    if (!current_user_can('administrator')) {
        return false; // ẩn thanh bar
    }
    return $show; // admin vẫn thấy
});
function hotel_theme_setup()
{
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'main-hotel'),
        'footer' => __('Footer Menu', 'main-hotel'),
    ));
}
add_action('after_setup_theme', 'hotel_theme_setup');
add_theme_support('post-thumbnails');
add_theme_support('page-thumbnails');

add_action('after_setup_theme', 'hotel_theme_setup');
// Thêm class Tailwind và data-testid
function custom_menu_link_attributes($atts, $item, $args, $depth)
{
    $atts['class'] = 'text-foreground hover:text-primary transition-colors scroll-link';
    if ($args->theme_location === 'footer') {
        $atts['class'] = 'text-primary-foreground/60 hover:text-primary-foreground transition-colors';
    }
    // Tạo data-testid từ slug của menu item
    $atts['data-testid'] = 'nav-' . sanitize_title($item->title);

    return $atts;
}
add_filter('nav_menu_link_attributes', 'custom_menu_link_attributes', 10, 4);
