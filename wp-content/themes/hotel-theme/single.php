<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        ?>
        <section class="section section-lg section-main-bunner section-main-bunner-filter banner">
            <div class="main-bunner-img"
                style="background-image: url('<?php echo esc_url($thumbnail_url ?: get_template_directory_uri() . '/images/default-banner.jpg'); ?>');
                    background-size: cover; background-position: center; background-repeat: no-repeat;">
            </div>
            <div class="main-bunner-inner">
                <div class="container">
                    <div class="row row-50 justify-content-lg-center align-items-lg-center">
                        <div class="col-lg-12">
                            <div class="bunner-content-modern text-center text-white">
                                <h1 class="display-4"><?php the_title(); ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="container my-5">
            <div class="row">
                <!-- Nội dung chính -->
                <div class="col-md-8">
                    <article id="post-<?php the_ID(); ?>" <?php post_class('mb-5'); ?>>
                        <div class="post-content mb-4">
                            <?php the_content(); ?>
                        </div>

                        <!-- Hiển thị gallery nếu có -->
                        <?php if (function_exists('pgb_display_gallery_slider')): ?>
                            <div class="mb-5">
                                <?php echo pgb_display_gallery_slider(get_the_ID()); ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <aside>
                        <?php
                        $related_posts = get_posts(array(
                            'category__in'   => wp_get_post_categories(get_the_ID()),
                            'post__not_in'   => array(get_the_ID()),
                            'posts_per_page' => 5,
                        ));
                        if ($related_posts): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?php _e('Bài viết liên quan', 'hotel'); ?></h5>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($related_posts as $post): setup_postdata($post); ?>
                                            <li class="mb-2">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </li>
                                        <?php endforeach;
                                        wp_reset_postdata(); ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Tag bài viết -->
                        <?php
                        $post_tags = get_the_tags();
                        if ($post_tags): ?>
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?php _e('Tags', 'your-textdomain'); ?></h5>
                                    <div class="d-flex flex-wrap">
                                        <?php foreach ($post_tags as $tag): ?>
                                            <a href="<?php echo get_tag_link($tag->term_id); ?>" class="badge badge-primary mr-2 mb-2">
                                                <?php echo esc_html($tag->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </main>

<?php endwhile;
endif; ?>

<?php get_footer(); ?>