<?php
/**
 * AJAX Search Functionality
 * 
 * Secure AJAX search implementation with nonce verification
 * 
 * @package MoomsDev
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Search Class
 */
class ThemeAjaxSearch {
    
    /**
     * Initialize AJAX search
     */
    public static function init() {
        add_action('wp_ajax_theme_ajax_search', [self::class, 'handle_search']);
        add_action('wp_ajax_nopriv_theme_ajax_search', [self::class, 'handle_search']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
    }
    
    /**
     * Handle AJAX search request
     */
    public static function handle_search() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'theme_ajax_search')) {
            wp_die(__('Security check failed', 'mms'));
        }
        
        // Sanitize search query
        $search_query = sanitize_text_field($_POST['search_query'] ?? '');
        
        if (empty($search_query) || strlen($search_query) < 2) {
            wp_send_json_error(__('Từ khóa tìm kiếm quá ngắn', 'mms'));
        }
        
        // Perform search
        $results = self::perform_search($search_query);
        
        if (!empty($results)) {
            wp_send_json_success([
                'html' => $results,
                'count' => count($results)
            ]);
        } else {
            wp_send_json_error(__('Không có kết quả', 'mms'));
        }
    }
    
    /**
     * Perform the actual search
     * 
     * @param string $query Search query
     * @return array Search results
     */
    private static function perform_search($query) {
        $args = [
            'post_type' => ['post', 'service', 'blog'],
            'posts_per_page' => 10,
            's' => $query,
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_search_exclude',
                    'value' => '1',
                    'compare' => '!='
                ],
                [
                    'key' => '_search_exclude',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        
        $search_query = new WP_Query($args);
        $results = [];
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                
                $results[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 20),
                    'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                    'post_type' => get_post_type()
                ];
            }
            wp_reset_postdata();
        }
        
        return $results;
    }
    
    /**
     * Enqueue AJAX search scripts
     */
    public static function enqueue_scripts() {
        // TODO: Create ajax-search.js file first
        /*
        wp_enqueue_script(
            'theme-ajax-search',
            get_template_directory_uri() . '/dist/js/ajax-search.js',
            ['jquery'],
            wp_get_theme()->get('Version'),
            true
        );
        */
        
        /*
        wp_localize_script('theme-ajax-search', 'themeAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('theme_ajax_search'),
            'strings' => [
                'searching' => __('Đang tìm kiếm...', 'mms'),
                'no_results' => __('Không có kết quả', 'mms'),
                'error' => __('Có lỗi xảy ra', 'mms'),
                'min_chars' => __('Nhập ít nhất 2 ký tự', 'mms')
            ]
        ]);
        */
    }
    
    /**
     * Get search result HTML template
     * 
     * @param array $result Search result data
     * @return string HTML template
     */
    private static function get_result_template($result) {
        $template = '
        <div class="search-result-item" data-id="' . esc_attr($result['id']) . '">
            <div class="search-result-content">
                <h4 class="search-result-title">
                    <a href="' . esc_url($result['permalink']) . '">' . esc_html($result['title']) . '</a>
                </h4>
                <p class="search-result-excerpt">' . esc_html($result['excerpt']) . '</p>
                <span class="search-result-type">' . esc_html($result['post_type']) . '</span>
            </div>';
            
        if ($result['thumbnail']) {
            $template .= '
            <div class="search-result-thumbnail">
                <img src="' . esc_url($result['thumbnail']) . '" alt="' . esc_attr($result['title']) . '">
            </div>';
        }
        
        $template .= '</div>';
        
        return $template;
    }
}

// Initialize AJAX search
ThemeAjaxSearch::init(); 