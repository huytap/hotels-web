<?php
/**
 * Main Template File - Dynamic Content Handler
 */

get_header(); ?>

<div class="main-content">
    <div class="container">
        
        <?php if (have_posts()): ?>
            
            <!-- Page Header -->
            <div class="page-header">
                <?php if (is_home() && !is_front_page()): ?>
                    <h1 class="page-title"><?php _e('Latest News', 'hotel-theme'); ?></h1>
                <?php elseif (is_archive()): ?>
                    <h1 class="page-title"><?php the_archive_title(); ?></h1>
                    <?php if (get_the_archive_description()): ?>
                        <div class="archive-description"><?php the_archive_description(); ?></div>
                    <?php endif; ?>
                <?php elseif (is_search()): ?>
                    <h1 class="page-title"><?php printf(__('Search Results for: %s', 'hotel-theme'), get_search_query()); ?></h1>
                <?php endif; ?>
            </div>
            
            <!-- Content Loop -->
            <div class="posts-container">
                <?php while (have_posts()): the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        
                        <?php if (has_post_thumbnail() && !is_single()): ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium_large'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <header class="post-header">
                                <?php if (!is_single()): ?>
                                    <h2 class="post-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                <?php else: ?>
                                    <h1 class="post-title"><?php the_title(); ?></h1>
                                <?php endif; ?>
                                
                                <div class="post-meta">
                                    <span class="post-date"><?php echo get_the_date(); ?></span>
                                    <?php if (!is_page()): ?>
                                        <span class="post-author"><?php _e('by', 'hotel-theme'); ?> <?php the_author(); ?></span>
                                        <?php if (has_category()): ?>
                                            <span class="post-categories"><?php _e('in', 'hotel-theme'); ?> <?php the_category(', '); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </header>
                            
                            <div class="post-excerpt">
                                <?php
                                if (is_single() || is_page()) {
                                    the_content();
                                    
                                    wp_link_pages(array(
                                        'before' => '<div class="page-links">' . __('Pages:', 'hotel-theme'),
                                        'after'  => '</div>',
                                    ));
                                } else {
                                    the_excerpt();
                                    ?>
                                    <a href="<?php the_permalink(); ?>" class="read-more">
                                        <?php _e('Read More', 'hotel-theme'); ?> &rarr;
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                            
                            <?php if (is_single() && has_tag()): ?>
                                <div class="post-tags">
                                    <?php _e('Tags:', 'hotel-theme'); ?> <?php the_tags('', ', '); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </article>
                    
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-wrapper">
                <?php hotel_theme_pagination(); ?>
            </div>
            
        <?php else: ?>
            
            <!-- No Posts Found -->
            <div class="no-posts-found">
                <h1><?php _e('Nothing Found', 'hotel-theme'); ?></h1>
                <p>
                    <?php if (is_search()): ?>
                        <?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'hotel-theme'); ?>
                    <?php else: ?>
                        <?php _e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'hotel-theme'); ?>
                    <?php endif; ?>
                </p>
                
                <div class="search-form-container">
                    <?php get_search_form(); ?>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<?php 
// Include sidebar for certain page types
if (is_single() || is_archive() || is_search()) {
    get_sidebar();
}
?>

<?php get_footer(); ?>