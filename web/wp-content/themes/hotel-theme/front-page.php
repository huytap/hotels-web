<?php

/**
 * Template Name: Front Page - Hotel Homepage
 * Description: Mẫu trang chủ dành cho khách sạn
 */

get_header(); ?>
<section class="section section-lg section-main-bunner section-main-bunner-filter">
    <div id="hotel-slider" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php
            $args = array(
                'post_type'      => 'slider',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            );
            $slider_query = new WP_Query($args);
            if ($slider_query->have_posts()) :
                $slide_index = 0;
                while ($slider_query->have_posts()) : $slider_query->the_post();
                    $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    $stars = get_post_meta(get_the_ID(), '_hotel_slider_stars', true);
                    $cta_text = get_post_meta(get_the_ID(), '_hotel_slider_cta_text', true);
                    $cta_link = get_post_meta(get_the_ID(), '_hotel_slider_cta_link', true);
            ?>
                    <div class="carousel-item <?php echo ($slide_index === 0) ? 'active' : ''; ?>">
                        <div class="main-bunner-img" style="background-image: url('<?php echo esc_url($image_url); ?>'); background-size: cover; background-position: center; height: 100vh;">
                        </div>
                        <div class="main-bunner-inner">
                            <div class="container">
                                <div class="row row-50 justify-content-lg-center align-items-lg-center">
                                    <div class="col-lg-12">
                                        <div class="bunner-content-modern text-center">
                                            <h1 class="text-uppercase"><?php the_title(); ?></h1>
                                            <?php if ($stars > 0): ?>
                                                <div class="star-section my-4">
                                                    <?php for ($i = 0; $i < $stars; $i++): ?>
                                                        <span class="icon mdi-star"></span>
                                                    <?php endfor; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php the_content(); ?>
                                            <?php if ($cta_text && $cta_link): ?>
                                                <a href="<?php echo esc_url($cta_link); ?>" class="btn btn-primary mt-3">
                                                    <?php echo esc_html($cta_text); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                    $slide_index++;
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

        <a class="carousel-control-prev" href="#hotel-slider" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Trước</span>
        </a>
        <a class="carousel-control-next" href="#hotel-slider" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Tiếp</span>
        </a>
    </div>
</section>
<?php
$booking_code = get_theme_mod('hotel_booking_code', '');
if ($booking_code) {
    echo $booking_code;
}
?>
<?php if (have_posts()) :
    while (have_posts()) : the_post();
?>
        <section class="section section-sm bg-default" id="about">
            <div class="container">
                <div class="row row-50 justify-content-xl-between align-items-lg-center">
                    <div class="col-lg-6 col-xl-6 wow-outer">
                        <div class="wow slideInLeft text-xl-center">
                            <?php
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('large', ['class' => 'img-fluid']);
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-6 wow-outer">
                        <div class="wow slideInRight">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<?php
    endwhile;
endif; ?>

<?php
if (function_exists('hotel_show_room_info')) {
    hotel_show_room_info();
}
?>

<?php
if (function_exists('dsd_render_services_on_frontpage')) {
    dsd_render_services_on_frontpage();
}
?>

<?php echo do_shortcode('[simple_gallery]'); ?>
<?php
if (function_exists('hcf_display_contact_form')): ?>
    <section class="section section-lg section-relative" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <?php
                    if (function_exists('hcf_display_contact_form')) {
                        hcf_display_contact_form();
                    }
                    ?>
                </div>
                <div class="col-md-6">
                    <?php if (function_exists('hotel_info_display')) hotel_info_display(); ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php if (function_exists('hotel_features_display')) {
    hotel_features_display();
} ?>

<?php get_footer(); ?>