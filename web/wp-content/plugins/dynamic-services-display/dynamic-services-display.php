<?php
/*
Plugin Name: Dynamic Content Display
Description: Cho phép chọn nhiều danh mục dịch vụ từ trang làm trang chủ, sắp xếp thứ tự và hiển thị dịch vụ theo thứ tự đó.
Version: 1.1
Author: Tap Nguyen
Text Domain: hotel
*/

// Thêm meta box chọn nhiều danh mục dịch vụ cho trang
function dsd_add_service_categories_meta_box()
{
    add_meta_box(
        'dsd_service_categories_meta_box',
        __('Chọn danh mục dịch vụ hiển thị trên trang này', 'dynamic-services-display'),
        'dsd_render_service_categories_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'dsd_add_service_categories_meta_box');

function dsd_render_service_categories_meta_box($post)
{
    $selected_cats = get_post_meta($post->ID, '_dsd_service_category_ids', true);
    if (!is_array($selected_cats)) {
        $selected_cats = [];
    }

    $categories = get_categories();

    wp_nonce_field('dsd_save_service_categories_meta_box', 'dsd_service_categories_nonce');

    echo '<p><small>' . esc_html__('Chọn danh mục dịch vụ và kéo thả để sắp xếp thứ tự.', 'dynamic-services-display') . '</small></p>';
    echo '<ul id="dsd-selected-cats" style="list-style:none; padding:0; margin:0 0 10px 0;">';

    // Hiển thị danh sách đã chọn (sortable)
    foreach ($selected_cats as $cat_id) {
        $cat = get_category($cat_id);
        if ($cat) {
            echo '<li class="dsd-cat-item" style="margin:4px 0; padding:4px; border:1px solid #ccc; background:#f9f9f9; cursor:move;" data-cat-id="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . ' <a href="#" class="dsd-remove-cat" style="color:red; text-decoration:none; margin-left:8px;">&times;</a></li>';
        }
    }
    echo '</ul>';

    // Select box chọn thêm danh mục
    echo '<select id="dsd-cat-select" style="width:100%">';
    echo '<option value="">' . esc_html__('-- Chọn danh mục --', 'dynamic-services-display') . '</option>';
    foreach ($categories as $cat) {
        if (!in_array($cat->term_id, $selected_cats)) {
            echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
        }
    }
    echo '</select>';

    // Hidden input lưu danh sách cat id (chuỗi json)
    echo '<input type="hidden" name="dsd_service_category_ids" id="dsd_service_category_ids" value="' . esc_attr(json_encode($selected_cats)) . '">';

    // JS + CSS để sortable + thêm/xóa
?>
    <style>
        #dsd-cat-select {
            margin-top: 8px;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            function updateHiddenInput() {
                var arr = [];
                $('#dsd-selected-cats li.dsd-cat-item').each(function() {
                    arr.push($(this).data('cat-id'));
                });
                $('#dsd_service_category_ids').val(JSON.stringify(arr));
            }

            // Kéo thả sắp xếp
            $('#dsd-selected-cats').sortable({
                update: function() {
                    updateHiddenInput();
                }
            });

            // Xóa danh mục đã chọn
            $('#dsd-selected-cats').on('click', '.dsd-remove-cat', function(e) {
                e.preventDefault();
                $(this).parent().remove();
                updateHiddenInput();
                // Thêm lại option vào select
                var removedText = $(this).parent().text().replace('×', '').trim();
                $('#dsd-cat-select').append($('<option>', {
                    value: $(this).parent().data('cat-id'),
                    text: removedText
                })).trigger('change');
            });

            // Thêm danh mục mới từ select
            $('#dsd-cat-select').on('change', function() {
                var val = $(this).val();
                var text = $('#dsd-cat-select option:selected').text();
                if (val) {
                    var exists = false;
                    $('#dsd-selected-cats li').each(function() {
                        if ($(this).data('cat-id') == val) {
                            exists = true;
                            return false;
                        }
                    });
                    if (!exists) {
                        $('#dsd-selected-cats').append('<li class="dsd-cat-item" style="margin:4px 0; padding:4px; border:1px solid #ccc; background:#f9f9f9; cursor:move;" data-cat-id="' + val + '">' + text + ' <a href="#" class="dsd-remove-cat" style="color:red; text-decoration:none; margin-left:8px;">&times;</a></li>');
                        updateHiddenInput();
                        // Xóa option vừa chọn
                        $('#dsd-cat-select option[value="' + val + '"]').remove();
                        $('#dsd-cat-select').val('');
                    }
                }
            });
        });
    </script>
<?php
}

// Lưu dữ liệu meta box nhiều danh mục (dưới dạng json array)
function dsd_save_service_categories_meta_box_data($post_id)
{
    if (!isset($_POST['dsd_service_categories_nonce']) || !wp_verify_nonce($_POST['dsd_service_categories_nonce'], 'dsd_save_service_categories_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['dsd_service_category_ids'])) {
        $cats_json = wp_unslash($_POST['dsd_service_category_ids']);
        $cats_array = json_decode($cats_json, true);
        if (is_array($cats_array)) {
            // Làm sạch id
            $cats_array = array_map('intval', $cats_array);
            update_post_meta($post_id, '_dsd_service_category_ids', $cats_array);
        } else {
            delete_post_meta($post_id, '_dsd_service_category_ids');
        }
    }
}
add_action('save_post_page', 'dsd_save_service_categories_meta_box_data');

// Lấy bài viết dịch vụ theo nhiều category của trang chủ hiện tại (theo thứ tự)
function dsd_get_services_posts()
{
    $frontpage_id = get_option('page_on_front');
    if (!$frontpage_id) return false;

    $cat_ids = get_post_meta($frontpage_id, '_dsd_service_category_ids', true);
    if (!is_array($cat_ids) || empty($cat_ids)) return false;

    // Với Polylang, lấy term id tương ứng từng ngôn ngữ
    if (function_exists('pll_get_term')) {
        $lang = pll_current_language();
        foreach ($cat_ids as &$cat_id) {
            $translated = pll_get_term($cat_id, $lang);
            if ($translated) $cat_id = $translated;
        }
        unset($cat_id);
    }

    // Lấy post theo từng category, gom lại theo thứ tự cat_ids
    $posts_by_cat = [];
    foreach ($cat_ids as $cat_id) {
        $args = [
            'cat' => $cat_id,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];
        $q = new WP_Query($args);
        if ($q->have_posts()) {
            $posts_by_cat[$cat_id] = $q->posts;
        }
        wp_reset_postdata();
    }

    return $posts_by_cat;
}

// Hàm render dịch vụ frontend
function dsd_render_services_on_frontpage()
{
    if (!is_front_page()) return;

    $posts_by_cat = dsd_get_services_posts();
    if (!$posts_by_cat) return;

    foreach ($posts_by_cat as $cat_id => $posts) {
        $cat = get_category($cat_id);
        if (!$cat) continue;

        echo '<section class="section section-lg bg-default" id="' . esc_attr($cat->slug) . '">';
        echo '<div class="container">';

        echo '<div class="row justify-content-center text-center">';
        echo '<div class="col-md-9 col-lg-7 wow-outer">';
        echo '<div class="wow slideInDown">';
        echo '<h3>' . esc_html($cat->name) . '</h3>';
        if ($cat->description) {
            echo '<p class="text-opacity-80">' . esc_html($cat->description) . '</p>';
        }
        echo '</div></div></div>';

        $total = count($posts);
        $cols = ($total % 3 === 0) ? 3 : 2;

        echo '<div class="row row-10 row-lg-30">';
        foreach ($posts as $post) {
            setup_postdata($post);
            echo '<div class="col-md-' . (12 / $cols) . ' col-lg-' . (12 / $cols) . ' wow-outer">';
            echo '<div class="wow fadeInUp">';
            echo '<div class="team-minimal-figure">';
            if (has_post_thumbnail($post)) {
                echo get_the_post_thumbnail($post, 'medium', ['class' => 'img-fluid']);
            } else {
                echo '<img src="' . esc_url(plugins_url('default.jpg', __FILE__)) . '" alt="">';
            }
            echo '</div>';
            echo '<div class="team-minimal-caption">';
            echo '<a class="team-name" href="' . get_permalink($post) . '">' . get_the_title($post) . '</a>';
            echo '</div></div></div>';
        }
        wp_reset_postdata();
        echo '</div>'; // .row
        echo '</div></section>';
    }
}
