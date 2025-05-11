<?php
/*
Plugin Name: LinguaPress Pro
Plugin URI: https://festingervault.com/p/multilingual-pro/
Version: 3.6.7
Description: Translate your content effortlessly with LinguaPress Pro! Reach a global audience and boost your website's SEO. Simple, powerful, and developer-friendly.
Author: Festinger Vault
Author URI: https://festingervault.com
Text Domain: polylang-pro
Domain Path: /languages
Requires at least: 6.4
Requires PHP: 8.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/licenses.html

LinguaPress Pro available through Festinger Vault is an independent version maintained by our team. We are not affiliated, endorsed, or associated with Polylang Pro or WP SYNTEX in any way. Our support is exclusively for the forked version available in Festinger Vault. If you require official updates, premium features, or priority support from the original developers, we strongly recommend purchasing a valid license from them.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

define( 'POLYLANG_PRO', true );
define( 'POLYLANG_PRO_FILE', __FILE__ );
define( 'POLYLANG_PRO_DIR', __DIR__ );

if ( ! defined( 'POLYLANG_ROOT_FILE' ) ) {
	define( 'POLYLANG_ROOT_FILE', __FILE__ );
}

if ( defined( 'POLYLANG_BASENAME' ) ) {
	// The user is attempting to activate a second plugin instance, typically Polylang and Polylang Pro.
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	deactivate_plugins( POLYLANG_BASENAME, false, is_network_admin() ); // Deactivate the other plugin.

	// Add the deactivated plugin to the list of recent activated plugins.
	if ( ! is_network_admin() ) {
		update_option( 'recently_activated', array( POLYLANG_BASENAME => time() ) + (array) get_option( 'recently_activated' ) );
	} else {
		update_site_option( 'recently_activated', array( POLYLANG_BASENAME => time() ) + (array) get_site_option( 'recently_activated' ) );
	}
} else {
	define( 'POLYLANG_BASENAME', plugin_basename( __FILE__ ) ); // Plugin name as known by WP.
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/wpsyntex/polylang/polylang.php';

if ( empty( $_GET['deactivate-polylang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_action( 'pll_pre_init', array( new PLL_Pro(), 'init' ), 0 );
}
