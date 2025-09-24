<?php
/**
 * Gallery Section - Uniform Grid Layout
 */
?>

<section class="content-section gallery-section gallery-uniform-grid" id="gallery">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('gallery_title', 'Hình ảnh khách sạn'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('gallery_subtitle', 'Khám phá không gian đẹp và tiện nghi của khách sạn'); ?></p>
        </div>
        
        <div class="gallery-grid uniform-grid">
            <?php
            // Get gallery images from WordPress media library or customizer
            $gallery_images = get_theme_mod('hotel_gallery_images', '');
            
            if ($gallery_images) {
                $image_ids = explode(',', $gallery_images);
                foreach ($image_ids as $image_id) {
                    $image_url = wp_get_attachment_image_url($image_id, 'large');
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    if ($image_url) {
                        echo '<div class="gallery-item">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" loading="lazy">';
                        echo '<div class="gallery-overlay">';
                        echo '<span class="gallery-zoom">🔍</span>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
            } else {
                // Default placeholder images
                $default_images = array(
                    array('title' => 'Phòng suite cao cấp', 'desc' => 'Không gian sang trọng'),
                    array('title' => 'Nhà hàng', 'desc' => 'Ẩm thực đẳng cấp'),
                    array('title' => 'Hồ bơi', 'desc' => 'Thư giãn tuyệt đối'),
                    array('title' => 'Spa & Wellness', 'desc' => 'Chăm sóc sức khỏe'),
                    array('title' => 'Phòng gym', 'desc' => 'Rèn luyện sức khỏe'),
                    array('title' => 'Sảnh chính', 'desc' => 'Chào đón ấm áp'),
                    array('title' => 'Tầng mái', 'desc' => 'Tầm nhìn tuyệt đẹp'),
                    array('title' => 'Phòng họp', 'desc' => 'Sự kiện chuyên nghiệp'),
                );
                
                foreach ($default_images as $index => $image): ?>
                    <div class="gallery-item placeholder">
                        <div class="gallery-placeholder" style="background: linear-gradient(45deg, #f0f8ff, #e8f4fd);">
                            <div class="placeholder-content">
                                <h4><?php echo esc_html($image['title']); ?></h4>
                                <p><?php echo esc_html($image['desc']); ?></p>
                            </div>
                        </div>
                        <div class="gallery-overlay">
                            <span class="gallery-zoom">🔍</span>
                        </div>
                    </div>
                <?php endforeach;
            }
            ?>
        </div>
        
        <?php 
        $gallery_page_url = get_theme_mod('gallery_page_url', '/gallery');
        if ($gallery_page_url): ?>
            <div class="text-center mt-4">
                <a href="<?php echo esc_url($gallery_page_url); ?>" class="btn btn-primary"><?php _e('Xem thêm hình ảnh', 'hotel-theme'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>