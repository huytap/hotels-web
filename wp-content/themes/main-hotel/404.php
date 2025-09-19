<?php
get_header();
?>
<section class="relative w-full h-[60vh] flex items-center justify-center">
    <div class="absolute inset-0 bg-cover bg-center"
        style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/404.jpg'); ?>');">
    </div>
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative z-10 text-center text-white">
        <h1 class="text-6xl font-bold"><?php _e('404', 'hotel'); ?></h1>
    </div>
</section>

<div class="container mx-auto text-center py-24">
    <h1 class="text-9xl font-extrabold text-red-600 mb-4">404</h1>
    <h2 class="text-3xl font-semibold mb-4">
        <?php _e('Rất tiếc, trang bạn tìm kiếm không tồn tại!', 'hotel'); ?>
    </h2>
    <p class="text-lg text-gray-600 mb-8">
        <?php _e('Có thể trang đã bị xóa hoặc bạn nhập sai địa chỉ.', 'hotel'); ?>
    </p>
    <a href="<?php echo esc_url(home_url('/')); ?>"
        class="inline-block px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-xl shadow hover:bg-blue-700 transition">
        <?php _e('Quay về trang chủ', 'hotel'); ?>
    </a>
</div>

<?php
get_footer();
?>