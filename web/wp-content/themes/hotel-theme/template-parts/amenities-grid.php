<?php
/**
 * Amenities Section - Grid Layout
 */
?>

<section class="content-section amenities-section amenities-grid" id="amenities">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('amenities_title', 'Tiện nghi'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('amenities_subtitle', 'Đầy đủ tiện nghi phục vụ nhu cầu nghỉ dưỡng và công việc'); ?></p>
        </div>
        
        <?php 
        if (shortcode_exists('hms_amenities_grid')) {
            $amenities_columns = get_theme_mod('amenities_grid_columns', 4);
            echo do_shortcode("[hms_amenities_grid columns='{$amenities_columns}']");
        } else {
            echo '<div class="amenities-placeholder">';
            echo '<p>' . __('Amenities are being configured. Please check back soon.', 'hotel-theme') . '</p>';
            echo '</div>';
        }
        ?>
    </div>
</section>