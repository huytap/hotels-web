<footer class="section footer-minimal context-dark">
    <div class="container wow-outer">
        <div class="wow fadeIn">
            <div class="row row-60">
                <div class="col-12">
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
                <div class="col-12">
                    <?php
                    wp_nav_menu(array(
                        //'theme_location' => 'primary-menu',
                        'container' => false,
                        'menu_class' => 'col-12',
                        'menu_id' => '',
                        'items_wrap' => '<ul class="footer-minimal-nav">%3$s</ul>',
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
                    )); ?>
                </div>
                <div class="col-12">
                    <?php if (function_exists('snm_display_social_networks')) snm_display_social_networks(); ?>
                </div>
            </div>
        </div>
        <p class="rights"><span>&copy;&nbsp;</span><span
                class="copyright-year"></span><span>&nbsp;</span><span>
                <?php $lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';
                echo get_option("hotel_info_name_{$lang}", ''); ?></span><span>.&nbsp;</span></p>
    </div>
</footer>
</div>
<a href="#" id="ui-to-top" class="ui-to-top fa fa-angle-up active"></a>
<?php wp_footer(); ?>