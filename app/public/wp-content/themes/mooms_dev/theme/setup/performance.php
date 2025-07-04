<?php
/**
 * Advanced Performance Optimizations
 * 
 * Comprehensive performance optimizations for high PageSpeed Insights scores
 * 
 * @package MoomsDev
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Performance Optimization Class
 */
class ThemePerformance {
    
    /**
     * Initialize all performance optimizations
     */
    public static function init() {
        self::remove_emoji_support();
        self::optimize_styles_loading();
        self::optimize_scripts_loading();
        self::optimize_images();
        self::implement_lazy_loading();
        self::add_resource_hints();
        self::optimize_fonts();
        self::implement_critical_css();
        self::add_service_worker();
        self::optimize_database_queries();
        self::remove_unnecessary_wp_features();
        self::implement_caching_headers();
    }
    
    /**
     * Remove emoji support to improve performance
     */
    private static function remove_emoji_support() {
        add_action('init', static function () {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            
            // Remove emoji from TinyMCE
            add_filter('tiny_mce_plugins', static function ($plugins) {
                return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
            });

            // Remove emoji DNS prefetch
            add_filter('wp_resource_hints', static function ($urls, $relation_type) {
                if ('dns-prefetch' === $relation_type) {
                    $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
                    $urls = array_diff($urls, [$emoji_svg_url]);
                }
                return $urls;
            }, 10, 2);
        });
    }
    
    /**
     * Advanced CSS loading optimization
     */
    private static function optimize_styles_loading() {
        // Critical CSS inline
        add_action('wp_head', function() {
            $critical_css = get_template_directory() . '/dist/css/critical.css';
            if (file_exists($critical_css)) {
                echo '<style id="critical-css">';
                echo file_get_contents($critical_css);
                echo '</style>';
            }
        }, 1);
        
        // Non-critical CSS with preload
        add_filter('style_loader_tag', function ($html, $handle, $href) {
            $non_critical_styles = ['theme-css-bundle', 'bootstrap', 'fontawesome'];
            
            if (in_array($handle, $non_critical_styles)) {
                return '<link rel="preload" href="' . $href . '" as="style" id="' . $handle . '-preload" onload="this.onload=null;this.rel=\'stylesheet\'">' .
                       '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
            }
            
            return $html;
        }, 10, 3);
        
        // Remove unused CSS
        add_action('wp_enqueue_scripts', function() {
            // Remove block library CSS if not using Gutenberg
            if (!is_admin() && !current_user_can('edit_posts')) {
                wp_dequeue_style('wp-block-library');
                wp_dequeue_style('wp-block-library-theme');
                wp_dequeue_style('wc-block-style');
            }
        }, 100);
    }
    
    /**
     * Advanced JavaScript loading optimization
     */
    private static function optimize_scripts_loading() {
        // Defer non-critical JavaScript
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            $defer_scripts = ['theme-js-bundle', 'bootstrap-js', 'jquery-effects'];
            $async_scripts = ['google-analytics', 'facebook-pixel'];
            
            if (in_array($handle, $defer_scripts)) {
                return str_replace('<script ', '<script defer ', $tag);
            }
            
            if (in_array($handle, $async_scripts)) {
                return str_replace('<script ', '<script async ', $tag);
            }
            
            return $tag;
        }, 10, 3);
        
        // Move jQuery to footer for better performance
        add_action('wp_enqueue_scripts', function() {
            if (!is_admin()) {
                wp_scripts()->add_data('jquery', 'group', 1);
                wp_scripts()->add_data('jquery-core', 'group', 1);
                wp_scripts()->add_data('jquery-migrate', 'group', 1);
            }
        });
    }
    
    /**
     * Advanced image optimization
     */
    private static function optimize_images() {
        // WebP support
        add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
            if (!function_exists('imagewebp')) {
                return $metadata;
            }
            
            $file_path = get_attached_file($attachment_id);
            $file_info = pathinfo($file_path);
            
            if (in_array(strtolower($file_info['extension']), ['jpg', 'jpeg', 'png'])) {
                $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
                
                $image = imagecreatefromstring(file_get_contents($file_path));
                if ($image) {
                    imagewebp($image, $webp_path, 80);
                    imagedestroy($image);
                }
            }
            
            return $metadata;
        }, 10, 2);
        
        // Responsive images optimization
        add_filter('wp_calculate_image_sizes', function($sizes) {
            return '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw';
        });
        
        // Remove unnecessary image sizes
        add_filter('intermediate_image_sizes_advanced', function($sizes) {
            $keep_sizes = ['thumbnail', 'medium', 'large', 'medium_large'];
            return array_intersect_key($sizes, array_flip($keep_sizes));
        });
    }
    
    /**
     * Implement native lazy loading
     */
    private static function implement_lazy_loading() {
        // Native lazy loading for images
        add_filter('wp_get_attachment_image_attributes', function($attr) {
            $attr['loading'] = 'lazy';
            return $attr;
        });
        
        // Lazy loading for content images
        add_filter('the_content', function($content) {
            if (is_admin() || is_feed() || wp_is_json_request()) {
                return $content;
            }
            
            return preg_replace('/<img(.*?)>/i', '<img$1 loading="lazy">', $content);
        });
        
        // Intersection Observer for advanced lazy loading
        add_action('wp_footer', function() {
            ?>
            <script>
            if ('IntersectionObserver' in window) {
                const lazyImages = document.querySelectorAll('img[data-src]');
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                lazyImages.forEach(img => imageObserver.observe(img));
            }
            </script>
            <?php
        });
    }
    
    /**
     * Add resource hints for better performance
     */
    private static function add_resource_hints() {
        add_action('wp_head', function() {
            // DNS prefetch for external resources
            echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
            echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">';
            
            // Preconnect to critical domains
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
            
            // Prefetch next likely pages
            if (is_home() || is_front_page()) {
                echo '<link rel="prefetch" href="' . get_permalink(get_option('page_for_posts')) . '">';
            }
        }, 1);
    }
    
    /**
     * Font optimization
     */
    private static function optimize_fonts() {
        // Preload critical fonts
        add_action('wp_head', function() {
            $critical_fonts = [
                '/wp-content/themes/mooms_dev/dist/fonts/primary-font.woff2',
                '/wp-content/themes/mooms_dev/dist/fonts/heading-font.woff2'
            ];
            
            foreach ($critical_fonts as $font) {
                if (file_exists(ABSPATH . $font)) {
                    echo '<link rel="preload" href="' . $font . '" as="font" type="font/woff2" crossorigin>';
                }
            }
        }, 1);
        
        // Font display optimization
        add_filter('style_loader_tag', function($html, $handle) {
            if (strpos($handle, 'google-fonts') !== false) {
                return str_replace('rel=\'stylesheet\'', 'rel="stylesheet" media="print" onload="this.media=\'all\'"', $html);
            }
            return $html;
        }, 10, 2);
    }
    
    /**
     * Critical CSS implementation
     */
    private static function implement_critical_css() {
        add_action('wp_head', function() {
            $page_template = get_page_template_slug();
            $critical_css_file = get_template_directory() . '/dist/css/critical-' . ($page_template ?: 'default') . '.css';
            
            if (file_exists($critical_css_file)) {
                echo '<style id="critical-css-' . ($page_template ?: 'default') . '">';
                echo file_get_contents($critical_css_file);
                echo '</style>';
            }
        }, 1);
    }
    
    /**
     * Service Worker for caching (with corrected path)
     */
    private static function add_service_worker() {
        add_action('wp_head', function() {
            if (!is_admin()) {
                // Fix path: remove /theme/ from template directory
                $my_theme   = wp_get_theme();
                $theme_name = str_replace('/theme', '', $my_theme->get_stylesheet());
                $theme_path = str_replace('wp-content/themes/'. $theme_name .'/theme', 'wp-content/themes/' . $theme_name . '/', $my_theme->get_template_directory_uri());

                $sw_url = $theme_path . '/dist/sw.js';

                ?>
                <script>
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', () => {
                        navigator.serviceWorker.register('<?= $sw_url; ?>')
                            .then(registration => {
                                console.log('SW registered successfully:', registration.scope);
                            })
                            .catch(registrationError => {
                                console.log('SW registration failed:', registrationError);
                            });
                    });
                }
                </script>
                <?php
            }
        });
    }
    
    /**
     * Database query optimization
     */
    private static function optimize_database_queries() {
        // Remove unnecessary queries
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        
        // Optimize query vars
        add_filter('query_vars', function($vars) {
            $vars[] = 'cache_bust';
            return $vars;
        });
        
        // Object caching for expensive queries
        add_action('init', function() {
            if (function_exists('wp_cache_add_global_groups')) {
                wp_cache_add_global_groups(['theme_cache']);
            }
        });
    }
    
    /**
     * Remove unnecessary WordPress features
     */
    private static function remove_unnecessary_wp_features() {
        // Remove unnecessary meta tags
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        
        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Remove jQuery migrate
        add_action('wp_default_scripts', function($scripts) {
            if (!is_admin() && isset($scripts->registered['jquery'])) {
                $script = $scripts->registered['jquery'];
                if ($script->deps) {
                    $script->deps = array_diff($script->deps, ['jquery-migrate']);
                }
            }
        });
        
        // Clean up dashboard for performance
        add_action('wp_dashboard_setup', function() {
            if (!current_user_can('manage_options')) {
                remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
                remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
                remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
                remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
                remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
                remove_meta_box('dashboard_primary', 'dashboard', 'side');
                remove_meta_box('dashboard_secondary', 'dashboard', 'side');
            }
        });
    }
    
    /**
     * Implement caching headers
     */
    private static function implement_caching_headers() {
        add_action('send_headers', function() {
            if (!is_admin()) {
                // Cache static assets for 1 year
                if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2|ttf|eot)$/', $_SERVER['REQUEST_URI'])) {
                    header('Cache-Control: public, max-age=31536000, immutable');
                    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
                }
                
                // Cache HTML for 1 hour
                if (!is_user_logged_in() && !is_admin()) {
                    header('Cache-Control: public, max-age=3600');
                    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
                }
            }
        });
    }
}

// Initialize advanced performance optimizations
ThemePerformance::init(); 