<?php
get_header();

// Lấy thông tin bài viết
$post_id = get_the_ID();
$title = get_the_title($post_id);
$post_obj = get_post($post_id);
$content = apply_filters('the_content', $post_obj->post_content);
$bed_type = get_post_meta($post_id, '_hotel_room_bed_type', true);
$amenities = get_post_meta($post_id, '_hotel_room_amenities', true);
$bathroom_amenities = get_post_meta($post_id, '_hotel_room_bathroom_amenities', true);
$view = get_post_meta($post_id, '_hotel_room_view', true);
$price = get_post_meta($post_id, '_hotel_room_price', true);
$area = get_post_meta($post_id, '_hotel_room_area', true);
$main_amenities = get_post_meta($post_id, '_hotel_room_main_amenities', true);
$gallery = get_post_meta($post_id, '_hotel_room_gallery', true);

$gallery_ids = $gallery ? explode(',', $gallery) : [];
?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        ?>
        <section class="section section-lg section-main-bunner section-main-bunner-filter banner">
            <div class="main-bunner-img"
                style="background-image: url('<?php echo esc_url($thumbnail_url ?: get_template_directory_uri() . '/images/default-banner.jpg'); ?>');
                    background-size: cover; background-position: center; background-repeat: no-repeat;">
            </div>
            <div class="main-bunner-inner">
                <div class="container">
                    <div class="row row-50 justify-content-lg-center align-items-lg-center">
                        <div class="col-lg-12">
                            <div class="bunner-content-modern text-center text-white">
                                <h1 class="display-4"><?php the_title(); ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<?php endwhile;
endif; ?>
<section class="single-room container py-5">
    <div class="row">
        <div class="col-md-8 col-12">
            <?php if (!empty($gallery_ids)) : ?>
                <div id="carouselRoom<?php echo esc_attr($post_id); ?>" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php foreach ($gallery_ids as $i => $img_id) : ?>
                            <li data-target="#carouselRoom<?php echo esc_attr($post_id); ?>" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>"></li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php foreach ($gallery_ids as $i => $img_id) :
                            $img_url = wp_get_attachment_url($img_id);
                            if (!$img_url) continue;
                        ?>
                            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                <img class="d-block w-100" src="<?php echo esc_url($img_url); ?>" alt="Slide <?php echo $i + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="carousel-control-prev" href="#carouselRoom<?php echo esc_attr($post_id); ?>" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselRoom<?php echo esc_attr($post_id); ?>" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            <?php else : ?>
                <p><?php _e('Không có hình ảnh.', 'hotel'); ?></p>
            <?php endif; ?>
        </div>

        <div class="col-md-4 col-12">
            <div class="team-name"><?php echo esc_html($title); ?></div>

            <?php if ($main_amenities) : ?>
                <p><?php echo nl2br(esc_html($main_amenities)); ?></p>
            <?php endif; ?>

            <ul class="room-info list-unstyled">
                <?php if ($area) : ?><li><?php _e('Diện tích', 'hotel'); ?>: <?php echo esc_html($area); ?></li><?php endif; ?>
                <?php if ($view) : ?><li><?php _e('Hướng', 'hotel'); ?>: <?php echo esc_html($view); ?></li><?php endif; ?>
                <?php if ($bed_type) : ?><li><?php _e('Loại giường', 'hotel'); ?>: <?php echo esc_html($bed_type); ?></li><?php endif; ?>
                <?php if ($price) : ?><li><?php _e('Giá', 'hotel'); ?>: <?php echo esc_html($price); ?></li><?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="room-full-description mt-4">
        <?php echo $content; ?>
    </div>

    <?php if ($amenities || $bathroom_amenities) : ?>
        <div class="row mt-4">
            <?php if ($bathroom_amenities) : ?>
                <div class="col-md-6">
                    <p><strong><?php _e('Trong phòng tắm riêng của bạn', 'hotel'); ?>:</strong></p>
                    <ul>
                        <?php foreach (explode("\n", $bathroom_amenities) as $item) : ?>
                            <li><?php echo esc_html(trim($item)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($amenities) : ?>
                <div class="col-md-6">
                    <p><strong><?php _e('Tiện nghi phòng', 'hotel'); ?>:</strong></p>
                    <ul>
                        <?php foreach (explode("\n", $amenities) as $item) : ?>
                            <li><?php echo esc_html(trim($item)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-12">
            <h3><?php _e('Phòng liên quan', 'hotel'); ?></h3>

            <?php
            $related_args = [
                'post_type' => 'room',
                'posts_per_page' => 9,
                'post__not_in' => [$post_id],
                'orderby' => 'rand',
            ];
            $related_query = new WP_Query($related_args);

            if ($related_query->have_posts()) :
                $rooms = $related_query->posts;
                $chunks = array_chunk($rooms, 3); // 3 phòng 1 slide
            ?>
                <div id="relatedRoomsCarousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($chunks as $i => $chunk) : ?>
                            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                <div class="row">
                                    <?php foreach ($chunk as $room) :
                                        $r_id = $room->ID;
                                        $r_thumb = get_the_post_thumbnail_url($r_id, 'medium'); // medium size
                                        $r_price = get_post_meta($r_id, '_hotel_room_price', true);
                                    ?>
                                        <div class="col-4">
                                            <div class="card mb-3">
                                                <?php if ($r_thumb) : ?>
                                                    <img src="<?php echo esc_url($r_thumb); ?>" class="card-img-top img-fluid" alt="<?php echo esc_attr(get_the_title($r_id)); ?>">
                                                <?php endif; ?>
                                                <div class="card-body p-2">
                                                    <h6 class="card-title mb-1"><?php echo get_the_title($r_id); ?></h6>
                                                    <p class="card-text mb-0"><?php echo esc_html($r_price ?: __('Liên hệ', 'hotel')); ?></p>
                                                    <a href="<?php echo get_permalink($r_id); ?>" class="stretched-link"></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($chunks) > 1) : // chỉ hiển thị control nếu >1 slide 
                    ?>
                        <a class="carousel-control-prev" href="#relatedRoomsCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#relatedRoomsCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    <?php endif; ?>
                </div>

            <?php
            else :
                echo '<p>' . __('Không có phòng liên quan', 'hotel') . '</p>';
            endif;
            wp_reset_postdata();
            ?>
        </div>
    </div>

</section>

<?php get_footer(); ?>