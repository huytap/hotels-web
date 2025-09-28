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
 * Rooms Grid Shortcode
 */
function hms_rooms_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6,
        'columns' => 3,
    ), $atts);

    // Mock data for demonstration - in real implementation, this would fetch from API
    $rooms = [
        [
            'id' => 1,
            'title' => ['vi' => 'Phòng Deluxe', 'en' => 'Deluxe Room'],
            'description' => ['vi' => 'Phòng rộng rãi với view đẹp', 'en' => 'Spacious room with beautiful view'],
            'featured_image' => 'https://via.placeholder.com/400x300',
            'gallery_images' => [
                'https://via.placeholder.com/800x600/1',
                'https://via.placeholder.com/800x600/2',
                'https://via.placeholder.com/800x600/3'
            ],
            'area' => ['vi' => '30m²', 'en' => '30m²'],
            'adult_capacity' => 2,
            'child_capacity' => 1,
            'bed_type' => ['vi' => 'Giường đôi king size', 'en' => 'King size bed'],
            'amenities' => ['vi' => 'WiFi miễn phí, TV LCD, Máy lạnh', 'en' => 'Free WiFi, LCD TV, Air conditioning'],
            'room_amenities' => ['vi' => 'Minibar, Két sắt, Bàn làm việc', 'en' => 'Minibar, Safe box, Work desk'],
            'bathroom_amenities' => ['vi' => 'Bồn tắm, Vòi sen, Máy sấy tóc', 'en' => 'Bathtub, Shower, Hair dryer'],
            'view' => ['vi' => 'View thành phố', 'en' => 'City view'],
            'base_price' => 1200000
        ],
        [
            'id' => 2,
            'title' => ['vi' => 'Phòng Superior', 'en' => 'Superior Room'],
            'description' => ['vi' => 'Phòng thoải mái với đầy đủ tiện nghi', 'en' => 'Comfortable room with full amenities'],
            'featured_image' => 'https://via.placeholder.com/400x300',
            'gallery_images' => [
                'https://via.placeholder.com/800x600/4',
                'https://via.placeholder.com/800x600/5',
                'https://via.placeholder.com/800x600/6'
            ],
            'area' => ['vi' => '25m²', 'en' => '25m²'],
            'adult_capacity' => 2,
            'child_capacity' => 0,
            'bed_type' => ['vi' => 'Giường đôi', 'en' => 'Double bed'],
            'amenities' => ['vi' => 'WiFi miễn phí, TV LCD, Máy lạnh', 'en' => 'Free WiFi, LCD TV, Air conditioning'],
            'room_amenities' => ['vi' => 'Minibar, Bàn làm việc', 'en' => 'Minibar, Work desk'],
            'bathroom_amenities' => ['vi' => 'Vòi sen, Máy sấy tóc', 'en' => 'Shower, Hair dryer'],
            'view' => ['vi' => 'View sân vườn', 'en' => 'Garden view'],
            'base_price' => 900000
        ]
    ];

    $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';

    ob_start();
    ?>
    <div class="rooms-grid-container">
        <div class="row">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-<?php echo 12 / intval($atts['columns']); ?> mb-4">
                    <div class="room-card" data-room-id="<?php echo $room['id']; ?>">
                        <div class="room-image" onclick="openRoomPopup(<?php echo $room['id']; ?>)">
                            <img src="<?php echo esc_url($room['featured_image']); ?>"
                                 alt="<?php echo esc_attr($room['title'][$current_lang]); ?>"
                                 class="img-fluid">
                            <div class="room-overlay">
                                <i class="fas fa-search-plus"></i>
                                <span>Xem chi tiết</span>
                            </div>
                        </div>
                        <div class="room-info">
                            <h3 class="room-title" onclick="openRoomPopup(<?php echo $room['id']; ?>)">
                                <?php echo esc_html($room['title'][$current_lang]); ?>
                            </h3>
                            <p class="room-description">
                                <?php echo esc_html($room['description'][$current_lang]); ?>
                            </p>
                            <div class="room-details">
                                <span class="room-capacity">
                                    <i class="fas fa-user"></i> <?php echo $room['adult_capacity']; ?> người lớn
                                    <?php if ($room['child_capacity'] > 0): ?>
                                        + <?php echo $room['child_capacity']; ?> trẻ em
                                    <?php endif; ?>
                                </span>
                                <span class="room-area">
                                    <i class="fas fa-expand-arrows-alt"></i> <?php echo $room['area'][$current_lang]; ?>
                                </span>
                            </div>
                            <div class="room-price">
                                <span class="price"><?php echo number_format($room['base_price']); ?> VNĐ</span>
                                <span class="price-unit">/đêm</span>
                            </div>
                        </div>

                        <!-- Hidden data for popup -->
                        <script type="application/json" class="room-data-<?php echo $room['id']; ?>">
                            <?php echo json_encode($room); ?>
                        </script>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Room Detail Popup Modal -->
    <div id="roomPopup" class="room-popup-modal">
        <div class="room-popup-content">
            <span class="room-popup-close">&times;</span>
            <div class="room-popup-body">
                <div class="room-gallery-section">
                    <div class="room-main-gallery">
                        <div class="main-image-container">
                            <img id="roomMainImage" src="" alt="" class="room-main-image">
                            <div class="gallery-nav">
                                <button class="gallery-prev" onclick="changeGalleryImage(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="gallery-next" onclick="changeGalleryImage(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="gallery-thumbnails" id="roomGalleryThumbs">
                            <!-- Thumbnails will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="room-info-section">
                    <div class="room-header">
                        <h2 id="roomPopupTitle"></h2>
                        <div class="room-price-popup">
                            <span id="roomPopupPrice"></span>
                            <span class="price-unit">/đêm</span>
                        </div>
                    </div>

                    <div class="room-description-popup">
                        <p id="roomPopupDescription"></p>
                    </div>

                    <div class="room-basic-info">
                        <div class="info-item">
                            <i class="fas fa-bed"></i>
                            <div>
                                <strong>Loại giường:</strong>
                                <span id="roomBedType"></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <div>
                                <strong>Diện tích:</strong>
                                <span id="roomArea"></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Sức chứa:</strong>
                                <span id="roomCapacity"></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-mountain"></i>
                            <div>
                                <strong>View:</strong>
                                <span id="roomView"></span>
                            </div>
                        </div>
                    </div>

                    <div class="room-amenities-popup">
                        <h4><i class="fas fa-star"></i> Tiện ích chính</h4>
                        <p id="roomAmenities"></p>

                        <h4><i class="fas fa-couch"></i> Tiện ích phòng</h4>
                        <p id="roomRoomAmenities"></p>

                        <h4><i class="fas fa-bath"></i> Tiện ích phòng tắm</h4>
                        <p id="roomBathroomAmenities"></p>
                    </div>

                    <div class="room-action-buttons">
                        <button class="btn btn-primary btn-book-now">
                            <i class="fas fa-calendar-check"></i> Đặt phòng ngay
                        </button>
                        <button class="btn btn-outline-secondary btn-contact">
                            <i class="fas fa-phone"></i> Liên hệ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Room Card Styles */
        .room-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .room-image {
            position: relative;
            overflow: hidden;
            height: 250px;
            cursor: pointer;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-image:hover img {
            transform: scale(1.1);
        }

        .room-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
        }

        .room-image:hover .room-overlay {
            opacity: 1;
        }

        .room-overlay i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .room-info {
            padding: 1.5rem;
        }

        .room-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            cursor: pointer;
            color: #333;
            transition: color 0.3s ease;
        }

        .room-title:hover {
            color: #007bff;
        }

        .room-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .room-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #777;
        }

        .room-details i {
            margin-right: 0.25rem;
            color: #007bff;
        }

        .room-price {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }

        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e74c3c;
        }

        .price-unit {
            color: #666;
            font-size: 0.9rem;
        }

        /* Popup Modal Styles */
        .room-popup-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }

        .room-popup-content {
            position: relative;
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 95%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .room-popup-close {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            z-index: 10;
            background: rgba(0,0,0,0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .room-popup-close:hover {
            background: rgba(0,0,0,0.8);
        }

        .room-popup-body {
            display: flex;
            height: 80vh;
        }

        .room-gallery-section {
            width: 60%;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
        }

        .room-main-gallery {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .main-image-container {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .room-main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-nav {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .gallery-prev, .gallery-next {
            background: rgba(0,0,0,0.6);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background 0.3s ease;
        }

        .gallery-prev:hover, .gallery-next:hover {
            background: rgba(0,0,0,0.8);
        }

        .gallery-thumbnails {
            height: 120px;
            padding: 15px;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            background: white;
            border-top: 1px solid #eee;
        }

        .gallery-thumb {
            width: 100px;
            height: 90px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: border-color 0.3s ease;
            flex-shrink: 0;
        }

        .gallery-thumb.active {
            border-color: #007bff;
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-info-section {
            width: 40%;
            padding: 2rem;
            overflow-y: auto;
            background: white;
        }

        .room-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .room-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .room-price-popup {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e74c3c;
        }

        .room-description-popup {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
        }

        .room-basic-info {
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item i {
            color: #007bff;
            font-size: 1.2rem;
            margin-right: 1rem;
            width: 20px;
        }

        .info-item strong {
            display: block;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .room-amenities-popup h4 {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
        }

        .room-amenities-popup h4 i {
            margin-right: 0.5rem;
            color: #007bff;
        }

        .room-amenities-popup p {
            color: #666;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .room-action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .btn-book-now, .btn-contact {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-book-now {
            background: #007bff;
            color: white;
            border: 2px solid #007bff;
        }

        .btn-book-now:hover {
            background: #0056b3;
            border-color: #0056b3;
        }

        .btn-contact {
            background: transparent;
            color: #007bff;
            border: 2px solid #007bff;
        }

        .btn-contact:hover {
            background: #007bff;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .room-popup-content {
                width: 98%;
                margin: 1% auto;
                max-height: 95vh;
            }

            .room-popup-body {
                flex-direction: column;
                height: auto;
                max-height: 90vh;
            }

            .room-gallery-section {
                width: 100%;
                height: 50vh;
            }

            .room-info-section {
                width: 100%;
                height: 40vh;
                overflow-y: auto;
            }

            .room-action-buttons {
                flex-direction: column;
            }
        }
    </style>

    <script>
        let currentRoomData = null;
        let currentImageIndex = 0;
        let galleryImages = [];

        function openRoomPopup(roomId) {
            // Get room data from hidden script tag
            const roomDataElement = document.querySelector('.room-data-' + roomId);
            if (!roomDataElement) return;

            currentRoomData = JSON.parse(roomDataElement.textContent);
            const currentLang = '<?php echo $current_lang; ?>';

            // Populate popup with room data
            document.getElementById('roomPopupTitle').textContent = currentRoomData.title[currentLang];
            document.getElementById('roomPopupDescription').textContent = currentRoomData.description[currentLang];
            document.getElementById('roomPopupPrice').textContent = new Intl.NumberFormat('vi-VN').format(currentRoomData.base_price) + ' VNĐ';
            document.getElementById('roomBedType').textContent = currentRoomData.bed_type[currentLang];
            document.getElementById('roomArea').textContent = currentRoomData.area[currentLang];
            document.getElementById('roomCapacity').textContent = currentRoomData.adult_capacity + ' người lớn' + (currentRoomData.child_capacity > 0 ? ' + ' + currentRoomData.child_capacity + ' trẻ em' : '');
            document.getElementById('roomView').textContent = currentRoomData.view[currentLang];
            document.getElementById('roomAmenities').textContent = currentRoomData.amenities[currentLang];
            document.getElementById('roomRoomAmenities').textContent = currentRoomData.room_amenities[currentLang];
            document.getElementById('roomBathroomAmenities').textContent = currentRoomData.bathroom_amenities[currentLang];

            // Setup gallery
            galleryImages = currentRoomData.gallery_images || [currentRoomData.featured_image];
            currentImageIndex = 0;
            loadGallery();

            // Show popup
            document.getElementById('roomPopup').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function loadGallery() {
            if (galleryImages.length === 0) return;

            // Set main image
            document.getElementById('roomMainImage').src = galleryImages[currentImageIndex];

            // Create thumbnails
            const thumbsContainer = document.getElementById('roomGalleryThumbs');
            thumbsContainer.innerHTML = '';

            galleryImages.forEach((image, index) => {
                const thumb = document.createElement('div');
                thumb.className = 'gallery-thumb' + (index === currentImageIndex ? ' active' : '');
                thumb.innerHTML = '<img src="' + image + '" alt="Room image ' + (index + 1) + '">';
                thumb.onclick = () => changeGalleryImage(index - currentImageIndex);
                thumbsContainer.appendChild(thumb);
            });
        }

        function changeGalleryImage(direction) {
            if (typeof direction === 'number') {
                if (Math.abs(direction) > 1) {
                    // Direct index change
                    currentImageIndex = direction;
                } else {
                    // Relative change
                    currentImageIndex += direction;
                }
            }

            if (currentImageIndex < 0) currentImageIndex = galleryImages.length - 1;
            if (currentImageIndex >= galleryImages.length) currentImageIndex = 0;

            document.getElementById('roomMainImage').src = galleryImages[currentImageIndex];

            // Update thumbnail active state
            document.querySelectorAll('.gallery-thumb').forEach((thumb, index) => {
                thumb.classList.toggle('active', index === currentImageIndex);
            });
        }

        // Close popup
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('roomPopup');
            const closeBtn = document.querySelector('.room-popup-close');

            closeBtn.onclick = function() {
                popup.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            window.onclick = function(event) {
                if (event.target === popup) {
                    popup.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (popup.style.display === 'block') {
                    if (e.key === 'Escape') {
                        popup.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    } else if (e.key === 'ArrowLeft') {
                        changeGalleryImage(-1);
                    } else if (e.key === 'ArrowRight') {
                        changeGalleryImage(1);
                    }
                }
            });
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('hms_rooms_grid', 'hms_rooms_grid_shortcode');

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
