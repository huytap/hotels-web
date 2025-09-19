<!DOCTYPE html>
<html <?php language_attributes(); ?> class="wide wow-animation">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body>
    <div class="page">
        <header class="section page-header" id="home">
            <!-- RD Navbar-->
            <div class="rd-navbar-wrap">
                <nav class="rd-navbar rd-navbar-aside" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
                    data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed"
                    data-lg-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-fixed"
                    data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static"
                    data-xxl-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static"
                    data-lg-stick-up-offset="46px" data-xl-stick-up-offset="46px" data-xxl-stick-up-offset="46px"
                    data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
                    <div class="rd-navbar-main-outer">
                        <div class="rd-navbar-main">
                            <!-- RD Navbar Panel-->
                            <div class="rd-navbar-panel">
                                <!-- RD Navbar Toggle-->
                                <button class="rd-navbar-toggle"
                                    data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
                                <!-- RD Navbar Brand-->
                                <div class="rd-navbar-brand">
                                    <?php
                                    if (function_exists('the_custom_logo') && has_custom_logo()) {
                                        the_custom_logo();
                                    } else { ?>
                                        <a href="<?php echo esc_url(home_url('/')); ?>">
                                            <img class="brand-logo-light"
                                                src="<?php echo get_template_directory_uri(); ?>/images/logo.png"
                                                alt="<?php bloginfo('name'); ?>"
                                                height="55" />
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="block-right">
                                <div class="rd-navbar-nav-wrap">
                                    <?php
                                    wp_nav_menu(array(
                                        //'theme_location' => 'primary-menu',
                                        'container' => false,
                                        'menu_class' => 'rd-navbar-nav-wrap',
                                        'menu_id' => '',
                                        'items_wrap' => '<ul class="rd-navbar-nav">%3$s</ul>',
                                        'depth' => 1,
                                        'link_before' => '',
                                        'link_after' => '',
                                        'walker' => new class extends Walker_Nav_Menu {
                                            function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
                                            {
                                                $classes = empty($item->classes) ? array() : (array) $item->classes;
                                                $classes[] = 'rd-nav-item';

                                                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
                                                $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

                                                $output .= '<li' . $class_names . '>';

                                                $attributes  = ! empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
                                                $attributes .= ' class="rd-nav-link"';

                                                $title = apply_filters('the_title', $item->title, $item->ID);

                                                $output .= '<a' . $attributes . '>' . $title . '</a>';
                                            }

                                            function end_el(&$output, $item, $depth = 0, $args = null)
                                            {
                                                $output .= "</li>\n";
                                            }
                                        }
                                    ));
                                    ?>
                                    <!-- <li class="rd-nav-item"><a href="/"><img width="30" src="images/vn.png"
                                                    alt="Tiếng Việt"></a></li>
                                        <li class="rd-nav-item" style="margin-left: 5px;"><a href="/en"><img width="30"
                                                    src="images/en.jpg" alt="English"></a></li> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>