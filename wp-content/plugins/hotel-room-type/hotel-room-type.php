<?php
/*
Plugin Name: Hotel Room Type Manager
Description: Quản lý loại phòng nghỉ với các trường bổ sung (loại giường, amenities, view, giá tiền, diện tích, tiện nghi chính, gallery, tiện nghi phòng tắm) với hỗ trợ dịch.
Version: 1.3
Author: Tap Nguyen
Text Domain: hotel
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

// Load text domain để Loco Translate nhận string
add_action('plugins_loaded', function () {
    $current_lang = get_locale(); // lấy ngôn ngữ WordPress hiện tại: en_US, vi, ...
    load_textdomain('hotel', plugin_dir_path(__FILE__) . "languages/hotel-$current_lang.mo");
});

// Đăng ký Custom Post Type 'room'
function hotel_register_room_cpt()
{
    $labels = array(
        'name'               => __('Phòng nghỉ', 'hotel'),
        'singular_name'      => __('Phòng nghỉ', 'hotel'),
        'menu_name'          => __('Phòng nghỉ', 'hotel'),
        'add_new'            => __('Thêm phòng mới', 'hotel'),
        'add_new_item'       => __('Thêm phòng mới', 'hotel'),
        'edit_item'          => __('Chỉnh sửa phòng', 'hotel'),
        'new_item'           => __('Phòng mới', 'hotel'),
        'view_item'          => __('Xem phòng', 'hotel'),
        'search_items'       => __('Tìm kiếm phòng', 'hotel'),
        'not_found'          => __('Không tìm thấy phòng', 'hotel'),
        'not_found_in_trash' => __('Không có phòng nào trong thùng rác', 'hotel'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_position'      => 3,
        'menu_icon'          => 'dashicons-building',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true,
    );

    register_post_type('room', $args);
}
add_action('init', 'hotel_register_room_cpt');


// Thêm meta box cho các trường bổ sung
function hotel_add_room_meta_boxes()
{
    add_meta_box(
        'hotel_room_details',
        __('Thông tin phòng nghỉ', 'hotel'),
        'hotel_room_meta_box_callback',
        'room',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'hotel_add_room_meta_boxes');

function hotel_room_meta_box_callback($post)
{
    // Thêm nonce để bảo mật
    wp_nonce_field('hotel_room_save_meta', 'hotel_room_meta_nonce');

    $bed_type = get_post_meta($post->ID, '_hotel_room_bed_type', true);
    $amenities = get_post_meta($post->ID, '_hotel_room_amenities', true);
    $bathroom_amenities = get_post_meta($post->ID, '_hotel_room_bathroom_amenities', true);
    $view = get_post_meta($post->ID, '_hotel_room_view', true);
    $price = get_post_meta($post->ID, '_hotel_room_price', true);
    $gallery = get_post_meta($post->ID, '_hotel_room_gallery', true);
    $area = get_post_meta($post->ID, '_hotel_room_area', true);
    $main_amenities = get_post_meta($post->ID, '_hotel_room_main_amenities', true);

?>
    <p>
        <label for="hotel_room_area"><strong>Diện tích (m²):</strong></label><br>
        <input type="text" name="hotel_room_area" id="hotel_room_area" value="<?php echo esc_attr($area); ?>" style="width:100%;" placeholder="VD: 30 m²" />
    </p>
    <p>
        <label for="hotel_room_main_amenities"><strong>Tiện nghi chính:</strong></label><br>
        <textarea name="hotel_room_main_amenities" id="hotel_room_main_amenities" rows="4" style="width:100%;"><?php echo esc_textarea($main_amenities); ?></textarea>
        <small>Nhập mỗi tiện nghi trên một dòng.</small>
    </p>
    <p>
        <label for="hotel_room_bed_type"><strong>Loại giường:</strong></label><br>
        <input type="text" name="hotel_room_bed_type" id="hotel_room_bed_type" value="<?php echo esc_attr($bed_type); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="hotel_room_amenities"><strong>Tiện nghi phòng:</strong></label><br>
        <textarea name="hotel_room_amenities" id="hotel_room_amenities" rows="4" style="width:100%;"><?php echo esc_textarea($amenities); ?></textarea>
        <small>Nhập mỗi tiện nghi trên 1 dòng.</small>
    </p>
    <p>
        <label for="hotel_room_bathroom_amenities"><strong>Tiện nghi trong phòng tắm:</strong></label><br>
        <textarea name="hotel_room_bathroom_amenities" id="hotel_room_bathroom_amenities" rows="4" style="width:100%;"><?php echo esc_textarea($bathroom_amenities); ?></textarea>
        <small>Nhập mỗi tiện nghi trên một dòng.</small>
    </p>
    <p>
        <label for="hotel_room_view"><strong>View phòng:</strong></label><br>
        <input type="text" name="hotel_room_view" id="hotel_room_view" value="<?php echo esc_attr($view); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="hotel_room_price"><strong>Giá tiền:</strong></label><br>
        <input type="text" name="hotel_room_price" id="hotel_room_price" value="<?php echo esc_attr($price); ?>" style="width:100%;" placeholder="VD: Từ 1,000,000 VNĐ" />
    </p>
    <p>
        <label><strong>Hình ảnh gallery:</strong></label><br>
        <button type="button" class="button" id="hotel_room_gallery_button">Chọn hình ảnh</button>
        <input type="hidden" name="hotel_room_gallery" id="hotel_room_gallery" value="<?php echo esc_attr($gallery); ?>" />

    <ul id="hotel_room_gallery_preview" class="hotel-room-gallery-sortable" style="margin-top:10px; display:flex; flex-wrap:wrap; gap:10px; list-style:none; padding:0;">
        <?php
        if (!empty($gallery)) {
            $gallery_ids = explode(',', $gallery);
            foreach ($gallery_ids as $image_id) {
                if ($image = wp_get_attachment_image($image_id, 'thumbnail')) {
                    echo '<li class="hotel-room-gallery-item" data-id="' . esc_attr($image_id) . '" style="cursor:grab;">' .
                        $image .
                        '<span class="remove-image" style="display:block;text-align:center;color:red;cursor:pointer;">×</span>' .
                        '</li>';
                }
            }
        }
        ?>
    </ul>
    </p>

    <script>
        jQuery(document).ready(function($) {
            var frame;

            function refreshGalleryInput() {
                var ids = [];
                $('#hotel_room_gallery_preview .hotel-room-gallery-item').each(function() {
                    ids.push($(this).data('id'));
                });
                $('#hotel_room_gallery').val(ids.join(','));
            }

            $('#hotel_room_gallery_button').on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Chọn hoặc tải lên hình ảnh',
                    button: {
                        text: 'Chọn'
                    },
                    multiple: true
                });

                frame.on('select', function() {
                    var attachments = frame.state().get('selection').toArray();
                    attachments.forEach(function(attachment) {
                        var imgUrl = attachment.attributes.sizes.thumbnail.url;
                        $('#hotel_room_gallery_preview').append(
                            '<li class="hotel-room-gallery-item" data-id="' + attachment.id + '" style="cursor:grab;">' +
                            '<img src="' + imgUrl + '" />' +
                            '<span class="remove-image" style="display:block;text-align:center;color:red;cursor:pointer;">×</span>' +
                            '</li>'
                        );
                    });
                    refreshGalleryInput();
                });

                frame.open();
            });

            // Sortable (dùng jQuery UI Sortable)
            $('#hotel_room_gallery_preview').sortable({
                placeholder: 'sortable-placeholder',
                update: function() {
                    refreshGalleryInput();
                }
            });

            // Xoá ảnh
            $('#hotel_room_gallery_preview').on('click', '.remove-image', function() {
                $(this).closest('.hotel-room-gallery-item').remove();
                refreshGalleryInput();
            });
        });
    </script>

    <style>
        #hotel_room_gallery_preview li img {
            display: block;
            max-width: 80px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .sortable-placeholder {
            width: 80px;
            height: 80px;
            border: 2px dashed #aaa;
            background: #f9f9f9;
        }
    </style>
    <?php
}

// Lưu dữ liệu meta box
function hotel_save_room_meta_boxes($post_id)
{
    // Kiểm tra nonce
    if (!isset($_POST['hotel_room_meta_nonce']) || !wp_verify_nonce($_POST['hotel_room_meta_nonce'], 'hotel_room_save_meta')) {
        return;
    }

    // Ngăn autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Kiểm tra quyền
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['hotel_room_bed_type'])) {
        update_post_meta($post_id, '_hotel_room_bed_type', sanitize_text_field($_POST['hotel_room_bed_type']));
    }

    if (isset($_POST['hotel_room_amenities'])) {
        update_post_meta($post_id, '_hotel_room_amenities', sanitize_textarea_field($_POST['hotel_room_amenities']));
    }

    if (isset($_POST['hotel_room_bathroom_amenities'])) {
        update_post_meta($post_id, '_hotel_room_bathroom_amenities', sanitize_textarea_field($_POST['hotel_room_bathroom_amenities']));
    }

    if (isset($_POST['hotel_room_view'])) {
        update_post_meta($post_id, '_hotel_room_view', sanitize_text_field($_POST['hotel_room_view']));
    }

    if (isset($_POST['hotel_room_price'])) {
        update_post_meta($post_id, '_hotel_room_price', sanitize_text_field($_POST['hotel_room_price']));
    }

    if (isset($_POST['hotel_room_gallery'])) {
        // Lưu chuỗi ID ảnh, kiểu "12,15,23"
        update_post_meta($post_id, '_hotel_room_gallery', sanitize_text_field($_POST['hotel_room_gallery']));
    }

    if (isset($_POST['hotel_room_area'])) {
        update_post_meta($post_id, '_hotel_room_area', sanitize_text_field($_POST['hotel_room_area']));
    }

    if (isset($_POST['hotel_room_main_amenities'])) {
        update_post_meta($post_id, '_hotel_room_main_amenities', sanitize_textarea_field($_POST['hotel_room_main_amenities']));
    }
}
add_action('save_post', 'hotel_save_room_meta_boxes');

function hotel_load_room_detail()
{
    if (empty($_GET['room_id'])) {
        wp_send_json_error(['message' => 'Thiếu room_id']);
    }

    $room_id = intval($_GET['room_id']);
    if (!$room_id || get_post_type($room_id) !== 'room') {
        wp_send_json_error(['message' => 'Phòng không tồn tại']);
    }

    // Lấy HTML
    if (!function_exists('hotel_get_room_details_html')) {
        wp_send_json_error(['message' => 'Chưa có hàm hotel_get_room_details_html']);
    }

    $html = hotel_get_room_details_html($room_id);
    wp_send_json_success(['html' => $html]);
}
// Nạp JS để xử lý nút "Xem thêm"
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), null, true);
    wp_enqueue_script(
        'hotel-room-ajax',
        plugin_dir_url(__FILE__) . 'js/hotel-room-ajax.js',
        array('jquery'),
        '1.0',
        true
    );
    wp_localize_script('hotel-room-ajax', 'hotelRoomAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'current_lang' => function_exists('pll_current_language') ? pll_current_language() : 'vi', // hoặc 'en'
    ]);
});
if (!function_exists('hotel_get_room_details_html')) {
    function hotel_get_room_details_html($post_id)
    {
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

        ob_start();
    ?>
        <div class="modal-header">
            <h5 class="modal-title"><?php echo esc_html($title); ?></h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <?php if (!empty($gallery_ids)): ?>
                        <div id="carouselRoom<?php echo esc_attr($post_id); ?>" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <?php foreach ($gallery_ids as $i => $img_id): ?>
                                    <li data-target="#carouselRoom<?php echo esc_attr($post_id); ?>" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>"></li>
                                <?php endforeach; ?>
                            </ol>
                            <div class="carousel-inner">
                                <?php foreach ($gallery_ids as $i => $img_id):
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
                    <?php else: ?>
                        <p><?php _e('Không có hình ảnh.', 'hotel'); ?></p>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <h5><?php echo esc_html($title); ?></h5>
                    <p>
                        <?php echo $area; ?> /
                        <?php if ($main_amenities): ?>
                            <?php echo nl2br(esc_html($main_amenities)); ?> <br>
                        <?php endif; ?>
                        <?php if ($bed_type): ?>
                            <?php echo $bed_type; ?> <br>
                        <?php endif; ?>
                        <?php _e('Hướng', 'hotel'); ?>: <?php echo $view; ?> <br>
                        <strong><?php _e('Giá', 'hotel'); ?>: <?php echo $price; ?></strong>
                    </p>
                </div>
            </div>
            <div class="room-full-description mt-3">
                <?php echo $content; ?>
            </div>
            <?php if ($amenities || $bathroom_amenities): ?>
                <div class="row mt-3">
                    <?php if ($bathroom_amenities): ?>
                        <div class="col-6">
                            <p><strong><?php _e('Trong phòng tắm riêng của bạn', 'hotel'); ?>:</strong></p>
                            <ul>
                                <?php foreach (explode("\n", $bathroom_amenities) as $item): ?>
                                    <li><?php echo esc_html(trim($item)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ($amenities): ?>
                        <div class="col-6">
                            <p><strong><?php _e('Tiện nghi phòng', 'hotel'); ?>:</strong></p>
                            <ul>
                                <?php foreach (explode("\n", $amenities) as $item): ?>
                                    <li><?php echo esc_html(trim($item)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php
        return ob_get_clean();
    }
    add_action('wp_loaded', function () {
        add_action('wp_ajax_load_room_detail', 'hotel_load_room_detail');
        add_action('wp_ajax_nopriv_load_room_detail', 'hotel_load_room_detail');
    });
}

// Hàm hiển thị danh sách phòng (chỉnh lại nút "Xem thêm")
function hotel_show_room_info()
{
    ?>
    <section class="section section-lg bg-gray-light" id="rooms">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-md-9 col-lg-7 wow-outer">
                    <div class="wow slideInDown">
                        <h3><?php _e('Phòng nghỉ', 'hotel'); ?></h3>
                        <p class="text-opacity-80">
                            <?php echo pll__(get_theme_mod('hotel_room_intro_text')); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
            $args = [
                'post_type'      => 'room',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ];
            $room_query = new WP_Query($args);

            if ($room_query->have_posts()) :
                $posts = $room_query->posts;
                $total = count($posts);

                if ($total === 0) {
                    echo '<p>' . esc_html__('Không có phòng nào', 'hotel') . '</p>';
                } else {
                    if ($total === 4) {
                        $row_sizes = [2, 2];
                    } else {
                        $row_sizes = [];
                        $full_rows = floor($total / 3);
                        for ($i = 0; $i < $full_rows; $i++) {
                            $row_sizes[] = 3;
                        }
                        $rem = $total % 3;
                        if ($rem) $row_sizes[] = $rem;
                    }

                    $idx = 0;
                    foreach ($row_sizes as $row_size) {
                        echo '<div class="row">';
                        for ($c = 0; $c < $row_size; $c++) {
                            if (!isset($posts[$idx])) break;

                            $room_id  = $posts[$idx]->ID;
                            $thumb_url = get_the_post_thumbnail_url($room_id, 'medium');
                            $price = get_post_meta($room_id, '_hotel_room_price', true);

                            if ($row_size === 3) {
                                $col_md = 'col-md-6';
                                $col_lg = 'col-lg-4';
                            } elseif ($row_size === 2) {
                                $col_md = 'col-md-6';
                                $col_lg = 'col-lg-6';
                            } else {
                                $col_md = 'col-md-12';
                                $col_lg = 'col-lg-12';
                            }
            ?>
                            <div class="col-12 <?php echo esc_attr("$col_md $col_lg"); ?> d-flex align-items-stretch mb-4">
                                <div class="wow fadeInUp w-100">
                                    <div class="room-item h-100 d-flex flex-column">
                                        <div class="team-minimal-figure">
                                            <?php if ($thumb_url): ?>
                                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title($room_id)); ?>" class="img-fluid w-100" />
                                            <?php else: ?>
                                                <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'placeholder.jpg'); ?>" alt="<?php echo esc_attr__('No image', 'hotel'); ?>" class="img-fluid w-100" />
                                            <?php endif; ?>

                                            <button class="button button-primary btn-view-room" data-room-id="<?php echo esc_attr($room_id); ?>">
                                                <?php _e('Xem thêm', 'hotel'); ?>
                                            </button>
                                        </div>

                                        <div class="team-minimal-caption mt-auto">
                                            <a class="team-name" href="<?php echo esc_url(get_permalink($room_id)); ?>"><?php echo esc_html(get_the_title($room_id)); ?></a>
                                            <p><?php echo esc_html($price ?: _e('Liên hệ', 'hotel')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
            <?php
                            $idx++;
                        }
                        echo '</div>';
                    }
                    wp_reset_postdata();
                }
            else :
                echo '<p>' . esc_html__('Hiện chưa có phòng nghỉ nào.', 'hotel') . '</p>';
            endif;
            ?>
        </div>
        <!-- Modal chung -->
        <div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-4 text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Đang tải...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
}
