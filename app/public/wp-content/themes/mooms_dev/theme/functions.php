<?php
/**
 * MoomsDev WordPress Theme
 * 
 * Modern WordPress theme built with WP Emerge framework
 * 
 * @package MoomsDev
 * @version 1.0.0
 * @author La Cà Dev <support@mooms.dev>
 * @link https://mooms.dev
 */

use WPEmerge\Facades\WPEmerge;
use WPEmergeTheme\Facades\Theme;

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// LOAD THEME MODULES
// =============================================================================

// Load theme constants and directory setup
require_once __DIR__ . '/setup/constants.php';

// Load security configuration
require_once __DIR__ . '/setup/security.php';

// Load performance optimizations
require_once __DIR__ . '/setup/performance.php';

// DEPENDENCIES & AUTOLOADING
// =============================================================================

// Load composer dependencies
if (file_exists(APP_VENDOR_DIR . 'autoload.php')) {
    require_once APP_VENDOR_DIR . 'autoload.php';
    \Carbon_Fields\Carbon_Fields::boot();
}

// Enable Theme shortcut
WPEmerge::alias('Theme', \WPEmergeTheme\Facades\Theme::class);

// Load helpers
require_once APP_APP_DIR . 'helpers.php';

// Bootstrap Theme
Theme::bootstrap(require APP_APP_DIR . 'config.php');

// Register hooks
require_once APP_APP_DIR . 'hooks.php';

// =============================================================================
// THEME SETUP
// =============================================================================

add_action('after_setup_theme', function () {
    // Load textdomain
    load_theme_textdomain('mms', APP_DIR . 'languages');

    // Load theme components
    require_once APP_APP_SETUP_DIR . 'theme-support.php';
    require_once APP_APP_SETUP_DIR . 'menus.php';
    require_once APP_APP_SETUP_DIR . 'assets.php';
    require_once APP_APP_SETUP_DIR . 'ajax-search.php';

    // Load Gutenberg blocks
    $blocks_dir = APP_APP_SETUP_DIR . '/blocks';
    if (is_dir($blocks_dir)) {
        $block_files = glob($blocks_dir . '/*.php');
        foreach ($block_files as $block_file) {
            require_once $block_file;
        }
    }
});

// =============================================================================
// AUTOLOAD COMPONENTS
// =============================================================================

// Autoload components from specific directories
$autoload_folders = [
    APP_APP_SETUP_ECOMMERCE_DIR,
    APP_APP_SETUP_TAXONOMY_DIR,
    APP_APP_SETUP_WALKER_DIR,
];

foreach ($autoload_folders as $folder) {
    if (is_dir($folder)) {
        $files = glob($folder . '*.php');
        foreach ($files as $file) {
            if (is_file($file)) {
                require_once $file;
            }
        }
    }
}

// =============================================================================
// CUSTOM POST TYPES REGISTRATION
// =============================================================================

// Initialize custom post types
add_action('init', function() {
    // Blog post type
    if (class_exists('\App\PostTypes\blog')) {
        new \App\PostTypes\blog();
    }
});
