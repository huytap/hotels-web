<?php
get_header();
?>

<div class="container mx-auto px-4 py-8">
    <div class="relative z-10 container mx-auto px-4 py-20">
        <div class="grid grid-cols-12 gap-12 mb-16">
            <!-- Nội dung bài viết chính -->
            <main class="col-span-12 lg:col-span-8 p-4">
                <?php
                if (have_posts()) :
                    while (have_posts()) : the_post(); ?>
                        <article <?php post_class("prose max-w-none"); ?>>
                            <h1 class="text-3xl font-bold mb-4"><?php the_title(); ?></h1>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="mb-6">
                                    <?php the_post_thumbnail('large', ['class' => 'w-full h-auto rounded']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="content mb-8">
                                <?php the_content(); ?>
                            </div>
                        </article>
                <?php
                    endwhile;
                endif;
                ?>
            </main>

            <!-- Sidebar -->
            <aside class="col-span-12 lg:col-span-4 p-4">
                <!-- Bài viết liên quan -->
                <div class="related-posts">
                    <h2 class="text-xl font-semibold mb-4">Bài viết liên quan</h2>
                    <ul class="space-y-3">
                        <?php
                        $related_args = array(
                            'post_type' => 'post',
                            'posts_per_page' => 5,
                            'post__not_in' => array(get_the_ID()),
                            'orderby' => 'rand',
                        );
                        $related = new WP_Query($related_args);
                        if ($related->have_posts()) :
                            while ($related->have_posts()) : $related->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>" class="text-blue-600 hover:underline"><?php the_title(); ?></a>
                                </li>
                        <?php
                            endwhile;
                        endif;
                        wp_reset_postdata();
                        ?>
                    </ul>
                </div>

                <!-- Tags -->
                <div class="post-tags">
                    <h2 class="text-xl font-semibold mb-4">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $post_tags = get_the_tags();
                        if ($post_tags) :
                            foreach ($post_tags as $tag) : ?>
                                <a href="<?php echo get_tag_link($tag->term_id); ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-sm"><?php echo $tag->name; ?></a>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
<?php
get_footer();
?>