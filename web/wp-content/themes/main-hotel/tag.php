<?php

/**
 * Template for displaying Tag archive pages
 * File: tag.php
 */

get_header(); ?>

<main class="container mx-auto px-4 py-8">
    <!-- Tiêu đề Tag -->
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold mb-2">
            <?php single_tag_title(); ?>
        </h1>
        <?php if (tag_description()) : ?>
            <p class="text-gray-600"><?php echo tag_description(); ?></p>
        <?php endif; ?>
    </header>

    <!-- Danh sách bài viết -->
    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while (have_posts()) : the_post(); ?>
                <article class="bg-white rounded-2xl shadow hover:shadow-lg transition p-4">
                    <a href="<?php the_permalink(); ?>" class="block">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="mb-4 rounded-xl overflow-hidden">
                                <?php the_post_thumbnail('medium', ['class' => 'w-full h-auto']); ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="text-xl font-semibold mb-2"><?php the_title(); ?></h2>
                    </a>
                    <p class="text-gray-700 text-sm mb-4">
                        <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                    </p>
                    <a href="<?php the_permalink(); ?>" class="text-primary font-medium hover:underline">
                        Đọc thêm →
                    </a>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            <?php the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => __('« Trước'),
                'next_text' => __('Tiếp »'),
            ]); ?>
        </div>

    <?php else : ?>
        <p class="text-center text-gray-600">
            Hiện chưa có bài viết nào cho tag này.
        </p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>