<?php
/**
 * Booking Section - Separate Section Layout
 */
?>

<section class="content-section booking-section" id="booking">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('booking_title', 'Đặt phòng'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('booking_subtitle', 'Chọn ngày và phòng phù hợp với nhu cầu của bạn'); ?></p>
        </div>
        
        <div class="booking-form-container">
            <?php 
            if (shortcode_exists('hms_booking_form')) {
                echo do_shortcode('[hms_booking_form style="full"]');
            } else {
                echo '<div class="booking-placeholder">';
                echo '<p>' . __('Booking system is being configured. Please check back soon.', 'hotel-theme') . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>