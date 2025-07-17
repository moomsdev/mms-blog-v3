<?php
use WPEmerge\Facades\WPEmerge;
use WPEmergeTheme\Facades\Theme;

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// SECURITY & PERMISSIONS
// =============================================================================

define('ALLOW_UNFILTERED_UPLOADS', true);
define('SUPER_USER', ['mooms.dev']);

// =============================================================================
// THEME INFORMATION
// =============================================================================

define('AUTHOR', [
    'name' => 'La Cà Dev',
    'email' => 'support@mooms.dev',
    'phone_number' => '0989 64 67 66',
    'website' => 'https://mooms.dev/',
    'date_started' => get_option('_theme_info_date_started'),
    'date_published' => get_option('_theme_info_date_publish'),
]);

// =============================================================================
// DIRECTORY CONSTANTS
// =============================================================================

// Directory Names
define('APP_APP_DIR_NAME', 'app');
define('APP_APP_HELPERS_DIR_NAME', 'helpers');
define('APP_APP_ROUTES_DIR_NAME', 'routes');
define('APP_APP_SETUP_DIR_NAME', 'setup');
define('APP_DIST_DIR_NAME', 'dist');
define('APP_RESOURCES_DIR_NAME', 'resources');
define('APP_THEME_DIR_NAME', 'theme');
define('APP_VENDOR_DIR_NAME', 'vendor');

// Theme Component Names
define('APP_THEME_USER_NAME', 'users');
define('APP_THEME_ECOMMERCE_NAME', 'users');
define('APP_THEME_POST_TYPE_NAME', 'post-types');
define('APP_THEME_TAXONOMY_NAME', 'taxonomies');
define('APP_THEME_WIDGET_NAME', 'widgets');
define('APP_THEME_BLOCK_NAME', 'blocks');
define('APP_THEME_WALKER_NAME', 'walkers');

// Directory Paths
define('APP_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_APP_DIR', APP_DIR . APP_APP_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_HELPERS_DIR', APP_APP_DIR . APP_APP_HELPERS_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_ROUTES_DIR', APP_APP_DIR . APP_APP_ROUTES_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_RESOURCES_DIR', APP_DIR . APP_RESOURCES_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_THEME_DIR', APP_DIR . APP_THEME_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_VENDOR_DIR', APP_DIR . APP_VENDOR_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_DIST_DIR', APP_DIR . APP_DIST_DIR_NAME . DIRECTORY_SEPARATOR);

// Setup Directories
define('APP_APP_SETUP_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_ECOMMERCE_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_ECOMMERCE_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_USER_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_USER_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_POST_TYPE_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_POST_TYPE_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_TAXONOMY_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_TAXONOMY_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_WIDGET_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_WIDGET_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_BLOCK_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_BLOCK_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_WALKER_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_WALKER_NAME . DIRECTORY_SEPARATOR);

// =============================================================================
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
    require_once APP_APP_SETUP_DIR . 'ajax.php';

    // Load advanced optimization modules
    require_once APP_APP_SETUP_DIR . 'performance.php';
    require_once APP_APP_SETUP_DIR . 'assets.php';

    // Load Gutenberg blocks
    $blocks_dir = APP_APP_SETUP_DIR . '/blocks';
    $block_files = glob($blocks_dir . '/*.php');
    foreach ($block_files as $block_file) {
        require_once $block_file;
    }
});

// =============================================================================
// PERFORMANCE OPTIMIZATIONS
// =============================================================================

// Remove emoji support
add_action('init', static function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter('tiny_mce_plugins', static function ($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });

    add_filter('wp_resource_hints', static function ($urls, $relation_type) {
        if ('dns-prefetch' === $relation_type) {
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
            $urls = array_diff($urls, [$emoji_svg_url]);
        }
        return $urls;
    }, 10, 2);
});

// Optimize style loading
add_filter('style_loader_tag', function ($html, $handle) {
    return str_replace("media='all' />", 'media="all" rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">', $html);
}, 10, 2);

// Prevent thumbnail generation
function remove_all_image_sizes($sizes)
{
    return array();
}
add_filter('intermediate_image_sizes_advanced', 'remove_all_image_sizes');

// =============================================================================
// EDITOR CUSTOMIZATIONS
// =============================================================================

function tinymce_allow_unsafe_link_target($mceInit)
{
    $mceInit['allow_unsafe_link_target'] = true;
    return $mceInit;
}

// =============================================================================
// SCRIPTS & STYLES
// =============================================================================

function my_theme_enqueue_scripts()
{
    wp_localize_script('my-theme-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');

// =============================================================================
// AUTOLOAD COMPONENTS
// =============================================================================

$folders = [
    APP_APP_SETUP_ECOMMERCE_DIR,
    APP_APP_SETUP_TAXONOMY_DIR,
    APP_APP_SETUP_WALKER_DIR,
];

foreach ($folders as $folder) {
    $filesPath = scandir($folder);
    if ($filesPath !== false) {
        foreach ($filesPath as $item) {
            $file = $folder . $item;
            if (is_file($file)) {
                require_once $folder . $item;
            }
        }
    }
}

// =============================================================================
// AJAX SEARCH
// =============================================================================

function ajax_search()
{
    // Kiểm tra nếu là request AJAX và có tham số 's'
    if (isset($_GET['s'])) {
        $search_query = sanitize_text_field($_GET['s']);

        $args = array(
            'post_type' => ['post', 'service', 'blog'], // Loại post bạn muốn tìm kiếm (có thể là 'post', 'page', hoặc loại post tùy chỉnh)
            'posts_per_page' => 10, // Số lượng bài viết bạn muốn lấy
            's' => $search_query,
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                // Output HTML cho từng bài viết
                echo '<div class="search-result-item">';
                echo '<a href="' . get_permalink() . '">';
                echo '<h4>' . get_the_title() . '</h4>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<div class="no-results">' . __('Không có kết quả', 'mms') . '</div>';
        }
        wp_reset_postdata();
    }
    die(); // Dừng script tại đây
}

add_action('wp_ajax_nopriv_ajax_search', 'ajax_search');
add_action('wp_ajax_ajax_search', 'ajax_search');
function custom_ajax_script()
{
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#search-input').on('input', function () {
                var searchQuery = $(this).val();

                if (searchQuery.length > 2) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'GET',
                        data: {
                            action: 'ajax_search',
                            s: searchQuery
                        },
                        beforeSend: function () {
                            // Bạn có thể hiển thị một biểu tượng loading ở đây
                        },
                        success: function (response) {
                            // Hiển thị kết quả tìm kiếm
                            $('.modal-body .search-results')
                                .html(response);
                        },
                        error: function () {
                            // Hiển thị thông báo lỗi nếu có
                        }
                    });
                } else {
                    $('.modal-body .search-results').html('');
                }
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_ajax_script');

// =============================================================================
// CUSTOM POST TYPES
// =============================================================================

new \App\PostTypes\blog();
