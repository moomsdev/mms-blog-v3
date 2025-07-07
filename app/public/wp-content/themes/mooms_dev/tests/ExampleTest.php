<?php

/**
 * Class ExampleTest
 *
 * @package MoomsDevTheme
 */

/**
 * Sample test case.
 */
class ExampleTest extends WP_UnitTestCase
{

    /**
     * A single example test.
     */
    public function test_sample()
    {
        // Test theme is active
        $this->assertEquals('mooms_dev', get_template());
    }

    /**
     * Test WordPress functions are available
     */
    public function test_wordpress_loaded()
    {
        $this->assertTrue(function_exists('wp_head'));
        $this->assertTrue(function_exists('wp_footer'));
        $this->assertTrue(function_exists('get_header'));
        $this->assertTrue(function_exists('get_footer'));
    }

    /**
     * Test theme supports
     */
    public function test_theme_supports()
    {
        $this->assertTrue(current_theme_supports('post-thumbnails'));
        $this->assertTrue(current_theme_supports('menus'));
    }

    /**
     * Test theme enqueue scripts and styles
     */
    public function test_theme_enqueue()
    {
        // Simulate front-end
        $this->go_to(home_url('/'));

        // Trigger wp_enqueue_scripts action
        do_action('wp_enqueue_scripts');

        // Check if theme styles/scripts are enqueued
        $this->assertTrue(wp_style_is('app-styles', 'enqueued') || wp_style_is('theme-style', 'enqueued'));
    }

    /**
     * Test custom functions exist
     */
    public function test_theme_functions()
    {
        // Test if theme functions are properly loaded
        $this->assertTrue(function_exists('app'));
    }
}