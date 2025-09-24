<?php
get_header(); ?>

<?php
// Get layout settings
$slider_layout = get_theme_mod('slider_layout', 'hero');
$booking_form_layout = get_theme_mod('booking_form_layout', 'overlay');

// Render hero/slider section based on layout choice
switch ($slider_layout) {
    case 'slider':
        get_template_part('template-parts/hero', 'slider');
        break;
    case 'video':
        get_template_part('template-parts/hero', 'video');
        break;
    default:
        get_template_part('template-parts/hero', 'default');
        break;
}

// Render booking form based on layout choice
// if ($booking_form_layout === 'section') {
//     get_template_part('template-parts/booking', 'section');
// }
?>

<?php
// Render introduction section based on layout choice
$intro_layout = get_theme_mod('intro_layout', 'image_left');
get_template_part('template-parts/introduction', $intro_layout);
?>

<?php
// Render rooms section based on layout choice
$rooms_layout = get_theme_mod('rooms_layout', 'grid');
get_template_part('template-parts/rooms', $rooms_layout);
?>

<?php
// Render amenities section based on layout choice
$amenities_layout = get_theme_mod('amenities_layout', 'grid');
get_template_part('template-parts/amenities', $amenities_layout);
?>

<?php
// Render services section based on layout choice
$services_layout = get_theme_mod('services_layout', 'grid');
get_template_part('template-parts/services', $services_layout);

// Render gallery section based on layout choice
$gallery_layout = get_theme_mod('gallery_layout', 'uniform_grid');
get_template_part('template-parts/gallery', $gallery_layout);
?>

<?php get_footer(); ?>