<?php
$banner = getPostThumbnailUrl(get_the_ID());
$title = getPageTitle();
?>
<section class="breadcumb" style="background-image: url('<?php echo $banner; ?>');">
	<div class="container">
		<div class="main">
			<div class="bread">
				<!-- <div class="img-icon">
					<img src="<?php theAsset('img/breadcumb-icon.png'); ?>" alt="contact-icon" loading="lazy">
				</div> -->
				<h1 class="bread-title"><?= $title; ?></h1>
				<div class="bread-subtitle">
                    <?php
                    if ( function_exists('rank_math_the_breadcrumbs') ) :
                        rank_math_the_breadcrumbs();
                    endif;
                    ?>
				</div>
			</div>
		</div>
	</div>
</section>