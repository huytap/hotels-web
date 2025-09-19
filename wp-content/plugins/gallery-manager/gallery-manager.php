<?php

/**
 * Plugin Name: Simple Gallery Manager
 * Description: Quản lý 1 gallery đơn giản, upload nhiều ảnh, hiển thị dạng Isotope + LightGallery.
 * Version: 1.0
 * Author: Tap Nguyen
 * Text Domain: hotel
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;
add_action('plugins_loaded', function () {
    $current_lang = get_locale(); // lấy ngôn ngữ WordPress hiện tại: en_US, vi, ...
    load_textdomain('hotel', plugin_dir_path(__FILE__) . "languages/hotel-$current_lang.mo");
});
class GalleryManager
{

    private $option_key = 'simple_gallery_images';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_ajax_save_simple_gallery', [$this, 'save_gallery_ajax']);
        add_shortcode('simple_gallery', [$this, 'render_gallery_shortcode']);
    }

    // Đăng ký trang trong admin
    public function register_admin_page()
    {
        add_menu_page(
            'Gallery',
            'Gallery',
            'manage_options',
            'simple-gallery',
            [$this, 'gallery_admin_page'],
            'dashicons-format-gallery',
            4
        );
    }

    // Trang admin quản lý gallery
    public function gallery_admin_page()
    {
        $images = get_option($this->option_key, []); ?>
        <div class="wrap">
            <h1>Quản lý Gallery</h1>
            <div id="gallery-images-container">
                <ul id="gallery-images-list">
                    <?php foreach ($images as $image_id):
                        $thumb = wp_get_attachment_image_url($image_id, 'thumbnail'); ?>
                        <li class="gallery-image-item" data-id="<?php echo esc_attr($image_id); ?>">
                            <img src="<?php echo esc_url($thumb); ?>" style="max-width:80px;">
                            <span class="remove-image">&times;</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button class="button" id="add-gallery-images">Chọn ảnh</button>
            <button class="button button-primary" id="save-gallery-images">Lưu</button>
        </div>

        <style>
            #gallery-images-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 10px;
            }

            .gallery-image-item {
                position: relative;
                display: inline-block;
            }

            .gallery-image-item .remove-image {
                position: absolute;
                top: -8px;
                right: -8px;
                background: #fff;
                border-radius: 50%;
                font-weight: bold;
                padding: 0 5px;
                cursor: pointer;
                color: red;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                let frame;
                let ids = <?php echo json_encode($images); ?>;

                $('#add-gallery-images').on('click', function(e) {
                    e.preventDefault();
                    if (frame) {
                        frame.open();
                        return;
                    }
                    frame = wp.media({
                        title: 'Chọn ảnh cho gallery',
                        button: {
                            text: 'Thêm vào gallery'
                        },
                        multiple: true
                    });
                    frame.on('select', function() {
                        let selection = frame.state().get('selection');
                        selection.map(function(attachment) {
                            attachment = attachment.toJSON();
                            ids.push(attachment.id);
                            $('#gallery-images-list').append(
                                '<li class="gallery-image-item" data-id="' + attachment.id + '">' +
                                '<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:80px;">' +
                                '<span class="remove-image">&times;</span></li>'
                            );
                        });
                    });
                    frame.open();
                });

                // Kích hoạt sortable
                $('#gallery-images-list').sortable({
                    update: function(event, ui) {
                        // Cập nhật lại ids theo thứ tự mới
                        ids = $('#gallery-images-list .gallery-image-item').map(function() {
                            return $(this).data('id');
                        }).get();
                    }
                });

                $(document).on('click', '.remove-image', function() {
                    let id = $(this).parent().data('id');
                    $(this).parent().remove();
                    ids = ids.filter(item => item != id);
                });

                $('#save-gallery-images').on('click', function() {
                    $.post(ajaxurl, {
                        action: 'save_simple_gallery',
                        ids: ids
                    }, function(response) {
                        alert('Gallery đã được lưu!');
                    });
                });
            });
        </script>

    <?php
    }

    // Lưu dữ liệu qua AJAX
    public function save_gallery_ajax()
    {
        if (!current_user_can('manage_options')) wp_send_json_error('No permission');
        $ids = array_filter(array_map('intval', $_POST['ids']));
        update_option($this->option_key, $ids);
        wp_send_json_success('Saved');
    }

    public function admin_assets($hook)
    {
        if ($hook === 'toplevel_page_simple-gallery') {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable'); // thêm sortable
        }
    }

    // Shortcode hiển thị gallery
    public function render_gallery_shortcode()
    {
        $images = get_option($this->option_key, []);
        if (empty($images)) return '<p>Chưa có ảnh trong gallery.</p>';

        ob_start(); ?>
        <section class="section section-lg bg-gray-light" id="gallery">
            <h3 class="text-center">
                <?php
                _e('Hình ảnh', 'hotel'); ?>
            </h3>
            <div class="row isotope-wrap">
                <div class="col-lg-12">
                    <div class="isotope" data-isotope-layout="fitRows" data-isotope-group="gallery"
                        data-lightgallery="group" data-lg-thumbnail="false">
                        <div class="row no-gutters row-condensed">
                            <?php foreach ($images as $img_id):
                                $img_src = wp_get_attachment_image_url($img_id, 'medium');
                                $img_full = wp_get_attachment_image_url($img_id, 'full'); ?>
                                <div class="col-12 col-sm-12 col-md-4 isotope-item wow-outer">
                                    <div class="wow slideInDown">
                                        <div class="gallery-item-classic">
                                            <img src="<?php echo esc_url($img_src); ?>" alt="" width="640" height="429" />
                                            <div class="gallery-item-classic-caption">
                                                <a href="<?php echo esc_url($img_full); ?>" data-lightgallery="item">zoom</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<?php
        return ob_get_clean();
    }
}
new GalleryManager();
