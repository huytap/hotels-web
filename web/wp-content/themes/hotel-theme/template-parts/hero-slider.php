<?php
/**
 * Hero Section - Slider Layout
 */
$booking_form_layout = get_theme_mod('booking_form_layout', 'overlay');
?>

<section class="hero-section hero-slider">
    <div class="hero-slider-container">
        <div class="hero-slides">
            <?php
            // Get slider images from customizer or use defaults
            $slide_1 = get_theme_mod('hero_slide_1', '');
            $slide_2 = get_theme_mod('hero_slide_2', '');
            $slide_3 = get_theme_mod('hero_slide_3', '');
            
            $slides = array(
                array('image' => $slide_1, 'title' => get_theme_mod('hero_title', 'Trải nghiệm nghỉ dưỡng đẳng cấp'), 'desc' => get_theme_mod('hero_subtitle', 'Khám phá không gian sang trọng với dịch vụ 5 sao')),
                array('image' => $slide_2, 'title' => get_theme_mod('slide_2_title', 'Không gian sang trọng'), 'desc' => get_theme_mod('slide_2_desc', 'Thiết kế hiện đại kết hợp với dịch vụ tận tâm')),
                array('image' => $slide_3, 'title' => get_theme_mod('slide_3_title', 'Dịch vụ hoàn hảo'), 'desc' => get_theme_mod('slide_3_desc', 'Trải nghiệm đẳng cấp quốc tế tại Việt Nam'))
            );
            
            foreach ($slides as $index => $slide):
                $bg_image = $slide['image'] ?: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23e8f4fd" width="1200" height="600"/><text x="600" y="300" text-anchor="middle" font-size="48" fill="%23666">🏨 SLIDE ' . ($index + 1) . '</text></svg>';
            ?>
                <div class="hero-slide" style="background-image: url('<?php echo esc_url($bg_image); ?>')">
                    <div class="hero-slide-content">
                        <h1><?php echo esc_html($slide['title']); ?></h1>
                        <p><?php echo esc_html($slide['desc']); ?></p>
                        <div class="hero-buttons">
                            <a href="#booking" class="btn btn-primary"><?php _e('Đặt phòng ngay', 'hotel-theme'); ?></a>
                            <a href="#rooms" class="btn btn-secondary"><?php _e('Khám phá phòng', 'hotel-theme'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Slider Navigation -->
        <div class="hero-slider-nav">
            <button class="hero-prev" aria-label="Previous slide">‹</button>
            <button class="hero-next" aria-label="Next slide">›</button>
        </div>
        
        <!-- Slider Dots -->
        <div class="hero-slider-dots">
            <button class="dot active" data-slide="0"></button>
            <button class="dot" data-slide="1"></button>
            <button class="dot" data-slide="2"></button>
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