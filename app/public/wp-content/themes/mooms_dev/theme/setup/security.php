<?php
/**
 * Advanced Security Hardening
 * 
 * Comprehensive security enhancements for WordPress
 *
 * @package MoomsDev
 * @since 1.0.0
 */

class ThemeSecurity
{

    /**
     * Initialize security hardening
     */
    public static function init()
    {
        // Core security measures
        add_action('init', [self::class, 'remove_wp_vulnerabilities']);
        add_action('template_redirect', [self::class, 'set_security_headers']);

        // Login security
        add_action('wp_login_failed', [self::class, 'limit_login_attempts']);
        add_filter('authenticate', [self::class, 'check_login_attempts'], 30, 3);
        add_action('wp_login', [self::class, 'log_successful_login'], 10, 2);

        // File upload security
        add_filter('upload_mimes', [self::class, 'restrict_file_uploads']);
        add_filter('wp_handle_upload_prefilter', [self::class, 'scan_uploaded_files']);

        // Hide sensitive information
        add_action('init', [self::class, 'hide_wp_version']);
        add_filter('login_errors', [self::class, 'generic_login_error']);

        // Disable dangerous features
        add_action('init', [self::class, 'disable_file_editing']);
        add_action('init', [self::class, 'block_user_enumeration']);

        // Content Security Policy
        add_action('wp_head', [self::class, 'add_content_security_policy'], 1);

        // Advanced protection
        add_action('init', [self::class, 'prevent_hotlinking']);
        add_action('template_redirect', [self::class, 'rate_limiting']);

        // New advanced security features
        add_action('init', [self::class, 'protect_wp_config']);
        add_action('init', [self::class, 'disable_directory_browsing']);
        add_action('init', [self::class, 'block_malicious_requests']);
        add_action('wp_loaded', [self::class, 'monitor_file_changes']);
        add_action('admin_init', [self::class, 'force_ssl_admin']);
        add_action('init', [self::class, 'hide_sensitive_files']);
        add_filter('wp_headers', [self::class, 'remove_security_headers']);
        add_action('init', [self::class, 'disable_author_pages']);
        add_action('wp_head', [self::class, 'add_security_meta_tags']);

        // SQL Injection protection
        add_action('init', [self::class, 'protect_against_sql_injection']);

        // Admin security
        add_action('admin_init', [self::class, 'admin_security_measures']);

        // IP-based security
        add_action('init', [self::class, 'ip_based_security']);

        // Database security
        add_action('init', [self::class, 'database_security']);
    }

    /**
     * Remove WordPress vulnerabilities
     */
    public static function remove_wp_vulnerabilities()
    {
        // Remove version info
        remove_action('wp_head', 'wp_generator');

        // Remove Windows Live Writer manifest
        remove_action('wp_head', 'wlwmanifest_link');

        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');

        // Remove RSD link
        remove_action('wp_head', 'rsd_link');

        // Disable REST API for unauthorized users
        add_filter('rest_authentication_errors', function ($result) {
            if (!is_user_logged_in()) {
                return new WP_Error('rest_cannot_access', 'REST API access restricted.', array('status' => 401));
            }
            return $result;
        });

        // Disable pingbacks
        add_filter('xmlrpc_methods', function ($methods) {
            unset($methods['pingback.ping']);
            unset($methods['pingback.extensions.getPingbacks']);
            return $methods;
        });
    }

    /**
     * Set advanced security headers
     */
    public static function set_security_headers()
    {
        if (!is_admin()) {
            // Prevent clickjacking
            header('X-Frame-Options: SAMEORIGIN');

            // Prevent MIME type sniffing
            header('X-Content-Type-Options: nosniff');

            // Enable XSS protection
            header('X-XSS-Protection: 1; mode=block');

            // Referrer policy
            header('Referrer-Policy: strict-origin-when-cross-origin');

            // Permissions policy
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

            // Strict Transport Security (HTTPS only)
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }

            // Remove server signature
            header_remove('X-Powered-By');
            header_remove('Server');
        }
    }

    /**
     * Limit login attempts
     */
    public static function limit_login_attempts($username)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $attempts = get_transient('login_attempts_' . $ip) ?: 0;
        $attempts++;

        set_transient('login_attempts_' . $ip, $attempts, 900); // 15 minutes

        if ($attempts >= 5) {
            set_transient('login_blocked_' . $ip, true, 3600); // 1 hour block
        }
    }

    /**
     * Check login attempts before authentication
     */
    public static function check_login_attempts($user, $username, $password)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (get_transient('login_blocked_' . $ip)) {
            return new WP_Error('too_many_attempts', 'Too many failed login attempts. Please try again later.');
        }

        return $user;
    }

    /**
     * Restrict file upload types
     */
    public static function restrict_file_uploads($mimes)
    {
        // Remove potentially dangerous file types
        unset($mimes['exe']);
        unset($mimes['bat']);
        unset($mimes['cmd']);
        unset($mimes['com']);
        unset($mimes['pif']);
        unset($mimes['scr']);
        unset($mimes['vbs']);
        unset($mimes['php']);

        // Add safe file types if needed
        $mimes['webp'] = 'image/webp';

        return $mimes;
    }

    /**
     * Scan uploaded files for security threats
     */
    public static function scan_uploaded_files($file)
    {
        $filename = $file['name'];
        $filetype = wp_check_filetype($filename);

        // Check for executable files disguised as images
        if (preg_match('/\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$/i', $filename)) {
            $file['error'] = 'File type not allowed for security reasons.';
        }

        // Check file size (limit to 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $file['error'] = 'File too large. Maximum size is 10MB.';
        }

        return $file;
    }

    /**
     * Hide WordPress version
     */
    public static function hide_wp_version()
    {
        // Remove version from scripts and styles
        add_filter('script_loader_src', [self::class, 'remove_version_from_assets'], 9999);
        add_filter('style_loader_src', [self::class, 'remove_version_from_assets'], 9999);

        // Remove version from RSS feeds
        add_filter('the_generator', '__return_empty_string');
    }

    /**
     * Remove version parameters from assets
     */
    public static function remove_version_from_assets($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }

    /**
     * Generic login error message
     */
    public static function generic_login_error($error)
    {
        return 'Login failed. Please check your credentials.';
    }

    /**
     * Disable file editing in WordPress admin
     */
    public static function disable_file_editing()
    {
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }

    /**
     * Block user enumeration attempts
     */
    public static function block_user_enumeration()
    {
        if (!is_admin() && isset($_REQUEST['author'])) {
            wp_redirect(home_url());
            exit;
        }

        // Block REST API user endpoints
        add_filter('rest_endpoints', function ($endpoints) {
            if (isset($endpoints['/wp/v2/users'])) {
                unset($endpoints['/wp/v2/users']);
            }
            if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
            }
            return $endpoints;
        });
    }

    /**
     * Add Content Security Policy
     */
    public static function add_content_security_policy()
    {
        if (!is_admin()) {
            $csp = "default-src 'self'; ";
            $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://ajax.googleapis.com; ";
            $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
            $csp .= "font-src 'self' https://fonts.gstatic.com; ";
            $csp .= "img-src 'self' data: https:; ";
            $csp .= "connect-src 'self'; ";
            $csp .= "frame-ancestors 'self'; ";
            $csp .= "base-uri 'self'; ";
            $csp .= "form-action 'self';";

            echo '<meta http-equiv="Content-Security-Policy" content="' . $csp . '">' . "\n";
        }
    }

    /**
     * Prevent hotlinking
     */
    public static function prevent_hotlinking()
    {
        if (!is_admin() && isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            $host = $_SERVER['HTTP_HOST'];

            // Check if request is for an image and referer is external
            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $_SERVER['REQUEST_URI'])) {
                if (!empty($referer) && !strpos($referer, $host)) {
                    // Redirect to placeholder image or return 403
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
            }
        }
    }

    /**
     * Basic rate limiting
     */
    public static function rate_limiting()
    {
        if (!is_admin() && !is_user_logged_in()) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $requests = get_transient('rate_limit_' . $ip) ?: 0;
            $requests++;

            set_transient('rate_limit_' . $ip, $requests, 60); // 1 minute window

            // Allow 100 requests per minute for non-logged users
            if ($requests > 100) {
                header('HTTP/1.1 429 Too Many Requests');
                exit('Rate limit exceeded. Please try again later.');
            }
        }
    }

    /**
     * Log successful login attempts
     */
    public static function log_successful_login($user_login, $user)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = current_time('mysql');

        // Clear failed attempts on successful login
        delete_transient('login_attempts_' . $ip);
        delete_transient('login_blocked_' . $ip);

        // Log successful login for monitoring
        if (WP_DEBUG) {
            error_log("Successful login: User '$user_login' from IP '$ip' at $time");
        }
    }

    /**
     * Protect wp-config.php and other sensitive files
     */
    public static function protect_wp_config()
    {
        // Block direct access to sensitive files
        $request_uri = $_SERVER['REQUEST_URI'];
        $sensitive_files = [
            'wp-config.php',
            'wp-config-sample.php',
            '.htaccess',
            'readme.html',
            'readme.txt',
            'license.txt',
            'wp-admin/install.php',
            'wp-admin/setup-config.php'
        ];

        foreach ($sensitive_files as $file) {
            if (strpos($request_uri, $file) !== false) {
                header('HTTP/1.1 403 Forbidden');
                exit('Access denied');
            }
        }
    }

    /**
     * Disable directory browsing
     */
    public static function disable_directory_browsing()
    {
        // Add index.php files to directories without them
        $directories = [
            WP_CONTENT_DIR . '/uploads',
            WP_CONTENT_DIR . '/themes',
            WP_CONTENT_DIR . '/plugins'
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $index_file = $dir . '/index.php';
                if (!file_exists($index_file)) {
                    file_put_contents($index_file, '<?php // Silence is golden');
                }
            }
        }
    }

    /**
     * Block common malicious requests
     */
    public static function block_malicious_requests()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Malicious patterns
        $malicious_patterns = [
            // SQL injection attempts
            '/union\s+select/i',
            '/concat\s*\(/i',
            '/base64_decode/i',
            '/eval\s*\(/i',

            // XSS attempts
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',

            // File inclusion attempts
            '/\.\.\//',
            '/etc\/passwd/',
            '/proc\/version/',

            // Common attack tools
            '/sqlmap/i',
            '/nmap/i',
            '/nikto/i',
            '/acunetix/i'
        ];

        $content_to_check = $request_uri . ' ' . $query_string . ' ' . $user_agent;

        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $content_to_check)) {
                self::log_security_event('Malicious request blocked', [
                    'pattern' => $pattern,
                    'request_uri' => $request_uri,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);

                header('HTTP/1.1 403 Forbidden');
                exit('Malicious request detected');
            }
        }
    }

    /**
     * Monitor critical file changes
     */
    public static function monitor_file_changes()
    {
        $critical_files = [
            ABSPATH . 'wp-config.php',
            ABSPATH . '.htaccess',
            get_template_directory() . '/functions.php'
        ];

        foreach ($critical_files as $file) {
            if (file_exists($file)) {
                $file_hash = md5_file($file);
                $stored_hash = get_option('file_hash_' . md5($file));

                if ($stored_hash && $stored_hash !== $file_hash) {
                    self::log_security_event('Critical file modified', [
                        'file' => $file,
                        'old_hash' => $stored_hash,
                        'new_hash' => $file_hash
                    ]);
                }

                update_option('file_hash_' . md5($file), $file_hash);
            }
        }
    }

    /**
     * Force SSL for admin
     */
    public static function force_ssl_admin()
    {
        if (!defined('FORCE_SSL_ADMIN')) {
            define('FORCE_SSL_ADMIN', true);
        }
    }

    /**
     * Hide sensitive files from being accessed
     */
    public static function hide_sensitive_files()
    {
        $request = $_SERVER['REQUEST_URI'];

        // Block access to log files
        if (preg_match('/\.(log|txt|md)$/i', $request)) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }

        // Block access to backup files
        if (preg_match('/\.(bak|backup|old|orig|save|tmp)$/i', $request)) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }
    }

    /**
     * Remove security-related headers that give away info
     */
    public static function remove_security_headers($headers)
    {
        unset($headers['X-Pingback']);
        return $headers;
    }

    /**
     * Disable author pages to prevent user enumeration
     */
    public static function disable_author_pages()
    {
        global $wp_query;

        if (is_author()) {
            $wp_query->set_404();
            status_header(404);
        }
    }

    /**
     * Add security-related meta tags
     */
    public static function add_security_meta_tags()
    {
        if (!is_admin()) {
            echo '<meta name="robots" content="noarchive">' . "\n";
            echo '<meta http-equiv="X-DNS-Prefetch-Control" content="off">' . "\n";
        }
    }

    /**
     * Protect against SQL injection in URL parameters
     */
    public static function protect_against_sql_injection()
    {
        $suspicious_patterns = [
            '/union.*select/i',
            '/select.*from/i',
            '/insert.*into/i',
            '/update.*set/i',
            '/delete.*from/i',
            '/drop.*table/i',
            '/alter.*table/i',
            '/create.*table/i'
        ];

        $query_string = $_SERVER['QUERY_STRING'] ?? '';

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $query_string)) {
                self::log_security_event('SQL injection attempt', [
                    'query_string' => $query_string,
                    'pattern' => $pattern,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);

                header('HTTP/1.1 403 Forbidden');
                exit('Malicious query detected');
            }
        }
    }

    /**
     * Admin security measures
     */
    public static function admin_security_measures()
    {
        // Hide WordPress version in admin
        add_filter('admin_footer_text', function () {
            return '';
        });

        // Remove WordPress version from admin
        add_filter('update_footer', function () {
            return '';
        });

        // Disable plugin/theme editor
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }

        // Disable plugin/theme installation
        if (!defined('DISALLOW_FILE_MODS')) {
            define('DISALLOW_FILE_MODS', true);
        }
    }

    /**
     * IP-based security measures
     */
    public static function ip_based_security()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        // Check against blocked IPs
        $blocked_ips = get_option('blocked_ips', []);
        if (in_array($ip, $blocked_ips)) {
            header('HTTP/1.1 403 Forbidden');
            exit('IP address blocked');
        }

        // Rate limiting per IP for admin access
        if (is_admin() && !current_user_can('administrator')) {
            $admin_requests = get_transient('admin_requests_' . $ip) ?: 0;
            $admin_requests++;

            set_transient('admin_requests_' . $ip, $admin_requests, 300); // 5 minute window

            if ($admin_requests > 20) {
                self::log_security_event('Excessive admin access attempts', [
                    'ip' => $ip,
                    'requests' => $admin_requests
                ]);

                header('HTTP/1.1 429 Too Many Requests');
                exit('Too many admin requests');
            }
        }
    }

    /**
     * Database security measures
     */
    public static function database_security()
    {
        global $wpdb;

        // Change database table prefix if still default
        if ($wpdb->prefix === 'wp_') {
            self::log_security_event('Warning: Using default table prefix', [
                'recommendation' => 'Change wp_ prefix to something unique'
            ]);
        }

        // Monitor for suspicious database queries (basic)
        if (WP_DEBUG && defined('SAVEQUERIES') && SAVEQUERIES) {
            add_action('shutdown', function () {
                global $wpdb;

                if (!empty($wpdb->queries)) {
                    foreach ($wpdb->queries as $query) {
                        $sql = $query[0];

                        // Check for suspicious queries
                        if (preg_match('/information_schema|mysql\.|pg_|sys\./i', $sql)) {
                            self::log_security_event('Suspicious database query', [
                                'query' => $sql,
                                'backtrace' => wp_debug_backtrace_summary()
                            ]);
                        }
                    }
                }
            });
        }
    }

    /**
     * Log security events
     */
    private static function log_security_event($event, $data = [])
    {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data
        ];

        // Log to WordPress debug log
        if (WP_DEBUG_LOG) {
            error_log('SECURITY EVENT: ' . json_encode($log_entry));
        }

        // Store in database for analysis (optional)
        $security_logs = get_option('security_logs', []);
        $security_logs[] = $log_entry;

        // Keep only last 100 entries
        if (count($security_logs) > 100) {
            $security_logs = array_slice($security_logs, -100);
        }

        update_option('security_logs', $security_logs);
    }

    /**
     * Get security logs for admin review
     */
    public static function get_security_logs($limit = 50)
    {
        $logs = get_option('security_logs', []);
        return array_slice($logs, -$limit);
    }

    /**
     * Block IP address permanently
     */
    public static function block_ip($ip)
    {
        $blocked_ips = get_option('blocked_ips', []);
        if (!in_array($ip, $blocked_ips)) {
            $blocked_ips[] = $ip;
            update_option('blocked_ips', $blocked_ips);

            self::log_security_event('IP address blocked', ['ip' => $ip]);
        }
    }

    /**
     * Unblock IP address
     */
    public static function unblock_ip($ip)
    {
        $blocked_ips = get_option('blocked_ips', []);
        $key = array_search($ip, $blocked_ips);

        if ($key !== false) {
            unset($blocked_ips[$key]);
            update_option('blocked_ips', array_values($blocked_ips));

            self::log_security_event('IP address unblocked', ['ip' => $ip]);
        }
    }
}

// Initialize advanced security hardening
ThemeSecurity::init();