<?php
/**
 * Rooms Section - List Layout (One per row)
 */
?>

<section class="content-section rooms-section rooms-list" id="rooms">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo get_theme_mod('rooms_title', 'Phòng nghỉ'); ?></h2>
            <p class="section-subtitle"><?php echo get_theme_mod('rooms_subtitle', 'Lựa chọn phòng nghỉ phù hợp với nhu cầu của bạn'); ?></p>
        </div>
        
        <div class="rooms-list-container">
            <?php 
            if (shortcode_exists('hms_rooms_grid')) {
                $rooms_limit = get_theme_mod('rooms_display_limit', 6);
                
                // Get rooms data
                $rooms_query = new WP_Query(array(
                    'post_type' => 'hms_room',
                    'posts_per_page' => $rooms_limit,
                    'post_status' => 'publish'
                ));
                
                if ($rooms_query->have_posts()):
                    while ($rooms_query->have_posts()): $rooms_query->the_post(); ?>
                        <div class="room-list-item">
                            <div class="room-image">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('room-large'); ?>
                                <?php else: ?>
                                    <div class="placeholder-image">🛏️ <?php the_title(); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="room-content">
                                <h3 class="room-title"><?php the_title(); ?></h3>
                                <div class="room-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                <div class="room-features">
                                    <?php 
                                    $size = get_post_meta(get_the_ID(), '_hms_room_size', true);
                                    $beds = get_post_meta(get_the_ID(), '_hms_room_beds', true);
                                    $view = get_post_meta(get_the_ID(), '_hms_room_view', true);
                                    
                                    if ($size) echo '<span class="feature">📐 ' . esc_html($size) . 'm²</span>';
                                    if ($beds) echo '<span class="feature">🛏️ ' . esc_html($beds) . '</span>';
                                    if ($view) echo '<span class="feature">🪟 ' . esc_html(ucfirst($view)) . ' view</span>';
                                    ?>
                                </div>
                                <div class="room-price">
                                    <?php 
                                    $price = get_post_meta(get_the_ID(), '_hms_room_base_price', true);
                                    if ($price) {
                                        echo __('From', 'hotel-theme') . ' ' . number_format($price, 0, ',', '.') . ' VND/' . __('night', 'hotel-theme');
                                    }
                                    ?>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary"><?php _e('Xem chi tiết', 'hotel-theme'); ?></a>
                            </div>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata();
                else: ?>
                    <p><?php _e('No rooms available.', 'hotel-theme'); ?></p>
                <?php endif;
            } else {
                echo '<p>' . __('Rooms are being configured. Please check back soon.', 'hotel-theme') . '</p>';
            }
            ?>
        </div>
        
        <?php 
        $rooms_page_url = get_theme_mod('rooms_page_url', '/rooms');
        if ($rooms_page_url): ?>
            <div class="text-center mt-4">
                <a href="<?php echo esc_url($rooms_page_url); ?>" class="btn btn-primary"><?php _e('Xem tất cả phòng', 'hotel-theme'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>