<?php
/*
Plugin Name: Social Network Manager
Description: Plugin quản lý mạng xã hội (thêm, sửa, xóa) + hiển thị icon ngoài frontend.
Version: 1.1
Author: Tap Nguyen
*/

if (!defined('ABSPATH')) exit;

// ====== TẠO MENU TRONG ADMIN ======
add_action('admin_menu', function () {
    add_menu_page(
        'Mạng xã hội',
        'Mạng xã hội',
        'manage_options',
        'social-manager',
        'snm_render_admin_page',
        'dashicons-share'
    );
});

// ====== HIỂN THỊ FORM TRONG ADMIN ======
function snm_render_admin_page()
{
    if (!current_user_can('manage_options')) return;

    // Lấy dữ liệu mạng xã hội từ options
    $social_networks = get_option('snm_social_networks', []);

    // Xử lý form submit
    if (isset($_POST['snm_submit'])) {
        check_admin_referer('snm_save_networks');

        $new_networks = [];
        if (!empty($_POST['snm_name'])) {
            foreach ($_POST['snm_name'] as $i => $name) {
                if (empty($name)) continue;
                $new_networks[] = [
                    'name' => sanitize_text_field($name),
                    'url'  => esc_url_raw($_POST['snm_url'][$i]),
                    'icon' => sanitize_text_field($_POST['snm_icon'][$i]),
                ];
            }
        }
        update_option('snm_social_networks', $new_networks);
        $social_networks = $new_networks;

        echo '<div class="updated"><p>Lưu thành công!</p></div>';
    }

?>
    <div class="wrap">
        <h1>Quản lý Mạng Xã Hội</h1>
        <form method="post">
            <?php wp_nonce_field('snm_save_networks'); ?>

            <table class="form-table" id="snm_table">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>URL</th>
                        <th>Icon (Font Awesome class)</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($social_networks as $i => $network): ?>
                        <tr>
                            <td><input type="text" name="snm_name[]" value="<?php echo esc_attr($network['name']); ?>"></td>
                            <td><input type="url" name="snm_url[]" value="<?php echo esc_attr($network['url']); ?>"></td>
                            <td><input type="text" name="snm_icon[]" value="<?php echo esc_attr($network['icon']); ?>"></td>
                            <td><button type="button" class="button snm-remove">X</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><button type="button" class="button" id="snm_add">+ Thêm mạng xã hội</button></p>
            <p><input type="submit" name="snm_submit" class="button-primary" value="Lưu"></p>
        </form>
    </div>

    <script>
        document.getElementById('snm_add').addEventListener('click', function() {
            const tbody = document.querySelector('#snm_table tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
            <td><input type="text" name="snm_name[]" placeholder="VD: Facebook"></td>
            <td><input type="url" name="snm_url[]" placeholder="https://facebook.com/..."></td>
            <td><input type="text" name="snm_icon[]" placeholder="fab fa-facebook"></td>
            <td><button type="button" class="button snm-remove">X</button></td>
        `;
            tbody.appendChild(row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('snm-remove')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
<?php
}

// ====== SHORTCODE HIỂN THỊ NGOÀI FRONTEND ======
add_shortcode('social_networks', function () {
    $social_networks = get_option('snm_social_networks', []);
    if (empty($social_networks)) return '';

    ob_start();
    echo '<div class="snm-social-links">';
    foreach ($social_networks as $network) {
        echo '<a href="' . esc_url($network['url']) . '" target="_blank" rel="noopener">
                <i class="' . esc_attr($network['icon']) . '"></i>
                ' . esc_html($network['name']) . '
              </a> ';
    }
    echo '</div>';
    return ob_get_clean();
});

// ====== LOAD FONT AWESOME ======
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css');
});
// ====== HIỂN THỊ TRỰC TIẾP TRONG CODE THEME ======
function snm_display_social_networks()
{
    $social_networks = get_option('snm_social_networks', []);
    if (empty($social_networks)) return;

    echo '<ul class="social-links">';
    foreach ($social_networks as $network) {
        echo '<li><a href="' . esc_url($network['url']) . '" target="_blank" rel="noopener" class="' . esc_attr($network['icon']) . '">
              </a></li>';
    }
    echo '</ul>';
}
