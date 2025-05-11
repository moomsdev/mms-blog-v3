<?php
/**
 * Theme header partial.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WPEmergeTheme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_head(); ?>
	<script src="https://code.iconify.design/3/3.0.0/iconify.min.js"></script>
</head>

<body <?php body_class(); ?>>
	<?php
  app_shim_wp_body_open();

  if (is_home() || is_front_page()) :
    echo '<h1 class="site-name d-none">' . get_bloginfo('name') . '</h1>';
  endif;
  ?>

	<div class="wrapper_mms">
		<header id="header">
			<div class="container-fluid">
				<figure class="header-logo">
					<img src="<?php theOptionImage('logo'); ?>" alt="<?php bloginfo('name'); ?>" loading="lazy">
				</img>
			</div>
		</header>