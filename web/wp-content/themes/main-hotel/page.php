<?php
get_header(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 container mx-auto px-4 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Nội dung bài viết chính -->
            <main class="lg:col-span-8">
                <h1 class="text-3xl font-bold mb-6"><?php the_title(); ?></h1>
                <div class="content">
                    <?php
                    while (have_posts()) : the_post();
                        the_content();
                    endwhile;
                    ?>
            </main>
        </div>
    </div>
</div>
<?php get_footer(); ?>