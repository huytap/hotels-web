<?php
/**
 * Rooms Section - Grid Layout
 */
?>

<section class="content-section rooms-section rooms-grid" id="rooms">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('rooms_title', 'Phòng nghỉ'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('rooms_subtitle', 'Lựa chọn phòng nghỉ phù hợp với nhu cầu của bạn'); ?></p>
        </div>
        
        <?php 
        if (shortcode_exists('hms_rooms_grid')) {
            $rooms_limit = get_theme_mod('rooms_display_limit', 6);
            $rooms_columns = get_theme_mod('rooms_grid_columns', 3);
            echo do_shortcode("[hms_rooms_grid limit='{$rooms_limit}' columns='{$rooms_columns}']");
        } else {
            echo '<div class="rooms-placeholder">';
            echo '<p>' . __('Rooms are being configured. Please check back soon.', 'hotel-theme') . '</p>';
            echo '</div>';
        }
        ?>
        
        <?php 
        $rooms_page_url = get_theme_mod('rooms_page_url', '/rooms');
        if ($rooms_page_url): ?>
            <div class="text-center mt-4">
                <a href="<?php echo esc_url($rooms_page_url); ?>" class="btn btn-primary"><?php _e('Xem tất cả phòng', 'hotel-theme'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>