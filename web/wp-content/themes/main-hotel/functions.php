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


///cấu hình site con
// ======== Thêm menu chính & submenu ========
add_action('admin_menu', function () {
    add_menu_page(
        'Cấu hình API',
        'Cấu hình API',
        'manage_options',
        'hotel-sync-config',
        'hotel_sync_api_config_render',
        'dashicons-admin-generic',
        3
    );
});

// ======== Hàm render trang cấu hình ========
function hotel_sync_api_config_render()
{
    if (!current_user_can('manage_options')) return;

    if (!is_multisite()) {
        echo '<div class="notice notice-error"><p>Trang này chỉ hoạt động với WordPress Multisite.</p></div>';
        return;
    }

    $sites = get_sites();
    $option_key = "hotel_sync_api_config";
    $config = get_option($option_key, ['subsites' => []]);

    // Xử lý lưu
    if (isset($_POST['hotel_sync_config_nonce']) && wp_verify_nonce($_POST['hotel_sync_config_nonce'], 'save_hotel_sync_api_config')) {
        $new_config = ['subsites' => []];
        foreach ($sites as $site) {
            if ($site->blog_id == 1) continue; // Bỏ site chính

            $blog_id = $site->blog_id;
            $new_config['subsites'][$blog_id] = [
                'hotel_name' => sanitize_text_field($_POST["hotel_name_$blog_id"] ?? ''),
                'token'      => sanitize_text_field($_POST["token_$blog_id"] ?? ''),
                'enabled'    => !empty($_POST["enabled_$blog_id"]),
            ];
        }
        update_option($option_key, $new_config);
        $config = $new_config;
        echo '<div class="updated"><p>Đã lưu cấu hình API.</p></div>';
    }

    echo '<div class="wrap">';
    echo '<h1>Cấu hình API Đồng bộ</h1>';
?>
    <form method="post">
        <?php wp_nonce_field('save_hotel_sync_api_config', 'hotel_sync_config_nonce'); ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Site</th>
                    <th>Tên khách sạn</th>
                    <th>Token</th>
                    <th>Cho phép đồng bộ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site):
                    if ($site->blog_id == 1) continue; // Bỏ site chính

                    $blog_id = $site->blog_id;
                    $site_conf = $config['subsites'][$blog_id] ?? [];
                    $hotel_name = esc_attr($site_conf['hotel_name'] ?? $site->blogname);
                    $token = esc_attr($site_conf['token'] ?? '');
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($site->blogname); ?></strong></td>
                        <td>
                            <input type="text" name="hotel_name_<?php echo $blog_id; ?>" value="<?php echo $hotel_name; ?>" class="regular-text" placeholder="Nhập tên khách sạn">
                        </td>
                        <td>
                            <input type="text" name="token_<?php echo $blog_id; ?>" id="token_<?php echo $blog_id; ?>" value="<?php echo $token; ?>" class="regular-text" readonly>
                            <button type="button" class="button generate-token" data-target="token_<?php echo $blog_id; ?>">Generate</button>
                        </td>
                        <td><input type="checkbox" name="enabled_<?php echo $blog_id; ?>" <?php checked(!empty($site_conf['enabled'])); ?>></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php submit_button('Lưu cấu hình'); ?>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.generate-token').forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = document.getElementById(this.dataset.target);
                    if (target.value) {
                        if (!confirm("Token đã tồn tại. Bạn có chắc muốn tạo lại không?")) return;
                    }
                    target.value = generateRandomToken();
                });
            });
        });

        function generateRandomToken() {
            return Array.from(window.crypto.getRandomValues(new Uint8Array(24)))
                .map(b => b.toString(16).padStart(2, "0"))
                .join('');
        }
    </script>
<?php
    echo '</div>';
}
