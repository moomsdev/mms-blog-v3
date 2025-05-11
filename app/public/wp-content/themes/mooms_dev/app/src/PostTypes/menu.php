<?php

namespace App\PostTypes;

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field;
use mms\Abstracts\AbstractPostType;

class menu extends \App\Abstracts\AbstractPostType
{

    public function __construct()
    {
        $this->showThumbnailOnList = true;
        $this->supports            = [
            'title',
            'thumbnail',
        ];

        $this->menuIcon         = 'dashicons-clipboard'; //https://developer.wordpress.org/resource/dashicons/
        // $this->menuIcon = get_template_directory_uri() . '/images/custom-icon.png';
        $this->post_type        = 'menu';
        $this->singularName     = $this->pluralName = __('Menu', 'mms');
        $this->titlePlaceHolder = __('Menu', 'mms');
        $this->slug             = 'menus';
        parent::__construct();
    }

    /**
     * Document: https://docs.carbonfields.net/#/containers/post-meta
     */
    public function metaFields()
    {
        Container::make('post_meta', __('Information | Thông tin', 'mms'))
            ->set_context('carbon_fields_after_title')
            ->set_priority('high')
            ->where('post_type', 'IN', [$this->post_type])
            ->add_fields([
                Field::make('text', 'price', __('Price | Giá', 'mms'))->set_width(30),
                Field::make('textarea', 'food_desc', __('Description | Mô tả', 'mms'))->set_width(70),
            ]);
    }
}
