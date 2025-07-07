<?php
/**
 * Asset helpers.
 *
 * @package WPEmergeTheme
 */

use WPEmergeTheme\Facades\Theme;
use WPEmergeTheme\Facades\Assets;

/**
 * Enhanced asset loading with performance optimizations
 */
function app_action_theme_enqueue_assets()
{
    $template_dir = Theme::uri();
    $version = wp_get_theme()->get('Version');

    /**
     * Enqueue the built-in comment-reply script for singular pages.
     */
    if (is_singular()) {
        wp_enqueue_script('comment-reply');
    }

    /**
     * Critical JS (inline or very small) - load in head for critical functionality
     */
    if (file_exists(get_template_directory() . '/dist/critical.js')) {
        wp_enqueue_script('theme-critical-js', $template_dir . '/dist/critical.js', [], $version, false);
    }

    /**
     * Main JavaScript bundle (deferred)
     */
    Assets::enqueueScript('theme-js-bundle', $template_dir . '/dist/theme.js', ['jquery'], true);

    /**
     * Conditional assets based on page type
     */
    if (is_home() || is_archive() || is_search()) {
        if (file_exists(get_template_directory() . '/dist/archive.js')) {
            wp_enqueue_script('theme-archive-js', $template_dir . '/dist/archive.js', ['theme-js-bundle'], $version, true);
        }
    }

    if (is_single() && comments_open()) {
        if (file_exists(get_template_directory() . '/dist/comments.js')) {
            wp_enqueue_script('theme-comments-js', $template_dir . '/dist/comments.js', ['theme-js-bundle'], $version, true);
        }
    }

    /**
     * Enqueue styles with preload optimization
     */
    Assets::enqueueStyle('theme-css-bundle', $template_dir . '/dist/styles/theme.css');

    /**
     * Conditional CSS based on page type
     */
    if (is_single()) {
        if (file_exists(get_template_directory() . '/dist/styles/single.css')) {
            wp_enqueue_style('theme-single-css', $template_dir . '/dist/styles/single.css', ['theme-css-bundle'], $version);
        }
    }

    /**
     * Enqueue theme's style.css file to allow overrides for the bundled styles.
     */
    Assets::enqueueStyle('theme-styles', get_template_directory_uri() . '/style.css');

    /**
     * Localize script with minimal data
     */
    wp_localize_script('theme-js-bundle', 'themeData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('theme_nonce'),
        'isHome' => is_home(),
        'isMobile' => wp_is_mobile(),
        'currentUrl' => get_permalink(),
    ]);
}

/**
 * Enqueue admin assets.
 *
 * @return void
 */
function app_action_admin_enqueue_assets()
{
    $template_dir = Theme::uri();

    /**
     * Enqueue styles.
     */
    Assets::enqueueStyle(
        'theme-admin-css-bundle',
        $template_dir . '/dist/styles/admin.css'
    );

    // Enqueue front-end styles in admin area
    //  Assets::enqueueStyle('theme-css-bundle', $template_dir . '/dist/styles/theme.css');
}

/**
 * Enqueue login assets.
 *
 * @return void
 */
function app_action_login_enqueue_assets()
{
    $template_dir = Theme::uri();

    /**
     * Enqueue scripts.
     */
    Assets::enqueueScript(
        'theme-login-js-bundle',
        $template_dir . '/dist/login.js',
        ['jquery'],
        true
    );

    /**
     * Enqueue styles.
     */
    Assets::enqueueStyle(
        'theme-login-css-bundle',
        $template_dir . '/dist/styles/login.css'
    );
}

/**
 * Enqueue editor assets.
 *
 * @return void
 */
function app_action_editor_enqueue_assets()
{
    $template_dir = Theme::uri();

    /**
     * Enqueue scripts.
     */
    Assets::enqueueScript(
        'theme-editor-js-bundle',
        $template_dir . '/dist/editor.js',
        ['jquery'],
        true
    );

    /**
     * Enqueue styles.
     */
    Assets::enqueueStyle(
        'theme-editor-css-bundle',
        $template_dir . '/dist/styles/editor.css'
    );
}

/**
 * Add favicon proxy.
 *
 * @return void
 * @link WPEmergeTheme\Assets\Assets::addFavicon()
 */
function app_action_add_favicon()
{
    Assets::addFavicon();
}

/**
 * Advanced script optimization with defer/async/preload
 */
add_filter('script_loader_tag', function ($tag, $handle, $src) {
    // Scripts to defer (non-critical)
    $defer_scripts = [
        'theme-js-bundle',
        'theme-admin-js-bundle',
        'theme-login-js-bundle',
        'theme-editor-js-bundle',
        'theme-archive-js',
        'theme-comments-js'
    ];

    // Scripts to async (tracking, analytics)
    $async_scripts = [
        'google-analytics',
        'facebook-pixel',
        'hotjar'
    ];

    // Scripts to preload (critical)
    $preload_scripts = [
        'theme-critical-js'
    ];

    if (in_array($handle, $defer_scripts)) {
        return str_replace('<script ', '<script defer ', $tag);
    }

    if (in_array($handle, $async_scripts)) {
        return str_replace('<script ', '<script async ', $tag);
    }

    if (in_array($handle, $preload_scripts)) {
        // Add preload hint for critical scripts
        echo '<link rel="preload" href="' . $src . '" as="script">';
    }

    return $tag;
}, 10, 3);

/**
 * Advanced style optimization with preload
 */
add_filter('style_loader_tag', function ($tag, $handle, $href) {
    // Non-critical styles to load asynchronously
    $non_critical_styles = [
        'theme-single-css',
        'fontawesome',
        'google-fonts'
    ];

    // Critical styles to preload
    $critical_styles = [
        'theme-css-bundle'
    ];

    if (in_array($handle, $non_critical_styles)) {
        // Load non-critical CSS asynchronously
        return '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" id="' . $handle . '">' .
            '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
    }

    if (in_array($handle, $critical_styles)) {
        // Add preload for critical CSS
        echo '<link rel="preload" href="' . $href . '" as="style">';
    }

    return $tag;
}, 10, 3);

/**
 * Enhanced resource hints for performance
 */
add_filter('wp_resource_hints', function ($hints, $relation_type) {
    if ('preconnect' === $relation_type) {
        $hints[] = 'https://fonts.gstatic.com';
        $hints[] = 'https://ajax.googleapis.com';
    }

    if ('dns-prefetch' === $relation_type) {
        $hints[] = '//fonts.googleapis.com';
        $hints[] = '//cdnjs.cloudflare.com';
    }

    if ('prefetch' === $relation_type && (is_home() || is_front_page())) {
        // Prefetch likely next pages
        $hints[] = get_permalink(get_option('page_for_posts'));
    }

    return $hints;
}, 10, 2);

// Hook vào action để enqueue assets thông qua function có sẵn thay vì thêm action mới
add_action('wp_enqueue_scripts', 'app_action_theme_enqueue_assets');
