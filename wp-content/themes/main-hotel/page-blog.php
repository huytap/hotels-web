<?php
/* Template Name: Blog Page */
get_header();
?>
<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 container mx-auto px-4 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Nội dung bài viết chính -->
            <main class="lg:col-span-8">
                <h1 class="text-3xl font-bold mb-6">Blog thiết kế website khách sạn</h1>

                <?php
                // Query bài viết
                $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                $args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 6,
                    'paged' => $paged,
                );

                $blog_query = new WP_Query($args);

                if ($blog_query->have_posts()) : ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <img src="<?php the_post_thumbnail_url('medium') ?>" alt="<?php the_title(); ?>" class="w-full h-48 object-cover">
                                    </a>
                                <?php endif; ?>

                                <div class="p-4">
                                    <h2 class="text-xl font-semibold mb-2">
                                        <a href="<?php the_permalink(); ?>" class="hover:text-blue-600"><?php the_title(); ?></a>
                                    </h2>
                                    <p class="text-gray-600 text-sm mb-4"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="text-blue-500 hover:underline font-medium">Xem chi tiết</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        <?php
                        echo paginate_links(array(
                            'total' => $blog_query->max_num_pages,
                            'prev_text' => '« Trước',
                            'next_text' => 'Tiếp »',
                            'type' => 'list',
                        ));
                        ?>
                    </div>
                <?php else : ?>
                    <p class="text-gray-500">Chưa có bài viết nào.</p>
                <?php endif; ?>

                <?php wp_reset_postdata(); ?>
            </main>
        </div>
    </div>
</div>
<?php get_footer(); ?>