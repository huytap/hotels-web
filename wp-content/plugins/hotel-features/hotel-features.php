<?php
/*
Plugin Name: Hotel Features Manager
Description: Quản lý các hạng mục nổi bật (Đội ngũ, Nhà hàng, Spa,...) để hiển thị trên trang chủ.
Version: 1.0
Author: Tap Nguyen
Text Domain: hotel
*/

// 1. Đăng ký Custom Post Type
add_action('init', function () {
    $labels = array(
        'name'               => __('Hạng mục', 'hotel-features'),
        'singular_name'      => __('Hạng mục', 'hotel-features'),
        'add_new'            => __('Thêm hạng mục', 'hotel-features'),
        'add_new_item'       => __('Thêm hạng mục mới', 'hotel-features'),
        'edit_item'          => __('Sửa hạng mục', 'hotel-features'),
        'new_item'           => __('Hạng mục mới', 'hotel-features'),
        'view_item'          => __('Xem hạng mục', 'hotel-features'),
        'search_items'       => __('Tìm kiếm hạng mục', 'hotel-features'),
        'not_found'          => __('Không tìm thấy hạng mục', 'hotel-features'),
        'not_found_in_trash' => __('Không có hạng mục nào trong thùng rác', 'hotel-features'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'menu_icon'          => 'dashicons-star-filled',
        'supports'           => array('title', 'editor', 'thumbnail'),
        'show_in_rest'       => true,
    );

    register_post_type('hotel_feature', $args);
});

// 2. Thêm meta box chọn icon (ví dụ dùng Linearicons hoặc Font Awesome)
add_action('add_meta_boxes', function () {
    add_meta_box(
        'hotel_feature_icon',
        __('Biểu tượng', 'hotel-features'),
        'hotel_feature_icon_callback',
        'hotel_feature',
        'side'
    );
});

function hotel_feature_icon_callback($post)
{
    $value = get_post_meta($post->ID, '_hotel_feature_icon', true);
    echo '<label for="hotel_feature_icon">' . __('Nhập class icon (vd: linearicons-woman)', 'hotel-features') . '</label>';
    echo '<input type="text" id="hotel_feature_icon" name="hotel_feature_icon" value="' . esc_attr($value) . '" style="width:100%;margin-top:5px;" />';
}

add_action('save_post', function ($post_id) {
    if (isset($_POST['hotel_feature_icon'])) {
        update_post_meta($post_id, '_hotel_feature_icon', sanitize_text_field($_POST['hotel_feature_icon']));
    }
});

// 3. Hàm hiển thị ra frontend
function hotel_features_display()
{
    $args = array(
        'post_type'      => 'hotel_feature',
        'posts_per_page' => 6,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) :
?>
        <section class="section section-lg text-center bg-gray-light">
            <div class="container">
                <div class="row row-50">
                    <?php while ($query->have_posts()) : $query->the_post();
                        $icon = get_post_meta(get_the_ID(), '_hotel_feature_icon', true);
                    ?>
                        <div class="col-md-6 col-lg-4 wow-outer">
                            <div class="wow fadeInUp">
                                <div class="box-icon-classic">
                                    <div class="box-icon-inner decorate-triangle">
                                        <?php if ($icon): ?>
                                            <span class="icon-xl <?php echo esc_attr($icon); ?>"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="box-icon-caption">
                                        <h4><?php the_title(); ?></h4>
                                        <p><?php the_content(); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
<?php
        wp_reset_postdata();
    endif;
}
