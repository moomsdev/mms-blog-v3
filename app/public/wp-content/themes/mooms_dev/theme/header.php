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
	<?php app_shim_wp_body_open(); ?>
	<div class="wrapper_mm">
		<header id="header">
			<div class="container">
				<div class="header-inner full-width">
					<div class="container">
						<div class="inner">
							<div class="logo-mb">
								<a href="<?php bloginfo('url') ?>"
									class="main-logo">
									<img src="<?php theOptionImage('logo'); ?>"
										alt="<?php bloginfo('url'); ?>">
								</a>
							</div>
							<div class="both-menu">
								<div class="main-menu">
									<nav
										class="pc-menu d-none d-md-block">
										<?php
										wp_nav_menu([
											'menu' => 'main-menu',
											'theme_location' => 'main-menu',
											'container' => 'ul',
											'menu_class' => 'nav-menu',
											'walker' => new Bootstrap_Menu_Walker(),
										])
										?>
									</nav>
									<div class="mb-menu d-flex d-md-none">
										<a class="__bar_menu"
											href="#mobile_menu">
											<button
												class="mburger mburger--collapse">
												<b></b>
												<b></b>
												<b></b>
											</button>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>