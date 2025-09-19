<?php
/*
Plugin Name: Post Gallery Bootstrap Slider
Description: Thêm gallery dạng slider dùng Bootstrap 4.1.3 Carousel cho bài viết, hỗ trợ kéo thả sắp xếp và xóa ảnh.
Version: 1.2
Author: Tap Nguyen
Text Domain: hotel
*/

// Enqueue media & jquery-ui-sortable trong admin chỉ khi chỉnh bài viết
function pgb_admin_enqueue_scripts($hook)
{
    if (! in_array($hook, array('post.php', 'post-new.php'))) return;

    $screen = get_current_screen();
    if (! $screen) return;

    // Chỉ load khi đang chỉnh post (post type 'post'). Nếu cần cho CPT khác, thêm vào mảng.
    if (in_array($screen->post_type, array('post'))) {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        // optional small style for admin
        wp_enqueue_style('pgb-admin-style', false);
        wp_add_inline_style('pgb-admin-style', '
            #pgb_gallery_preview li { position: relative; display:inline-block; margin:6px; border:1px solid #e1e1e1; padding:4px; background:#fff; }
            #pgb_gallery_preview li img { display:block; max-width:100px; height:auto; }
            #pgb_gallery_preview .pgb-remove-image { position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; border:none; width:22px; height:22px; line-height:20px; text-align:center; border-radius:50%; cursor:pointer; }
            .sortable-placeholder { width:100px; height:80px; border:2px dashed #bbb; display:inline-block; margin:6px; }
        ');
    }
}
add_action('admin_enqueue_scripts', 'pgb_admin_enqueue_scripts');

// 1. Thêm meta box chọn ảnh gallery vào post
function pgb_add_gallery_meta_box()
{
    add_meta_box(
        'pgb_gallery_meta_box',
        __('Gallery slider bài viết', 'post-gallery'),
        'pgb_gallery_meta_box_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'pgb_add_gallery_meta_box');

function pgb_gallery_meta_box_callback($post)
{
    wp_nonce_field('pgb_save_gallery', 'pgb_gallery_nonce');
    $gallery = get_post_meta($post->ID, '_pgb_gallery', true);
    $ids = $gallery ? array_filter(explode(',', $gallery)) : array();
?>
    <p>
        <button type="button" class="button" id="pgb_gallery_button"><?php echo esc_html__('Chọn hình ảnh', 'post-gallery'); ?></button>
        <input type="hidden" name="pgb_gallery" id="pgb_gallery" value="<?php echo esc_attr($gallery); ?>" />
    </p>

    <ul id="pgb_gallery_preview" style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; list-style:none; padding:0;">
        <?php
        foreach ($ids as $id) {
            $img = wp_get_attachment_image($id, 'thumbnail');
            if ($img) {
                echo '<li data-id="' . esc_attr($id) . '">' . $img . '<button type="button" class="pgb-remove-image" aria-label="' . esc_attr__('Xóa ảnh', 'post-gallery') . '">×</button></li>';
            }
        }
        ?>
    </ul>
    <style>
        #pgb_gallery_preview li {
            position: relative;
            max-width: 80px;
        }

        #pgb_gallery_preview li button {
            position: absolute;
            top: 0;
            right: 0;
            cursor: pointer;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            var frame;

            function updateHiddenField() {
                var ids = [];
                $('#pgb_gallery_preview li').each(function() {
                    ids.push($(this).data('id'));
                });
                $('#pgb_gallery').val(ids.join(','));
            }

            // Open media frame and append selected images (avoid duplicates)
            $('#pgb_gallery_button').on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '<?php echo esc_js(__('Chọn hình ảnh cho gallery slider', 'post-gallery')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Chọn', 'post-gallery')); ?>'
                    },
                    multiple: true
                });

                frame.on('select', function() {
                    var attachments = frame.state().get('selection').toArray();

                    attachments.forEach(function(attachment) {
                        var id = attachment.id.toString();

                        // nếu đã tồn tại thì bỏ qua
                        if ($('#pgb_gallery_preview li[data-id="' + id + '"]').length) {
                            return;
                        }

                        // lấy url thumbnail nếu có, ngược lại dùng url chính
                        var thumb = (attachment.attributes.sizes && attachment.attributes.sizes.thumbnail) ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.url;

                        $('#pgb_gallery_preview').append(
                            '<li data-id="' + id + '">' +
                            '<img src="' + thumb + '" alt="" />' +
                            '<button type="button" class="pgb-remove-image" aria-label="<?php echo esc_js(__('Xóa ảnh', 'post-gallery')); ?>">×</button>' +
                            '</li>'
                        );
                    });

                    updateHiddenField();
                });

                frame.open();
            });

            // Kích hoạt sortable
            if ($.fn.sortable) {
                $('#pgb_gallery_preview').sortable({
                    placeholder: 'sortable-placeholder',
                    update: function() {
                        updateHiddenField();
                    }
                });
            }

            // Xóa ảnh khi click nút xóa
            $('#pgb_gallery_preview').on('click', '.pgb-remove-image', function(e) {
                e.preventDefault();
                $(this).closest('li').remove();
                updateHiddenField();
            });
        });
    </script>
<?php
}

// 2. Lưu dữ liệu meta box
function pgb_save_gallery_meta_box($post_id)
{
    if (!isset($_POST['pgb_gallery_nonce']) || !wp_verify_nonce($_POST['pgb_gallery_nonce'], 'pgb_save_gallery')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['pgb_gallery'])) {
        // Chuẩn hóa: loại bỏ khoảng trắng, duplicate id rỗng
        $raw = sanitize_text_field($_POST['pgb_gallery']);
        $ids = array_filter(array_map('trim', explode(',', $raw)));
        // đảm bảo chỉ số nguyên
        $ids = array_map('intval', $ids);
        update_post_meta($post_id, '_pgb_gallery', implode(',', $ids));
    } else {
        // nếu không có trường (nghĩa là xóa hết) -> xóa meta
        delete_post_meta($post_id, '_pgb_gallery');
    }
}
add_action('save_post', 'pgb_save_gallery_meta_box');

// 3. Hiển thị gallery slider trong frontend
function pgb_display_gallery_slider($post_id)
{
    $gallery = get_post_meta($post_id, '_pgb_gallery', true);
    if (!$gallery) return '';
    $ids = array_filter(explode(',', $gallery));
    if (count($ids) == 0) return '';

    ob_start();
?>
    <div id="pgbCarousel-<?php echo esc_attr($post_id); ?>" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php foreach ($ids as $index => $id): ?>
                <li data-target="#pgbCarousel-<?php echo esc_attr($post_id); ?>" data-slide-to="<?php echo intval($index); ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></li>
            <?php endforeach; ?>
        </ol>
        <div class="carousel-inner">
            <?php foreach ($ids as $index => $id): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php echo wp_get_attachment_image($id, 'large', false, ['class' => 'd-block w-100']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#pgbCarousel-<?php echo esc_attr($post_id); ?>" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only"><?php echo esc_html__('Previous', 'post-gallery'); ?></span>
        </a>
        <a class="carousel-control-next" href="#pgbCarousel-<?php echo esc_attr($post_id); ?>" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only"><?php echo esc_html__('Next', 'post-gallery'); ?></span>
        </a>
    </div>
    <style>
        #pgbCarousel-<?php echo esc_attr($post_id); ?>.carousel-item img {
            max-height: 500px;
            object-fit: contain;
            margin: auto;
        }
    </style>
<?php
    return ob_get_clean();
}
