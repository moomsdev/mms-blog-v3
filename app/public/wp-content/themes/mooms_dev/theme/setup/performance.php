<?php
/**
 * Advanced Performance Optimizations
 * 
 * Comprehensive performance enhancements for maximum PageSpeed Insights scores
 *
 * @package MoomsDev
 * @since 1.0.0
 */

class ThemePerformance
{

    /**
     * Initialize performance optimizations
     */
    public static function init()
    {
        // Core WordPress optimizations
        add_action('init', [self::class, 'remove_wordpress_bloat']);
        add_action('wp_enqueue_scripts', [self::class, 'optimize_wp_scripts']);

        // Advanced caching
        add_action('template_redirect', [self::class, 'set_cache_headers']);
        add_action('wp_head', [self::class, 'add_advanced_resource_hints'], 1);

        // Database optimizations
        add_action('init', [self::class, 'optimize_database_queries']);
        add_filter('posts_request', [self::class, 'optimize_sql_queries']);

        // Memory management
        add_action('wp_head', [self::class, 'optimize_memory_usage']);
        add_action('wp_footer', [self::class, 'cleanup_memory'], 999);

        // Image optimizations
        add_filter('wp_get_attachment_image_attributes', [self::class, 'optimize_images'], 10, 3);
        add_filter('the_content', [self::class, 'optimize_content_images']);

        // Advanced compression
        add_action('template_redirect', [self::class, 'enable_compression']);

        // Service Worker
        add_action('wp_footer', [self::class, 'register_service_worker']);

        // Performance monitoring
        add_action('wp_footer', [self::class, 'add_performance_monitoring']);
    }

    /**
     * Remove WordPress bloat for better performance
     */
    public static function remove_wordpress_bloat()
    {
        // Remove emoji support
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        // Remove unnecessary WordPress features
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);

        // Disable embeds
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');

        // Disable REST API for non-logged users
        add_filter('rest_authentication_errors', function ($result) {
            if (!is_user_logged_in()) {
                return new WP_Error('rest_cannot_access', 'REST API restricted.', array('status' => 403));
            }
            return $result;
        });

        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');

        // Optimize heartbeat
        add_filter('heartbeat_settings', function ($settings) {
            $settings['interval'] = 120; // 2 minutes instead of 15 seconds
            return $settings;
        });
    }

    /**
     * Optimize WordPress scripts and styles
     */
    public static function optimize_wp_scripts()
    {
        // Remove jQuery Migrate
        if (!is_admin()) {
            wp_deregister_script('jquery');
            wp_register_script('jquery', includes_url('/js/jquery/jquery.min.js'), false, null, true);
            wp_enqueue_script('jquery');
        }

        // Remove unnecessary WordPress styles
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-block-style');
        wp_dequeue_style('global-styles');
    }

    /**
     * Set advanced cache headers
     */
    public static function set_cache_headers()
    {
        if (!is_admin() && !is_user_logged_in()) {
            // Cache static assets for 1 year
            if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2|ttf|eot|ico)$/', $_SERVER['REQUEST_URI'])) {
                header('Cache-Control: public, max-age=31536000, immutable');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
                header('Pragma: public');
            }

            // Cache HTML for 1 hour
            else {
                header('Cache-Control: public, max-age=3600, must-revalidate');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
                header('Vary: Accept-Encoding');
            }
        }
    }

    /**
     * Add advanced resource hints
     */
    public static function add_advanced_resource_hints()
    {
        // Đã có filter wp_resource_hints trong assets.php, không cần in HTML thủ công nữa
    }

    /**
     * Optimize database queries
     */
    public static function optimize_database_queries()
    {
        // Reduce post revisions
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', 3);
        }

        // Optimize autosave interval
        if (!defined('AUTOSAVE_INTERVAL')) {
            define('AUTOSAVE_INTERVAL', 300); // 5 minutes
        }

        // Enable object caching
        if (function_exists('wp_cache_set')) {
            wp_cache_set('performance_optimized', true, 'theme', 3600);
        }
    }

    /**
     * Optimize SQL queries
     */
    public static function optimize_sql_queries($query)
    {
        // Add query optimization if needed
        if (strpos($query, 'SELECT') === 0) {
            // Log slow queries in development
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $start_time = microtime(true);
                register_shutdown_function(function () use ($start_time, $query) {
                    $execution_time = microtime(true) - $start_time;
                    if ($execution_time > 0.5) { // Log queries taking more than 500ms
                        error_log("Slow query detected: {$execution_time}s - {$query}");
                    }
                });
            }
        }
        return $query;
    }

    /**
     * Optimize memory usage
     */
    public static function optimize_memory_usage()
    {
        // Increase memory limit if needed
        if (function_exists('ini_get') && ini_get('memory_limit') < 256) {
            ini_set('memory_limit', '256M');
        }

        // Enable garbage collection
        if (function_exists('gc_enable')) {
            gc_enable();
        }
    }

    /**
     * Cleanup memory at the end
     */
    public static function cleanup_memory()
    {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Optimize images with advanced attributes
     */
    public static function optimize_images($attr, $attachment, $size)
    {
        // Add loading and decoding attributes
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';

        // Add better alt text if missing
        if (empty($attr['alt'])) {
            $attr['alt'] = get_the_title($attachment->ID) ?: 'Image';
        }

        // Add dimensions if missing
        if (empty($attr['width']) || empty($attr['height'])) {
            $image_meta = wp_get_attachment_metadata($attachment->ID);
            if (!empty($image_meta['width']) && !empty($image_meta['height'])) {
                $attr['width'] = $image_meta['width'];
                $attr['height'] = $image_meta['height'];
            }
        }

        return $attr;
    }

    /**
     * Optimize content images
     */
    public static function optimize_content_images($content)
    {
        // Add lazy loading to content images
        $content = preg_replace('/<img((?![^>]*loading)[^>]*)>/', '<img$1 loading="lazy" decoding="async">', $content);

        // Add responsive images if missing
        $content = preg_replace_callback('/<img([^>]+)>/', function ($matches) {
            $img_tag = $matches[0];
            if (strpos($img_tag, 'srcset') === false) {
                // Try to add responsive images here if needed
                return $img_tag;
            }
            return $img_tag;
        }, $content);

        return $content;
    }

    /**
     * Enable compression
     */
    public static function enable_compression()
    {
        if (!is_admin()) {
            // Enable gzip compression
            if (function_exists('gzencode') && !ob_get_contents()) {
                ob_start('ob_gzhandler');
            }
        }
    }

    /**
     * Register service worker for caching
     */
    public static function register_service_worker()
    {
        if (!is_admin() && !is_user_logged_in()) {
            ?>
            <script>
                if ('serviceWorker' in navigator && !navigator.serviceWorker.controller) {
                    window.addEventListener('load', function () {
                        navigator.serviceWorker.register('<?= get_template_directory_uri(); ?>/dist/sw.js', {
                            scope: '/'
                        }).then(function (registration) {
                            console.log('SW registered:', registration.scope);
                        }).catch(function (error) {
                            console.log('SW registration failed:', error);
                        });
                    });
                }
            </script>
            <?php
        }
    }

    /**
     * Add performance monitoring
     */
    public static function add_performance_monitoring()
    {
        if (!is_admin()) {
            ?>
            <script>
                // Core Web Vitals monitoring
                if ('PerformanceObserver' in window) {
                    // Largest Contentful Paint
                    new PerformanceObserver((entryList) => {
                        for (const entry of entryList.getEntries()) {
                            console.log('LCP:', entry.startTime);
                        }
                    }).observe({ type: 'largest-contentful-paint', buffered: true });

                    // Cumulative Layout Shift
                    new PerformanceObserver((entryList) => {
                        for (const entry of entryList.getEntries()) {
                            if (!entry.hadRecentInput) {
                                console.log('CLS:', entry.value);
                            }
                        }
                    }).observe({ type: 'layout-shift', buffered: true });

                    // First Input Delay
                    new PerformanceObserver((entryList) => {
                        for (const entry of entryList.getEntries()) {
                            console.log('FID:', entry.processingStart - entry.startTime);
                        }
                    }).observe({ type: 'first-input', buffered: true });
                }

                // Performance marks
                if ('performance' in window && 'mark' in performance) {
                    performance.mark('theme-loaded');
                }
            </script>
            <?php
        }
    }
}

// Initialize advanced performance optimizations
ThemePerformance::init();