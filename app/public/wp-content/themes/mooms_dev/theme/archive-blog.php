<?php
/**
 * App Layout: layouts/app.php
 *
 * This is the template that is used for displaying all posts by default.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPEmergeTheme
 */
?>
<?= get_template_part('template-parts/breadcrumb') ?>

<div class="page-listing">
	<div class="container">
		<div class="row">
			<?php
			if (have_posts()) :
				while (have_posts()) : the_post();
					get_template_part('template-parts/loop','post');
				endwhile;
				wp_reset_postdata();
			endif;
			thePagination();
			?>
		</div>
	</div>
</div>
