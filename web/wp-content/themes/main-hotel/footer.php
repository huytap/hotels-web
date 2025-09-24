</main>
<footer class="bg-primary text-primary-foreground">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8">
                <?php if (is_active_sidebar('footer-1')) dynamic_sidebar('footer-1'); ?>
                <?php if (is_active_sidebar('footer-2')) dynamic_sidebar('footer-2'); ?>
                <?php if (is_active_sidebar('footer-3')) dynamic_sidebar('footer-3'); ?>
                <?php if (is_active_sidebar('footer-4')) dynamic_sidebar('footer-4'); ?>
            </div>
            <div class="border-t border-primary-foreground/20 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-primary-foreground/60 text-sm">© <?php echo date('Y'); ?> HotelsWeb. Tất cả quyền được bảo lưu.</div>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'container' => false,
                        'menu_class' => 'flex space-x-6 text-sm',
                        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'fallback_cb' => false,
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</footer>
<button id="back-to-top" class="fixed bottom-8 right-8 p-3 bg-primary text-white rounded-full shadow-lg hidden hover:bg-primary-dark transition-colors">
    ↑
</button>
<?php wp_footer(); ?>
</body>

</html>