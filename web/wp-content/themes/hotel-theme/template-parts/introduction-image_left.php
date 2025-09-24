<?php
/**
 * Introduction Section - Image Left Layout
 */
?>

<section class="content-section intro-section intro-image-left" id="about">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('intro_title', 'Về chúng tôi'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('intro_subtitle', 'Khách sạn hàng đầu với hơn 20 năm kinh nghiệm phục vụ du khách trong và ngoài nước'); ?></p>
        </div>
        
        <div class="intro-content">
            <div class="intro-image">
                <?php 
                $intro_image = get_theme_mod('intro_image');
                if ($intro_image) {
                    echo '<img src="' . esc_url($intro_image) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
                } else {
                    echo '<div class="placeholder-image">🏨</div>';
                }
                ?>
            </div>
            <div class="intro-text">
                <h3><?php echo get_theme_mod('intro_heading', get_bloginfo('name')); ?></h3>
                <div class="intro-description">
                    <?php 
                    $intro_content = get_theme_mod('intro_content', 'Tọa lạc tại vị trí đắc địa trung tâm thành phố, khách sạn chúng tôi mang đến cho quý khách trải nghiệm nghỉ dưỡng đẳng cấp với dịch vụ chuyên nghiệp và không gian sang trọng.');
                    echo wp_kses_post($intro_content);
                    ?>
                </div>
                
                <div class="quick-facts">
                    <?php
                    $facts = array(
                        array('icon' => '📍', 'title' => 'Vị trí đắc địa', 'desc' => 'Trung tâm thành phố'),
                        array('icon' => '⭐', 'title' => '5 sao', 'desc' => 'Đánh giá xuất sắc'),
                        array('icon' => '🏆', 'title' => '20+ năm', 'desc' => 'Kinh nghiệm phục vụ')
                    );
                    
                    foreach ($facts as $fact): ?>
                        <div class="fact">
                            <span class="fact-icon"><?php echo esc_html($fact['icon']); ?></span>
                            <strong><?php echo esc_html($fact['title']); ?></strong>
                            <span><?php echo esc_html($fact['desc']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php 
                $about_link = get_theme_mod('about_page_url', '/about');
                if ($about_link): ?>
                    <a href="<?php echo esc_url($about_link); ?>" class="btn btn-primary"><?php _e('Tìm hiểu thêm', 'hotel-theme'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>