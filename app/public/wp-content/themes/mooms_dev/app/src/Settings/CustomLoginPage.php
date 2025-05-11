<?php

namespace App\Settings;

class CustomLoginPage
{

    public function __construct()
    {
        add_action('login_enqueue_scripts', static function () {
            // wp_enqueue_style('custom-login', adminAsset('css/login.css'));
            wp_enqueue_script('custom-login', adminAsset('js/login.js'));
        });
    }
}
