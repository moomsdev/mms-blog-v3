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
<main class="single-content">
	<div class="container">
		<?php
		// theBreadcrumb();
		?>

		<div class="row">
			<div class="col-12 col-md-9 main-content">
				<figure><img src="<?= thePostThumbnailUrl(); ?>"
						alt="<?= the_title(); ?>"></figure>
				<h1 class="title-single"><?= get_the_title() ?></h1>
				

				<div id="article-content">
					<?php theContent(); ?>
				</div>
				<?php theShareSocials(); ?>
			</div>

			<aside class="col-3 sidebar d-none d-md-block">
				<div class="inner">
					<div class="latest-post">
						<div class="title-sidebar">
							<h2 class="title">Bài viết mới nhất</h2>
						</div>
						<div class="post-list">
							<?php
							$cpt = get_post_type();
							$args = array(
								'post_type' => $cpt,
								'posts_per_page' => 6,
								'orderby' => 'date',
								'order' => 'DESC',
								'post__not_in' => array(get_the_ID())
							);
							$query = new WP_Query($args);
							if ($query->have_posts()) {
								while ($query->have_posts()) {
									$query->the_post();
							?>
									<div class="post-item">
										<a href="<?= get_the_permalink() ?>">
											<div class="inner">
												<figure
													class="responsive-media">
													<img src="<?= getPostThumbnailUrl(get_the_ID()) ?>"
														alt="<?= get_the_title() ?>">
												</figure>
												<h4 class="post-title">
													<?= get_the_title() ?>
												</h4>
											</div>
										</a>
									</div>
							<?php
								}
							}
							?>
						</div>
					</div>
					
				</div>

			</aside>
		</div>
		<!-- <section class="related-post">
			<h2 class="title py-5">Bài viết liên quan</h2>
			<div class="row gy-5">
				<?php
				// $post_query = getRelatePostsbyPT(get_the_ID(), 6);
				// if ($post_query->have_posts()) :
				// 	while ($post_query->have_posts()) :
				// 		$post_query->the_post();
				// 		get_template_part('template-parts/loop', 'post');
				// 	endwhile;
				// endif;
				?>
			</div>
		</section> -->
	</div>
</main>