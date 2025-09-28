<?php
/*
Plugin Name: Hotel Info Manager
Description: Plugin quản lý thông tin khách sạn hoàn chỉnh với 15 trường đa ngôn ngữ và 14 trường chung. Hỗ trợ đồng bộ với Laravel API. Form một trang quản lý tất cả thông tin (hỗ trợ Polylang).
Version: 2.0
Author: Tap Nguyen
Text Domain: hotel
*/

// Migration function for existing data
function hotel_info_migrate_data() {
    $version = get_option('hotel_info_plugin_version', '1.0');

    if (version_compare($version, '1.4', '<')) {
        // Migrate phone, email, map from language-specific to common
        if (function_exists('pll_languages_list')) {
            $languages = pll_languages_list();
            $default_lang = pll_default_language();

            // For phone, email, map - use default language data as common data
            $phone = get_option("hotel_info_phone_{$default_lang}", '');
            $email = get_option("hotel_info_email_{$default_lang}", '');
            $map = get_option("hotel_info_map_{$default_lang}", '');

            if ($phone) update_option('hotel_info_phone', $phone);
            if ($email) update_option('hotel_info_email', $email);
            if ($map) update_option('hotel_info_map', $map);
        }

        update_option('hotel_info_plugin_version', '1.4');
    }
}

// Run migration on admin_init
add_action('admin_init', 'hotel_info_migrate_data');

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

    // Xử lý lưu dữ liệu
    if (isset($_POST['hotel_info_nonce']) && wp_verify_nonce($_POST['hotel_info_nonce'], 'save_hotel_info')) {
        // Lưu các trường có đa ngôn ngữ cho tất cả ngôn ngữ
        $multilingual_fields = [
            'name', 'address', 'policy', 'description', 'short_description',
            'amenities', 'facilities', 'services', 'nearby_attractions',
            'transportation', 'dining_options', 'room_features',
            'cancellation_policy', 'terms_conditions', 'special_notes'
        ];

        foreach ($languages as $lang) {
            foreach ($multilingual_fields as $field) {
                $post_key = "hotel_info_{$field}_{$lang}";
                if (isset($_POST[$post_key])) {
                    if (in_array($field, ['policy', 'description', 'amenities', 'facilities', 'services', 'nearby_attractions', 'transportation', 'dining_options', 'room_features', 'cancellation_policy', 'terms_conditions', 'special_notes'])) {
                        // Textarea fields
                        update_option("hotel_info_{$field}_{$lang}", sanitize_textarea_field($_POST[$post_key]));
                    } else {
                        // Text fields
                        update_option("hotel_info_{$field}_{$lang}", sanitize_text_field($_POST[$post_key]));
                    }
                }
            }
        }

        // Các trường không theo ngôn ngữ (lưu chung)
        $common_fields = [
            'phone' => 'sanitize_text_field',
            'email' => 'sanitize_email',
            'fax' => 'sanitize_text_field',
            'website' => 'esc_url_raw',
            'tax_code' => 'sanitize_text_field',
            'business_license' => 'sanitize_text_field',
            'star_rating' => 'sanitize_text_field',
            'established_year' => 'sanitize_text_field',
            'total_rooms' => 'sanitize_text_field',
            'check_in_time' => 'sanitize_text_field',
            'check_out_time' => 'sanitize_text_field',
            'currency' => 'sanitize_text_field',
            'timezone' => 'sanitize_text_field',
        ];

        foreach ($common_fields as $field => $sanitize_func) {
            if (isset($_POST["hotel_info_{$field}"])) {
                update_option("hotel_info_{$field}", $sanitize_func($_POST["hotel_info_{$field}"]));
            }
        }

        // Special handling for map field (allow iframe)
        if (isset($_POST['hotel_info_map'])) {
            update_option("hotel_info_map", wp_kses($_POST['hotel_info_map'], array(
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
        }

        // Gọi hàm đồng bộ dữ liệu sau khi lưu thành công
        if (function_exists('sync_hotel_data_to_laravel')) {
            sync_hotel_data_to_laravel();
        }
        echo '<div class="updated"><p>✅ Đã lưu thông tin cho tất cả ngôn ngữ thành công!</p></div>';
    }

    // Lấy dữ liệu cho tất cả ngôn ngữ
    $data = [];
    foreach ($languages as $lang) {
        $data[$lang] = [
            'name' => get_option("hotel_info_name_{$lang}", ''),
            'address' => get_option("hotel_info_address_{$lang}", ''),
            'policy' => get_option("hotel_info_policy_{$lang}", ''),
            'description' => get_option("hotel_info_description_{$lang}", ''),
            'short_description' => get_option("hotel_info_short_description_{$lang}", ''),
            'amenities' => get_option("hotel_info_amenities_{$lang}", ''),
            'facilities' => get_option("hotel_info_facilities_{$lang}", ''),
            'services' => get_option("hotel_info_services_{$lang}", ''),
            'nearby_attractions' => get_option("hotel_info_nearby_attractions_{$lang}", ''),
            'transportation' => get_option("hotel_info_transportation_{$lang}", ''),
            'dining_options' => get_option("hotel_info_dining_options_{$lang}", ''),
            'room_features' => get_option("hotel_info_room_features_{$lang}", ''),
            'cancellation_policy' => get_option("hotel_info_cancellation_policy_{$lang}", ''),
            'terms_conditions' => get_option("hotel_info_terms_conditions_{$lang}", ''),
            'special_notes' => get_option("hotel_info_special_notes_{$lang}", ''),
        ];
    }

    // Các trường không theo ngôn ngữ (lấy chung)
    $common_data = [
        'phone' => get_option("hotel_info_phone", ''),
        'email' => get_option("hotel_info_email", ''),
        'map' => get_option("hotel_info_map", ''),
        'fax' => get_option("hotel_info_fax", ''),
        'website' => get_option("hotel_info_website", ''),
        'tax_code' => get_option("hotel_info_tax_code", ''),
        'business_license' => get_option("hotel_info_business_license", ''),
        'star_rating' => get_option("hotel_info_star_rating", ''),
        'established_year' => get_option("hotel_info_established_year", ''),
        'total_rooms' => get_option("hotel_info_total_rooms", ''),
        'check_in_time' => get_option("hotel_info_check_in_time", '14:00'),
        'check_out_time' => get_option("hotel_info_check_out_time", '12:00'),
        'currency' => get_option("hotel_info_currency", 'VND'),
        'timezone' => get_option("hotel_info_timezone", 'Asia/Ho_Chi_Minh'),
    ];

    echo '<div class="wrap">';
    echo '<h1>🏨 Thông tin khách sạn</h1>';

    echo '<form method="post">';
    wp_nonce_field('save_hotel_info', 'hotel_info_nonce');
?>
    <style>
        .hotel-info-admin {
            max-width: 900px;
        }

        /* Tab Navigation */
        .tab-nav {
            border-bottom: 1px solid #ccd0d4;
            margin-bottom: 0;
            background: #f6f7f7;
            padding: 0;
        }
        .tab-nav a {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            background: #f6f7f7;
            border: 1px solid #ccd0d4;
            border-bottom: none;
            margin-right: 2px;
            border-radius: 4px 4px 0 0;
            color: #50575e;
            font-weight: 500;
            transition: all 0.2s;
        }
        .tab-nav a.active {
            background: #fff;
            color: #2271b1;
            border-color: #2271b1;
            position: relative;
            z-index: 2;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .tab-nav a:hover {
            background: #e9ecef;
            color: #2271b1;
        }

        /* Tab Content */
        .tab-content {
            display: none;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-top: 1px solid #2271b1;
            padding: 25px;
            border-radius: 0 0 4px 4px;
            min-height: 400px;
        }
        .tab-content.active {
            display: block;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
            border: 1px solid #e1e1e1;
            padding: 20px;
            border-radius: 6px;
            background: #fafafa;
        }
        .form-group h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2271b1;
            font-size: 16px;
            border-bottom: 2px solid #2271b1;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #1d2327;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group input[type="time"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 600px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            padding: 8px 12px;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-group .description {
            color: #646970;
            font-size: 13px;
            font-style: italic;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f0f6fc;
            border-left: 3px solid #72aee6;
            border-radius: 0 4px 4px 0;
        }

        /* Two column layout */
        .form-row {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        .form-col {
            flex: 1;
            min-width: 280px;
        }
        .form-col-small {
            flex: 0 0 200px;
        }
    </style>

    <div class="hotel-info-admin">
        <!-- Tab Navigation -->
        <div class="tab-nav">
            <a href="#tab-vietnamese" class="tab-link active" onclick="switchTab(event, 'vietnamese')">🇻🇳 Tiếng Việt</a>
            <a href="#tab-english" class="tab-link" onclick="switchTab(event, 'english')">🇺🇸 English</a>
            <a href="#tab-common" class="tab-link" onclick="switchTab(event, 'common')">⚙️ Thông tin chung</a>
        </div>

        <!-- Vietnamese Tab -->
        <div id="tab-vietnamese" class="tab-content active">
            <div class="form-group">
                <h4>📝 Tên khách sạn</h4>
                <label for="hotel_info_name_vi">Tên khách sạn</label>
                <input type="text" name="hotel_info_name_vi" id="hotel_info_name_vi"
                       value="<?php echo esc_attr($data['vi']['name']); ?>"
                       placeholder="Tên khách sạn bằng tiếng Việt">
            </div>

            <div class="form-group">
                <h4>📍 Địa chỉ</h4>
                <label for="hotel_info_address_vi">Địa chỉ</label>
                <input type="text" name="hotel_info_address_vi" id="hotel_info_address_vi"
                       value="<?php echo esc_attr($data['vi']['address']); ?>"
                       placeholder="Địa chỉ bằng tiếng Việt">
            </div>

            <div class="form-group">
                <h4>🕐 Quy định Check-in / Check-out</h4>
                <label for="hotel_info_policy_vi">Quy định Check-in / Check-out</label>
                <textarea name="hotel_info_policy_vi" id="hotel_info_policy_vi" rows="3"
                          placeholder="Ví dụ: Check-in: 14:00 | Check-out: 12:00 | Trả phòng muộn có thể tính phí thêm"><?php echo esc_textarea($data['vi']['policy']); ?></textarea>
                <div class="description">Nhập thông tin về thời gian check-in, check-out và các quy định liên quan</div>
            </div>

            <div class="form-group">
                <h4>📝 Mô tả khách sạn</h4>
                <label for="hotel_info_description_vi">Mô tả chi tiết</label>
                <textarea name="hotel_info_description_vi" id="hotel_info_description_vi" rows="5"
                          placeholder="Mô tả chi tiết về khách sạn bằng tiếng Việt"><?php echo esc_textarea($data['vi']['description']); ?></textarea>
            </div>

            <div class="form-group">
                <h4>📄 Mô tả ngắn</h4>
                <label for="hotel_info_short_description_vi">Mô tả ngắn gọn</label>
                <input type="text" name="hotel_info_short_description_vi" id="hotel_info_short_description_vi"
                       value="<?php echo esc_attr($data['vi']['short_description']); ?>"
                       placeholder="Mô tả ngắn gọn về khách sạn">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🏨 Tiện nghi</h4>
                        <label for="hotel_info_amenities_vi">Tiện nghi khách sạn</label>
                        <textarea name="hotel_info_amenities_vi" id="hotel_info_amenities_vi" rows="4"
                                  placeholder="WiFi miễn phí, Hồ bơi, Gym, Spa..."><?php echo esc_textarea($data['vi']['amenities']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🏗️ Cơ sở vật chất</h4>
                        <label for="hotel_info_facilities_vi">Cơ sở vật chất</label>
                        <textarea name="hotel_info_facilities_vi" id="hotel_info_facilities_vi" rows="4"
                                  placeholder="Thang máy, Bãi đỗ xe, Phòng hội nghị..."><?php echo esc_textarea($data['vi']['facilities']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🛎️ Dịch vụ</h4>
                        <label for="hotel_info_services_vi">Dịch vụ khách sạn</label>
                        <textarea name="hotel_info_services_vi" id="hotel_info_services_vi" rows="4"
                                  placeholder="Room service, Dịch vụ giặt ủi, Đưa đón sân bay..."><?php echo esc_textarea($data['vi']['services']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🗺️ Điểm tham quan gần đó</h4>
                        <label for="hotel_info_nearby_attractions_vi">Điểm tham quan</label>
                        <textarea name="hotel_info_nearby_attractions_vi" id="hotel_info_nearby_attractions_vi" rows="4"
                                  placeholder="Bảo tàng, Nhà thờ Đức Bà, Chợ Bến Thành..."><?php echo esc_textarea($data['vi']['nearby_attractions']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🚗 Phương tiện di chuyển</h4>
                        <label for="hotel_info_transportation_vi">Thông tin di chuyển</label>
                        <textarea name="hotel_info_transportation_vi" id="hotel_info_transportation_vi" rows="3"
                                  placeholder="Cách sân bay 30km, Ga tàu 5km..."><?php echo esc_textarea($data['vi']['transportation']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🍽️ Lựa chọn ăn uống</h4>
                        <label for="hotel_info_dining_options_vi">Ăn uống</label>
                        <textarea name="hotel_info_dining_options_vi" id="hotel_info_dining_options_vi" rows="3"
                                  placeholder="Nhà hàng buffet, Quán bar, Café..."><?php echo esc_textarea($data['vi']['dining_options']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🛏️ Đặc điểm phòng</h4>
                        <label for="hotel_info_room_features_vi">Đặc điểm phòng</label>
                        <textarea name="hotel_info_room_features_vi" id="hotel_info_room_features_vi" rows="3"
                                  placeholder="Điều hòa, TV LED, Mini bar..."><?php echo esc_textarea($data['vi']['room_features']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>❌ Chính sách hủy phòng</h4>
                        <label for="hotel_info_cancellation_policy_vi">Chính sách hủy</label>
                        <textarea name="hotel_info_cancellation_policy_vi" id="hotel_info_cancellation_policy_vi" rows="3"
                                  placeholder="Hủy miễn phí trước 24h..."><?php echo esc_textarea($data['vi']['cancellation_policy']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <h4>📋 Điều khoản & Điều kiện</h4>
                <label for="hotel_info_terms_conditions_vi">Điều khoản và điều kiện</label>
                <textarea name="hotel_info_terms_conditions_vi" id="hotel_info_terms_conditions_vi" rows="4"
                          placeholder="Các điều khoản sử dụng dịch vụ..."><?php echo esc_textarea($data['vi']['terms_conditions']); ?></textarea>
            </div>

            <div class="form-group">
                <h4>📝 Ghi chú đặc biệt</h4>
                <label for="hotel_info_special_notes_vi">Ghi chú đặc biệt</label>
                <textarea name="hotel_info_special_notes_vi" id="hotel_info_special_notes_vi" rows="3"
                          placeholder="Ghi chú đặc biệt khác..."><?php echo esc_textarea($data['vi']['special_notes']); ?></textarea>
            </div>
        </div>

        <!-- English Tab -->
        <div id="tab-english" class="tab-content">
            <div class="form-group">
                <h4>📝 Hotel Name</h4>
                <label for="hotel_info_name_en">Hotel Name</label>
                <input type="text" name="hotel_info_name_en" id="hotel_info_name_en"
                       value="<?php echo esc_attr($data['en']['name']); ?>"
                       placeholder="Hotel name in English">
            </div>

            <div class="form-group">
                <h4>📍 Address</h4>
                <label for="hotel_info_address_en">Address</label>
                <input type="text" name="hotel_info_address_en" id="hotel_info_address_en"
                       value="<?php echo esc_attr($data['en']['address']); ?>"
                       placeholder="Address in English">
            </div>

            <div class="form-group">
                <h4>🕐 Check-in / Check-out Policy</h4>
                <label for="hotel_info_policy_en">Check-in / Check-out Policy</label>
                <textarea name="hotel_info_policy_en" id="hotel_info_policy_en" rows="3"
                          placeholder="Example: Check-in: 2:00 PM | Check-out: 12:00 PM | Late checkout may incur additional charges"><?php echo esc_textarea($data['en']['policy']); ?></textarea>
                <div class="description">Enter information about check-in, check-out times and related policies</div>
            </div>

            <div class="form-group">
                <h4>📝 Hotel Description</h4>
                <label for="hotel_info_description_en">Detailed Description</label>
                <textarea name="hotel_info_description_en" id="hotel_info_description_en" rows="5"
                          placeholder="Detailed hotel description in English"><?php echo esc_textarea($data['en']['description']); ?></textarea>
            </div>

            <div class="form-group">
                <h4>📄 Short Description</h4>
                <label for="hotel_info_short_description_en">Brief Description</label>
                <input type="text" name="hotel_info_short_description_en" id="hotel_info_short_description_en"
                       value="<?php echo esc_attr($data['en']['short_description']); ?>"
                       placeholder="Brief description of the hotel">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🏨 Amenities</h4>
                        <label for="hotel_info_amenities_en">Hotel Amenities</label>
                        <textarea name="hotel_info_amenities_en" id="hotel_info_amenities_en" rows="4"
                                  placeholder="Free WiFi, Swimming Pool, Gym, Spa..."><?php echo esc_textarea($data['en']['amenities']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🏗️ Facilities</h4>
                        <label for="hotel_info_facilities_en">Facilities</label>
                        <textarea name="hotel_info_facilities_en" id="hotel_info_facilities_en" rows="4"
                                  placeholder="Elevator, Parking, Conference Room..."><?php echo esc_textarea($data['en']['facilities']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🛎️ Services</h4>
                        <label for="hotel_info_services_en">Hotel Services</label>
                        <textarea name="hotel_info_services_en" id="hotel_info_services_en" rows="4"
                                  placeholder="Room service, Laundry service, Airport shuttle..."><?php echo esc_textarea($data['en']['services']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🗺️ Nearby Attractions</h4>
                        <label for="hotel_info_nearby_attractions_en">Attractions</label>
                        <textarea name="hotel_info_nearby_attractions_en" id="hotel_info_nearby_attractions_en" rows="4"
                                  placeholder="Museums, Notre Dame Cathedral, Ben Thanh Market..."><?php echo esc_textarea($data['en']['nearby_attractions']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🚗 Transportation</h4>
                        <label for="hotel_info_transportation_en">Transportation Info</label>
                        <textarea name="hotel_info_transportation_en" id="hotel_info_transportation_en" rows="3"
                                  placeholder="30km from airport, 5km from train station..."><?php echo esc_textarea($data['en']['transportation']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🍽️ Dining Options</h4>
                        <label for="hotel_info_dining_options_en">Dining</label>
                        <textarea name="hotel_info_dining_options_en" id="hotel_info_dining_options_en" rows="3"
                                  placeholder="Buffet restaurant, Bar, Café..."><?php echo esc_textarea($data['en']['dining_options']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🛏️ Room Features</h4>
                        <label for="hotel_info_room_features_en">Room Features</label>
                        <textarea name="hotel_info_room_features_en" id="hotel_info_room_features_en" rows="3"
                                  placeholder="Air conditioning, LED TV, Mini bar..."><?php echo esc_textarea($data['en']['room_features']); ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>❌ Cancellation Policy</h4>
                        <label for="hotel_info_cancellation_policy_en">Cancellation Policy</label>
                        <textarea name="hotel_info_cancellation_policy_en" id="hotel_info_cancellation_policy_en" rows="3"
                                  placeholder="Free cancellation before 24h..."><?php echo esc_textarea($data['en']['cancellation_policy']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <h4>📋 Terms & Conditions</h4>
                <label for="hotel_info_terms_conditions_en">Terms and Conditions</label>
                <textarea name="hotel_info_terms_conditions_en" id="hotel_info_terms_conditions_en" rows="4"
                          placeholder="Terms and conditions for service usage..."><?php echo esc_textarea($data['en']['terms_conditions']); ?></textarea>
            </div>

            <div class="form-group">
                <h4>📝 Special Notes</h4>
                <label for="hotel_info_special_notes_en">Special Notes</label>
                <textarea name="hotel_info_special_notes_en" id="hotel_info_special_notes_en" rows="3"
                          placeholder="Other special notes..."><?php echo esc_textarea($data['en']['special_notes']); ?></textarea>
            </div>
        </div>

        <!-- Common Fields Tab -->
        <div id="tab-common" class="tab-content">
            <div class="form-group">
                <h4>🔄 Thông tin chung</h4>
                <div class="description">Thông tin này sẽ hiển thị giống nhau cho tất cả ngôn ngữ</div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>📞 Liên hệ</h4>
                        <label for="hotel_info_phone">Số điện thoại</label>
                        <input type="tel" name="hotel_info_phone" id="hotel_info_phone"
                               value="<?php echo esc_attr($common_data['phone']); ?>"
                               placeholder="+84 123 456 789">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>✉️ Email</h4>
                        <label for="hotel_info_email">Địa chỉ email</label>
                        <input type="email" name="hotel_info_email" id="hotel_info_email"
                               value="<?php echo esc_attr($common_data['email']); ?>"
                               placeholder="info@hotel.com">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>📠 Fax & Website</h4>
                        <label for="hotel_info_fax">Số fax</label>
                        <input type="tel" name="hotel_info_fax" id="hotel_info_fax"
                               value="<?php echo esc_attr($common_data['fax']); ?>"
                               placeholder="+84 123 456 790">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>🌐 Website</h4>
                        <label for="hotel_info_website">Website chính thức</label>
                        <input type="url" name="hotel_info_website" id="hotel_info_website"
                               value="<?php echo esc_attr($common_data['website']); ?>"
                               placeholder="https://hotel.com">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <h4>🏢 Thông tin doanh nghiệp</h4>
                        <label for="hotel_info_tax_code">Mã số thuế</label>
                        <input type="text" name="hotel_info_tax_code" id="hotel_info_tax_code"
                               value="<?php echo esc_attr($common_data['tax_code']); ?>"
                               placeholder="0123456789">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <h4>📄 Giấy phép</h4>
                        <label for="hotel_info_business_license">Giấy phép kinh doanh</label>
                        <input type="text" name="hotel_info_business_license" id="hotel_info_business_license"
                               value="<?php echo esc_attr($common_data['business_license']); ?>"
                               placeholder="GPKD123456">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col-small">
                    <div class="form-group">
                        <h4>⭐ Xếp hạng</h4>
                        <label for="hotel_info_star_rating">Xếp hạng sao</label>
                        <select name="hotel_info_star_rating" id="hotel_info_star_rating">
                            <option value="">Chọn xếp hạng</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($common_data['star_rating'], $i); ?>><?php echo $i; ?> sao</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-col-small">
                    <div class="form-group">
                        <h4>📅 Năm thành lập</h4>
                        <label for="hotel_info_established_year">Năm thành lập</label>
                        <input type="number" name="hotel_info_established_year" id="hotel_info_established_year"
                               value="<?php echo esc_attr($common_data['established_year']); ?>"
                               min="1900" max="<?php echo date('Y'); ?>" placeholder="2020">
                    </div>
                </div>
                <div class="form-col-small">
                    <div class="form-group">
                        <h4>🏨 Số phòng</h4>
                        <label for="hotel_info_total_rooms">Tổng số phòng</label>
                        <input type="number" name="hotel_info_total_rooms" id="hotel_info_total_rooms"
                               value="<?php echo esc_attr($common_data['total_rooms']); ?>"
                               min="1" placeholder="50">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col-small">
                    <div class="form-group">
                        <h4>🕐 Check-in</h4>
                        <label for="hotel_info_check_in_time">Giờ check-in</label>
                        <input type="time" name="hotel_info_check_in_time" id="hotel_info_check_in_time"
                               value="<?php echo esc_attr($common_data['check_in_time']); ?>">
                    </div>
                </div>
                <div class="form-col-small">
                    <div class="form-group">
                        <h4>🕐 Check-out</h4>
                        <label for="hotel_info_check_out_time">Giờ check-out</label>
                        <input type="time" name="hotel_info_check_out_time" id="hotel_info_check_out_time"
                               value="<?php echo esc_attr($common_data['check_out_time']); ?>">
                    </div>
                </div>
                <div class="form-col-small">
                    <div class="form-group">
                        <h4>💰 Tiền tệ</h4>
                        <label for="hotel_info_currency">Tiền tệ</label>
                        <select name="hotel_info_currency" id="hotel_info_currency">
                            <option value="VND" <?php selected($common_data['currency'], 'VND'); ?>>VND - Việt Nam Đồng</option>
                            <option value="USD" <?php selected($common_data['currency'], 'USD'); ?>>USD - US Dollar</option>
                            <option value="EUR" <?php selected($common_data['currency'], 'EUR'); ?>>EUR - Euro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <h4>🌍 Múi giờ</h4>
                <label for="hotel_info_timezone">Múi giờ</label>
                <select name="hotel_info_timezone" id="hotel_info_timezone">
                    <option value="Asia/Ho_Chi_Minh" <?php selected($common_data['timezone'], 'Asia/Ho_Chi_Minh'); ?>>Asia/Ho_Chi_Minh (UTC+7)</option>
                    <option value="Asia/Bangkok" <?php selected($common_data['timezone'], 'Asia/Bangkok'); ?>>Asia/Bangkok (UTC+7)</option>
                    <option value="Asia/Singapore" <?php selected($common_data['timezone'], 'Asia/Singapore'); ?>>Asia/Singapore (UTC+8)</option>
                </select>
            </div>

            <div class="form-group">
                <h4>🗺️ Google Map</h4>
                <label for="hotel_info_map">Iframe Google Map</label>
                <textarea name="hotel_info_map" id="hotel_info_map" rows="5"
                          placeholder="Dán iframe code từ Google Maps ở đây..."><?php echo esc_textarea($common_data['map']); ?></textarea>
                <div class="description">Dán iframe code từ Google Maps để hiển thị bản đồ vị trí khách sạn</div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(evt, tabName) {
            // Hide all tab contents
            var tabContents = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }

            // Remove active class from all tab links
            var tabLinks = document.getElementsByClassName('tab-link');
            for (var i = 0; i < tabLinks.length; i++) {
                tabLinks[i].classList.remove('active');
            }

            // Show the selected tab content and mark the button as active
            document.getElementById('tab-' + tabName).classList.add('active');
            evt.currentTarget.classList.add('active');

            // Prevent default link behavior
            evt.preventDefault();
            return false;
        }

        // Auto-save functionality (optional)
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form');
            var inputs = form.querySelectorAll('input, textarea, select');

            // Add change listeners for auto-save indication
            inputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    // You can add auto-save indication here if needed
                    console.log('Field changed:', this.name, '=', this.value);
                });
            });
        });
    </script>

<?php
    submit_button('💾 Lưu tất cả thông tin');
    echo '</form></div>';
}
// Hàm hiển thị frontend theo ngôn ngữ
function hotel_info_display()
{
    $lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';

    // Các trường có đa ngôn ngữ
    $name    = get_option("hotel_info_name_{$lang}", '');
    $address = get_option("hotel_info_address_{$lang}", '');
    $policy  = get_option("hotel_info_policy_{$lang}", '');

    // Các trường không theo ngôn ngữ (chung)
    $phone   = get_option("hotel_info_phone", '');
    $email   = get_option("hotel_info_email", '');
    $map     = get_option("hotel_info_map", '');

    if (!$name && !$address && !$phone && !$email && !$map && !$policy) return;

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

    // Hiển thị quy định check-in/check-out
    if ($policy) {
        echo '<div class="hotel-policy" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba; border-radius: 4px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #007cba;">🕐 ' . esc_html__('Quy định Check-in / Check-out:', 'hotel-info-plugin') . '</h4>';
        echo '<div style="line-height: 1.6;">' . nl2br(esc_html($policy)) . '</div>';
        echo '</div>';
    }

    if ($map) {
        echo '<div style="margin-top: 20px;">' . $map . '</div>';
    }
}

// Shortcode gọi từ trình soạn thảo: [hotel_info]
add_shortcode('hotel_info', function () {
    ob_start();
    hotel_info_display();
    return ob_get_clean();
});
