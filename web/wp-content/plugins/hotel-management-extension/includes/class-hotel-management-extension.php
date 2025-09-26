<?php

/**
 * Class HME_Promotion_Manager
 * Quản lý các chức năng liên quan đến Promotions và Discount Codes
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Class chính quản lý Hotel Management Extension
 */
class Hotel_Management_Extension
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function init()
    {
        // Chỉ load trong admin area
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menus'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

            // Khởi tạo các manager classes
            new HME_Booking_Manager();
            new HME_Room_Rate_Manager();
            new HME_Promotion_Manager();
        }

        // Load AJAX handlers
        $this->register_ajax_handlers();
    }

    public function activate()
    {
        // Kiểm tra dependencies
        if (!function_exists('callApi')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Hotel Management Extension requires Hotel Sync API plugin to be activated first.');
        }

        // Tạo database tables nếu cần (cho cache/logs)
        $this->create_tables();
    }

    public function deactivate()
    {
        // Cleanup nếu cần
    }

    private function create_tables()
    {
        global $wpdb;

        // Tạo bảng để cache API responses (tùy chọn)
        $table_name = $wpdb->prefix . 'hme_api_cache';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_value longtext NOT NULL,
            blog_id mediumint(9) NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key_blog (cache_key, blog_id),
            INDEX blog_expires (blog_id, expires_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_admin_menus()
    {
        $current_blog_id = get_current_blog_id();

        // Chỉ hiển thị menu trên subsites (hotels)
        if ($current_blog_id <= 1) {
            return; // Main site - không hiển thị menu
        }

        // Kiểm tra xem có cấu hình API không
        if (!$this->is_api_configured()) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning"><p><strong>Hotel Management:</strong> API not configured for this site. Please contact administrator.</p></div>';
            });
        }

        // Menu chính
        add_menu_page(
            __('Quản Lý Khách Sạn', 'hotel'),
            __('Quản Lý Khách Sạn', 'hotel'),
            'manage_options',
            'hotel-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-building',
            30
        );

        // Submenu
        add_submenu_page(
            'hotel-dashboard',
            __('Bảng Điều Khiển', 'hotel'),
            __('Bảng Điều Khiển', 'hotel'),
            'manage_options',
            'hotel-dashboard',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'hotel-dashboard',
            __('Đặt Phòng', 'hotel'),
            __('Đặt Phòng', 'hotel'),
            'manage_options',
            'hotel-bookings',
            array($this, 'bookings_page')
        );

        add_submenu_page(
            'hotel-dashboard',
            __('Giá Phòng', 'hotel'),
            __('Giá Phòng', 'hotel'),
            'manage_options',
            'hotel-room-rates',
            array($this, 'room_rates_page')
        );

        add_submenu_page(
            'hotel-dashboard',
            __('Khuyến Mãi', 'hotel'),
            __('Khuyến Mãi', 'hotel'),
            'manage_options',
            'hotel-promotions',
            array($this, 'promotions_page')
        );
    }

    private function is_api_configured()
    {
        $current_blog_id = get_current_blog_id();

        // Lấy config từ main site
        switch_to_blog(get_main_site_id());
        $config = get_option("hotel_sync_api_config", ['subsites' => []]);
        $site_conf = $config['subsites'][$current_blog_id] ?? null;
        restore_current_blog();

        return !empty($site_conf) && !empty($site_conf['enabled']) && !empty($site_conf['token']);
    }

    public function admin_scripts($hook)
    {
        if (strpos($hook, 'hotel-') !== false) {
            // Enqueue jQuery và các thư viện cần thiết
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css');

            // Plugin scripts
            // Utility functions first (as dependency for other scripts)
            wp_enqueue_script(
                'hme-utils-js',
                HME_PLUGIN_URL . 'assets/js/hme-utils.js',
                array('jquery'),
                '2.0.0',
                true
            );
            wp_enqueue_script(
                'hme-room-management-js',
                HME_PLUGIN_URL . 'assets/js/room-management.js',
                array('jquery', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'hme-utils-js'),
                '2.0.0',
                true
            );
            wp_enqueue_script(
                'hme-admin-js',
                HME_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'hme-utils-js'),
                '2.0.0',
                true
            );

            wp_enqueue_style(
                'hme-admin-css',
                HME_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                '2.0.0'
            );

            wp_enqueue_style(
                'hme-admin-style',
                HME_PLUGIN_URL . 'assets/css/admin-style.css',
                array(),
                '2.0.0'
            );

            wp_enqueue_style(
                'hme-room-management-css',
                HME_PLUGIN_URL . 'assets/css/room-management.css',
                array(),
                '2.0.0'
            );

            // Localize script với config
            wp_localize_script('hme-room-management-js', 'hme_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hme_admin_nonce'),
                'blog_id' => get_current_blog_id(),
                'api_configured' => $this->is_api_configured(),
                'strings' => array(
                    'confirm_delete' => __('Bạn có chắc chắn muốn xóa mục này không?', 'hotel'),
                    'loading' => __('Đang tải...', 'hotel'),
                    'error' => __('Đã xảy ra lỗi. Vui lòng thử lại.', 'hotel'),
                    'success' => __('Thao tác hoàn thành thành công.', 'hotel'),
                    'please_select_room_type' => __('Vui lòng chọn loại phòng', 'hotel'),
                    'failed_to_load' => __('Không thể tải dữ liệu', 'hotel'),
                    'updated_successfully' => __('Cập nhật thành công', 'hotel'),
                    'failed_to_update' => __('Cập nhật thất bại', 'hotel'),
                    'bulk_update_failed' => __('Cập nhật hàng loạt thất bại', 'hotel'),
                    'copy_rates_coming_soon' => __('Chức năng sao chép giá sẽ có sớm...', 'hotel'),
                    'rate_updated_successfully' => __('Giá phòng đã được cập nhật thành công', 'hotel'),
                    'inventory_updated_successfully' => __('Tồn kho đã được cập nhật thành công', 'hotel'),
                    'failed_to_toggle_availability' => __('Không thể thay đổi trạng thái phòng', 'hotel'),
                    'room_opened_successfully' => __('Phòng đã được mở thành công', 'hotel'),
                    'room_closed_successfully' => __('Phòng đã được đóng thành công', 'hotel')
                )
            ));
        }
    }

    private function register_ajax_handlers()
    {
        // Booking AJAX handlers
        add_action('wp_ajax_hme_get_bookings', array('HME_Booking_Manager', 'ajax_get_bookings'));
        add_action('wp_ajax_hme_create_booking', array('HME_Booking_Manager', 'ajax_create_booking'));
        add_action('wp_ajax_hme_update_booking', array('HME_Booking_Manager', 'ajax_update_booking'));
        add_action('wp_ajax_hme_delete_booking', array('HME_Booking_Manager', 'ajax_delete_booking'));
        add_action('wp_ajax_hme_get_booking_details', array('HME_Booking_Manager', 'ajax_get_booking_details'));

        // Room Rate AJAX handlers - removed old handlers as they're not used anymore
        // All room rate functionality now goes through hme_api_call

        // Promotion AJAX handlers
        add_action('wp_ajax_hme_get_promotions', array('HME_Promotion_Manager', 'ajax_get_promotions'));
        add_action('wp_ajax_hme_create_promotion', array('HME_Promotion_Manager', 'ajax_create_promotion'));
        add_action('wp_ajax_hme_update_promotion', array('HME_Promotion_Manager', 'ajax_update_promotion'));
        add_action('wp_ajax_hme_update_status_promotion', array('HME_Promotion_Manager', 'ajax_update_status_promotion'));
        add_action('wp_ajax_hme_delete_promotion', array('HME_Promotion_Manager', 'ajax_delete_promotion'));
        add_action('wp_ajax_hme_get_promotion', array('HME_Promotion_Manager', 'ajax_get_promotion'));
        add_action('wp_ajax_hme_validate_promotion', array('HME_Promotion_Manager', 'ajax_validate_promotion'));
        add_action('wp_ajax_hme_generate_promotion_code', array('HME_Promotion_Manager', 'ajax_generate_promotion_code'));
        add_action('wp_ajax_hme_bulk_promotion_actions', array('HME_Promotion_Manager', 'ajax_bulk_promotion_actions'));

        // Dashboard AJAX handlers
        add_action('wp_ajax_hme_get_dashboard_stats', array($this, 'ajax_get_dashboard_stats'));

        // Generic API call handler
        add_action('wp_ajax_hme_api_call', array($this, 'ajax_api_call'));

        // Export handlers
        add_action('wp_ajax_hme_export_rates', array('HME_Room_Rate_Manager', 'ajax_export_rates'));
    }

    // ============ PAGE HANDLERS ============

    public function dashboard_page()
    {
?>
        <div class="wrap">
            <h1>Hotel Management Dashboard</h1>
            <p><strong>Site:</strong> <?php echo get_bloginfo('name'); ?> | <strong>ID:</strong> <?php echo get_current_blog_id(); ?></p>

            <div class="hme-dashboard-loading">
                <p>Loading dashboard statistics...</p>
            </div>

            <div class="hme-dashboard-content" style="display: none;">
                <!-- Stats Cards -->
                <div class="hme-stats-grid">
                    <div class="hme-stat-card" id="total-bookings">
                        <h3>Total Bookings</h3>
                        <span class="hme-stat-number">0</span>
                    </div>
                    <div class="hme-stat-card" id="pending-bookings">
                        <h3>Pending Bookings</h3>
                        <span class="hme-stat-number">0</span>
                    </div>
                    <div class="hme-stat-card" id="available-rooms">
                        <h3>Available Rooms</h3>
                        <span class="hme-stat-number">0/0</span>
                    </div>
                    <div class="hme-stat-card" id="active-promotions">
                        <h3>Active Promotions</h3>
                        <span class="hme-stat-number">0</span>
                    </div>
                    <div class="hme-stat-card revenue" id="today-revenue">
                        <h3>Today Revenue</h3>
                        <span class="hme-stat-number">0 VNĐ</span>
                    </div>
                    <div class="hme-stat-card revenue" id="month-revenue">
                        <h3>This Month</h3>
                        <span class="hme-stat-number">0 VNĐ</span>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="hme-dashboard-section">
                    <h2>Quick Actions</h2>
                    <div class="hme-quick-actions">
                        <a href="<?php echo admin_url('admin.php?page=hotel-bookings&action=add'); ?>" class="button button-primary button-large">
                            <span class="dashicons dashicons-plus-alt"></span> New Booking
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=hotel-room-rates'); ?>" class="button button-large">
                            <span class="dashicons dashicons-admin-home"></span> Manage Rooms
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=hotel-promotions&action=add'); ?>" class="button button-large">
                            <span class="dashicons dashicons-tag"></span> New Promotion
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="hme-dashboard-grid">
                    <div class="hme-dashboard-section">
                        <h3>Recent Bookings</h3>
                        <div id="recent-bookings" class="hme-recent-list">
                            <p>Loading...</p>
                        </div>
                        <p class="hme-view-all">
                            <a href="<?php echo admin_url('admin.php?page=hotel-bookings'); ?>">View all bookings →</a>
                        </p>
                    </div>

                    <div class="hme-dashboard-section">
                        <h3>Room Availability Today</h3>
                        <div id="room-availability" class="hme-room-grid">
                            <p>Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                loadDashboardData();

                function loadDashboardData() {
                    $.ajax({
                        url: hme_admin.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'hme_get_dashboard_stats',
                            nonce: hme_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                updateDashboardStats(response.data);
                                $('.hme-dashboard-loading').hide();
                                $('.hme-dashboard-content').show();
                            } else {
                                showError('Failed to load dashboard data: ' + response.data);
                            }
                        },
                        error: function() {
                            showError('Error connecting to server');
                        }
                    });
                }

                function updateDashboardStats(data) {
                    $('#total-bookings .hme-stat-number').text(data.total_bookings || 0);
                    $('#pending-bookings .hme-stat-number').text(data.pending_bookings || 0);
                    $('#available-rooms .hme-stat-number').text((data.available_rooms || 0) + '/' + (data.total_rooms || 0));
                    $('#active-promotions .hme-stat-number').text(data.active_promotions || 0);
                    $('#today-revenue .hme-stat-number').text(formatCurrency(data.today_revenue || 0) + ' VNĐ');
                    $('#month-revenue .hme-stat-number').text(formatCurrency(data.month_revenue || 0) + ' VNĐ');

                    // Update recent bookings
                    if (data.recent_bookings && data.recent_bookings.length > 0) {
                        var html = '<ul>';
                        data.recent_bookings.forEach(function(booking) {
                            html += '<li><strong>' + booking.customer_name + '</strong> - ' +
                                booking.room_type + ' <span class="status-' + booking.status + '">' + booking.status + '</span></li>';
                        });
                        html += '</ul>';
                        $('#recent-bookings').html(html);
                    } else {
                        $('#recent-bookings').html('<p>No recent bookings</p>');
                    }

                    // Update room availability
                    if (data.room_availability && data.room_availability.length > 0) {
                        var html = '';
                        data.room_availability.forEach(function(room) {
                            html += '<div class="hme-room-item">';
                            html += '<strong>' + room.name + '</strong><br>';
                            html += '<span class="available">' + room.available + ' available</span> / ';
                            html += '<span class="total">' + room.total + ' total</span>';
                            html += '</div>';
                        });
                        $('#room-availability').html(html);
                    } else {
                        $('#room-availability').html('<p>No room data available</p>');
                    }
                }

                function formatCurrency(amount) {
                    return new Intl.NumberFormat('vi-VN').format(amount);
                }

                function showError(message) {
                    $('.hme-dashboard-loading').html('<div class="notice notice-error"><p>' + message + '</p></div>');
                }
            });
        </script>
<?php
    }

    public function bookings_page()
    {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        switch ($action) {
            case 'add':
                include HME_PLUGIN_PATH . 'views/booking-add.php';
                break;
            case 'edit':
                include HME_PLUGIN_PATH . 'views/booking-edit.php';
                break;
            default:
                include HME_PLUGIN_PATH . 'views/bookings-list.php';
        }
    }

    public function room_rates_page()
    {
        include HME_PLUGIN_PATH . 'views/room-rates.php';
    }

    public function promotions_page()
    {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        switch ($action) {
            case 'add':
                include HME_PLUGIN_PATH . 'views/promotions/promotion-add.php';
                break;
            case 'edit':
                include HME_PLUGIN_PATH . 'views/promotions/promotion-edit.php';
                break;
            default:
                include HME_PLUGIN_PATH . 'views/promotions/promotions-list.php';
        }
    }

    // ============ AJAX HANDLERS ============

    public function ajax_get_dashboard_stats()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Gọi API để lấy stats
        $response = callApi('dashboard/stats', 'GET');
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Generic API call handler
     */
    public function ajax_api_call()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Get parameters
        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : 'GET';
        $data = isset($_POST['data']) ? $_POST['data'] : array();

        if (empty($endpoint)) {
            wp_send_json_error('Endpoint is required');
            return;
        }

        // Sanitize method
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            wp_send_json_error('Invalid HTTP method');
            return;
        }

        // Call API
        $response = callApi($endpoint, $method, $data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}

/**
 * Helper function để cache API responses (optional)
 */
function hme_get_cached_api_response($cache_key, $ttl = 300)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hme_api_cache';
    $blog_id = get_current_blog_id();

    $cached = $wpdb->get_row($wpdb->prepare(
        "SELECT cache_value FROM $table_name WHERE cache_key = %s AND blog_id = %d AND expires_at > NOW()",
        $cache_key,
        $blog_id
    ));

    if ($cached) {
        return json_decode($cached->cache_value, true);
    }

    return false;
}

/**
 * Helper function để lưu cache API responses (optional)
 */
function hme_set_cached_api_response($cache_key, $data, $ttl = 300)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hme_api_cache';
    $blog_id = get_current_blog_id();
    $expires_at = date('Y-m-d H:i:s', time() + $ttl);

    $wpdb->replace(
        $table_name,
        array(
            'cache_key' => $cache_key,
            'cache_value' => json_encode($data),
            'blog_id' => $blog_id,
            'expires_at' => $expires_at
        ),
        array('%s', '%s', '%d', '%s')
    );
}

/**
 * Helper function format currency
 */
function hme_format_currency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

/**
 * Helper function format date
 */
function hme_format_date($date_string)
{
    return date('d/m/Y', strtotime($date_string));
}

/**
 * Helper function format datetime
 */
function hme_format_datetime($datetime_string)
{
    return date('d/m/Y H:i', strtotime($datetime_string));
}
