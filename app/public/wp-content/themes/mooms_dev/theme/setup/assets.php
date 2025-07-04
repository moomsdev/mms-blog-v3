<?php
/**
 * Advanced Asset Management
 * 
 * Optimized asset loading for maximum PageSpeed Insights performance
 *
 * @package MoomsDev
 * @since 1.0.0
 */

use WPEmergeTheme\Facades\Theme;
use WPEmergeTheme\Facades\Assets;

/**
 * Enhanced asset loading with performance optimizations
 */
class ThemeAssets {
    
    /**
     * Initialize asset management
     */
    public static function init() {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend_assets'], 10);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets'], 10);
        add_action('login_enqueue_scripts', [self::class, 'enqueue_login_assets'], 10);
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_editor_assets'], 10);
        
        // Asset optimization hooks
        add_filter('script_loader_tag', [self::class, 'optimize_script_loading'], 10, 3);
        add_filter('style_loader_tag', [self::class, 'optimize_style_loading'], 10, 3);
        add_action('wp_head', [self::class, 'add_resource_hints'], 1);
        add_action('wp_head', [self::class, 'inline_critical_css'], 2);
        add_action('wp_footer', [self::class, 'load_non_critical_css'], 99);
    }
    
    /**
     * Enqueue optimized frontend assets
     */
    public static function enqueue_frontend_assets() {
        $template_dir = Theme::uri();
        $version = wp_get_theme()->get('Version');
        
        // Critical CSS already inlined in head
        // Non-critical CSS loaded via JavaScript
        
        // Critical JavaScript (inline or very small)
        wp_enqueue_script(
            'theme-critical-js',
            $template_dir . '/dist/js/critical.min.js',
            [],
            $version,
            false // Load in head for critical functionality
        );
        
        // Main JavaScript bundle (deferred)
        wp_enqueue_script(
            'theme-main-js',
            $template_dir . '/dist/js/main.min.js',
            ['theme-critical-js'],
            $version,
            true // Load in footer
        );
        
        // Vendor JavaScript (deferred, separate chunk for caching)
        wp_enqueue_script(
            'theme-vendor-js',
            $template_dir . '/dist/js/vendor.min.js',
            [],
            $version,
            true
        );
        
        // Conditional assets based on page type
        if (is_singular()) {
            wp_enqueue_script('comment-reply');
        }
        
        if (is_home() || is_archive() || is_search()) {
            wp_enqueue_script(
                'theme-archive-js',
                $template_dir . '/dist/js/archive.min.js',
                ['theme-main-js'],
                $version,
                true
            );
        }
        
        // Localize script with minimal data
        wp_localize_script('theme-main-js', 'themeData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('theme_nonce'),
            'isHome' => is_home(),
            'isMobile' => wp_is_mobile(),
        ]);
    }
    
    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets() {
        $template_dir = Theme::uri();
        $version = wp_get_theme()->get('Version');
        
        wp_enqueue_script(
            'theme-admin-js',
            $template_dir . '/dist/js/admin.min.js',
            ['jquery'],
            $version,
            true
        );
        
        wp_enqueue_style(
            'theme-admin-css',
            $template_dir . '/dist/css/admin-css.min.css',
            [],
            $version
        );
    }
    
    /**
     * Enqueue login assets
     */
    public static function enqueue_login_assets() {
        $template_dir = Theme::uri();
        $version = wp_get_theme()->get('Version');
        
        // TODO: Fix image dependencies first
        // wp_enqueue_style(
        //     'theme-login-css',
        //     $template_dir . '/dist/css/login-css.min.css',
        //     [],
        //     $version
        // );
        
        wp_enqueue_script(
            'theme-login-js',
            $template_dir . '/dist/js/login.min.js',
            ['jquery'],
            $version,
            true
        );
    }
    
    /**
     * Enqueue editor assets
     */
    public static function enqueue_editor_assets() {
        $template_dir = Theme::uri();
        $version = wp_get_theme()->get('Version');
        
        // TODO: Fix image dependencies first  
        // wp_enqueue_style(
        //     'theme-editor-css',
        //     $template_dir . '/dist/css/editor-css.min.css',
        //     ['wp-edit-blocks'],
        //     $version
        // );
        
        wp_enqueue_script(
            'theme-editor-js',
            $template_dir . '/dist/js/editor.min.js',
            ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
            $version,
            true
        );
    }
    
    /**
     * Optimize script loading with defer/async
     */
    public static function optimize_script_loading($tag, $handle, $src) {
        // Scripts to defer (non-critical)
        $defer_scripts = [
            'theme-main-js',
            'theme-vendor-js',
            'theme-archive-js',
            'google-analytics',
            'facebook-pixel'
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
            $preload_tag = '<link rel="preload" href="' . $src . '" as="script">';
            echo $preload_tag;
        }
        
        return $tag;
    }
    
    /**
     * Optimize style loading with preload
     */
    public static function optimize_style_loading($tag, $handle, $href) {
        // Non-critical styles to load asynchronously
        $non_critical_styles = [
            'theme-main-css',
            'theme-components-css',
            'fontawesome',
            'google-fonts'
        ];
        
        if (in_array($handle, $non_critical_styles)) {
            // Load non-critical CSS asynchronously
            return '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" id="' . $handle . '">' .
                   '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
        }
        
        return $tag;
    }
    
    /**
     * Add resource hints for performance
     */
    public static function add_resource_hints() {
        ?>
        <!-- DNS Prefetch -->
        <link rel="dns-prefetch" href="//fonts.googleapis.com">
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
        <link rel="dns-prefetch" href="//ajax.googleapis.com">
        
        <!-- Preconnect to critical domains -->
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        
        <!-- Preload critical fonts -->
        <?php
        // TODO: Copy fonts to dist/fonts/ directory first
        /*
        $critical_fonts = [
            Theme::uri() . '/dist/fonts/primary-regular.woff2',
            Theme::uri() . '/dist/fonts/primary-bold.woff2',
        ];
        
        foreach ($critical_fonts as $font_url) {
            echo '<link rel="preload" href="' . $font_url . '" as="font" type="font/woff2" crossorigin>';
        }
        */
        ?>
        
        <!-- Prefetch likely next pages -->
        <?php if (is_home() || is_front_page()): ?>
            <link rel="prefetch" href="<?= get_permalink(get_option('page_for_posts')); ?>">
        <?php endif; ?>
        
        <?php if (is_single()): ?>
            <!-- Prefetch related posts -->
            <?php
            $related_posts = get_posts([
                'numberposts' => 2,
                'post__not_in' => [get_the_ID()],
                'category__in' => wp_get_post_categories(get_the_ID()),
                'meta_key' => '_thumbnail_id'
            ]);
            
            foreach ($related_posts as $post):
                echo '<link rel="prefetch" href="' . get_permalink($post->ID) . '">';
            endforeach;
            ?>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Inline critical CSS
     */
    public static function inline_critical_css() {
        $critical_css_files = [
            'critical' => get_template_directory() . '/dist/css/critical.min.css',
            'above-fold' => get_template_directory() . '/dist/css/above-fold.min.css',
        ];
        
        // Page-specific critical CSS
        $page_template = get_page_template_slug();
        if ($page_template) {
            $template_name = str_replace('.php', '', basename($page_template));
            $critical_css_files['template'] = get_template_directory() . '/dist/css/critical-' . $template_name . '.min.css';
        }
        
        // Device-specific critical CSS
        if (wp_is_mobile()) {
            $critical_css_files['mobile'] = get_template_directory() . '/dist/css/critical-mobile.min.css';
        }
        
        echo '<style id="critical-css">';
        foreach ($critical_css_files as $name => $file) {
            if (file_exists($file)) {
                echo '/* Critical CSS: ' . $name . ' */' . PHP_EOL;
                echo file_get_contents($file) . PHP_EOL;
            }
        }
        echo '</style>';
        
        // Preload non-critical CSS
        $non_critical_css = [
            'main' => Theme::uri() . '/dist/css/main-css.min.css',
            // 'components' => Theme::uri() . '/dist/css/components.min.css', // TODO: Create components CSS
        ];
        
        foreach ($non_critical_css as $name => $url) {
            echo '<link rel="preload" href="' . $url . '" as="style" id="preload-' . $name . '">';
        }
    }
    
    /**
     * Load non-critical CSS at the end
     */
    public static function load_non_critical_css() {
        ?>
        <script>
        // Load non-critical CSS
        (function() {
            var preloadLinks = document.querySelectorAll('link[rel="preload"][as="style"]');
            preloadLinks.forEach(function(link) {
                link.addEventListener('load', function() {
                    this.rel = 'stylesheet';
                });
                // Fallback for browsers that don't support preload
                setTimeout(function() {
                    if (link.rel === 'preload') {
                        link.rel = 'stylesheet';
                    }
                }, 100);
            });
        })();
        
        // Service Worker registration - DISABLED (using performance.php instead)
        /*
        if ('serviceWorker' in navigator && !navigator.serviceWorker.controller) {
            navigator.serviceWorker.register('<?= Theme::uri(); ?>/dist/sw.js', {
                scope: '/'
            }).then(function(registration) {
                console.log('SW registered with scope:', registration.scope);
            }).catch(function(error) {
                console.log('SW registration failed:', error);
            });
        }
        */
        </script>
        <?php
    }
    
    /**
     * Get asset URL with cache busting
     */
    public static function asset_url($path) {
        $file_path = get_template_directory() . '/dist/' . $path;
        $file_url = Theme::uri() . '/dist/' . $path;
        
        if (file_exists($file_path)) {
            $version = filemtime($file_path);
            return $file_url . '?v=' . $version;
        }
        
        return $file_url;
    }
    
    /**
     * Check if asset exists
     */
    public static function asset_exists($path) {
        return file_exists(get_template_directory() . '/dist/' . $path);
    }
}

// Initialize enhanced asset management
ThemeAssets::init();

/**
 * Legacy functions for backward compatibility
 */
function app_action_theme_enqueue_assets() {
    ThemeAssets::enqueue_frontend_assets();
}

function app_action_admin_enqueue_assets() {
    ThemeAssets::enqueue_admin_assets();
}

function app_action_login_enqueue_assets() {
    ThemeAssets::enqueue_login_assets();
}

function app_action_editor_enqueue_assets() {
    ThemeAssets::enqueue_editor_assets();
}

function app_action_add_favicon() {
    Assets::addFavicon();
}
