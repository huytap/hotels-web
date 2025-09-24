<?php
/**
 * Amenities Section - Icons Only Layout
 */
?>

<section class="content-section amenities-section amenities-icons-only" id="amenities">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('amenities_title', 'Tiện nghi'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('amenities_subtitle', 'Đầy đủ tiện nghi phục vụ nhu cầu nghỉ dưỡng và công việc'); ?></p>
        </div>
        
        <div class="amenities-icons-container">
            <?php 
            // Get amenities data
            $amenities = get_posts(array(
                'post_type' => 'hms_amenity',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            if (!empty($amenities)):
                foreach ($amenities as $amenity): ?>
                    <div class="amenity-icon-item" title="<?php echo esc_attr($amenity->post_title); ?>">
                        <span class="amenity-icon">
                            <?php 
                            $icon = get_post_meta($amenity->ID, '_hms_amenity_icon', true);
                            echo $icon ? esc_html($icon) : '⭐';
                            ?>
                        </span>
                        <span class="amenity-label"><?php echo esc_html($amenity->post_title); ?></span>
                    </div>
                <?php endforeach;
            else:
                // Default amenities with icons
                $default_amenities = array(
                    array('icon' => '🏊‍♂️', 'name' => 'Hồ bơi'),
                    array('icon' => '🍽️', 'name' => 'Nhà hàng'),
                    array('icon' => '💆‍♀️', 'name' => 'Spa'),
                    array('icon' => '🏋️‍♂️', 'name' => 'Phòng gym'),
                    array('icon' => '🚗', 'name' => 'Bãi đỗ xe'),
                    array('icon' => '📶', 'name' => 'WiFi miễn phí'),
                    array('icon' => '❄️', 'name' => 'Điều hòa'),
                    array('icon' => '🛎️', 'name' => 'Dịch vụ phòng 24/7'),
                );
                
                foreach ($default_amenities as $amenity): ?>
                    <div class="amenity-icon-item">
                        <span class="amenity-icon"><?php echo esc_html($amenity['icon']); ?></span>
                        <span class="amenity-label"><?php echo esc_html($amenity['name']); ?></span>
                    </div>
                <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>