<?php
/**
 * Services Section - Grid Layout
 */
?>

<section class="content-section services-section services-grid" id="services">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('services_title', 'Dịch vụ'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('services_subtitle', 'Dịch vụ chuyên nghiệp mang đến trải nghiệm hoàn hảo'); ?></p>
        </div>
        
        <?php 
        if (shortcode_exists('hms_services_grid')) {
            $services_limit = get_theme_mod('services_display_limit', 4);
            $services_columns = get_theme_mod('services_grid_columns', 2);
            echo do_shortcode("[hms_services_grid limit='{$services_limit}' columns='{$services_columns}']");
        } else {
            echo '<div class="services-placeholder">';
            echo '<p>' . __('Services are being configured. Please check back soon.', 'hotel-theme') . '</p>';
            echo '</div>';
        }
        ?>
        
        <?php 
        $services_page_url = get_theme_mod('services_page_url', '/services');
        if ($services_page_url): ?>
            <div class="text-center mt-4">
                <a href="<?php echo esc_url($services_page_url); ?>" class="btn btn-primary"><?php _e('Xem tất cả dịch vụ', 'hotel-theme'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>