<?php
/**
 * Hero Section - Default Layout
 */
$booking_form_layout = get_theme_mod('booking_form_layout', 'overlay');
?>

<section class="hero-section hero-default">
    <div class="hero-content">
        <h1><?php echo get_theme_mod('hero_title', 'Trải nghiệm nghỉ dưỡng đẳng cấp'); ?></h1>
        <p><?php echo get_theme_mod('hero_subtitle', 'Khám phá không gian sang trọng với dịch vụ 5 sao và tầm nhìn tuyệt đẹp ra biển'); ?></p>
        <div class="hero-buttons">
            <a href="#booking" class="btn btn-primary"><?php _e('Đặt phòng ngay', 'hotel-theme'); ?></a>
            <a href="#rooms" class="btn btn-secondary"><?php _e('Khám phá phòng', 'hotel-theme'); ?></a>
        </div>
    </div>
    
    <?php if ($booking_form_layout === 'overlay'): ?>
        <!-- Booking Bar -->
        <div class="booking-bar" id="booking">
            <?php 
            if (shortcode_exists('hms_booking_form')) {
                echo do_shortcode('[hms_booking_form style="compact"]');
            } else {
                echo '<p>' . __('Booking system is being configured. Please check back soon.', 'hotel-theme') . '</p>';
            }
            ?>
        </div>
    <?php endif; ?>
</section>