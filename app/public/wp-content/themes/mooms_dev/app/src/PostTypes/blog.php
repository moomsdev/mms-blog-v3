<?php

namespace App\PostTypes;

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field;
use mms\Abstracts\AbstractPostType;

class blog extends \App\Abstracts\AbstractPostType
{

    public function __construct() {
        $this->showThumbnailOnList = true;
        $this->supports            = [
            'title',
            'editor',
            'thumbnail',
            'page-attributes',
        ];

        $this->menuIcon         = 'dashicons-admin-post'; //https://developer.wordpress.org/resource/dashicons/
        // $this->menuIcon = get_template_directory_uri() . '/images/custom-icon.png';
        $this->post_type        = 'blog';
        $this->singularName     = $this->pluralName = __('Blog', 'mms'); 
        $this->titlePlaceHolder = __('Post', 'mms');
        $this->slug             = 'blogs';
        parent::__construct();
    }
}
