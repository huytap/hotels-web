<?php
get_header();
?>
<section class="section section-lg section-main-bunner section-main-bunner-filter banner">
    <div class="main-bunner-img"
        style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/404.jpg'); ?>');
                    background-size: cover; background-position: center; background-repeat: no-repeat;">
    </div>
    <div class="main-bunner-inner">
        <div class="container">
            <div class="row row-50 justify-content-lg-center align-items-lg-center">
                <div class="col-lg-12">
                    <div class="bunner-content-modern text-center text-white">
                        <h1 class="display-4"><?php echo _e('404', 'hotel'); ?></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="container text-center" style="padding: 100px 0;">
    <h1 class="display-1 text-danger">404</h1>
    <h2 class="mb-4"><?php echo _e('Rất tiếc, trang bạn tìm kiếm không tồn tại!', 'hotel'); ?></h2>
    <p class="mb-4"><?php echo _e('Có thể trang đã bị xóa hoặc bạn nhập sai địa chỉ.', 'hotel'); ?></p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary btn-lg">
        <?php echo _e('Quay về trang chủ', 'hotel'); ?>
    </a>
</div>

<?php
get_footer(); // Gọi footer của theme
?>