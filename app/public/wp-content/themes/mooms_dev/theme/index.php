<?php
/**
 * App Layout: layouts/app.php
 *
 * The main template file.
 *
 * This is the template that is used for displaying:
 * - posts
 * - single posts
 * - archive pages
 * - search results pages
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPEmergeTheme
 */

$query = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => '12',
]);

if($query->have_posts()) :
    while($query->have_posts()) : $query->the_post();
        get_template_part('loop_templates/loop_post');
    endwhile;
    wp_reset_postdata();
    wp_reset_query();
endif;