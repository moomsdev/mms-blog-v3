<?php
/**
 * Security Configuration
 * @package MoomsDev
 */

if (!defined('ABSPATH')) {
    exit;
}

class ThemeSecurity {
    public static function init() {
        self::setup_constants();
        self::setup_upload_security();
        self::remove_wp_vulnerabilities();
    }
    
    private static function setup_constants() {
        define('SUPER_USER', ['mooms.dev']);
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }
    
    private static function setup_upload_security() {
        add_filter('upload_mimes', function($mimes) {
            unset($mimes['exe'], $mimes['php'], $mimes['php3']);
            return $mimes;
        });
    }
    
    private static function remove_wp_vulnerabilities() {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        add_filter('xmlrpc_enabled', '__return_false');
    }
}

ThemeSecurity::init(); 