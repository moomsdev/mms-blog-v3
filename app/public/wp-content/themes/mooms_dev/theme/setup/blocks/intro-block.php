<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

$addLabels = array(
    'plural_name' => 'media',
    'singular_name' => 'media',
);

Block::make(__('Block Giới Thiệu', 'mms'))
    ->add_fields([
        Field::make('separator', 'intro_spt', __('BLOCK GIỚI THIỆU', 'mms'))->set_width(70),
        //Title
        Field::make('text', 'intro_title', __('', 'mms'))
            ->set_attribute('placeholder', 'Nhập tiêu đề của block'),
        //Description
        Field::make('textarea', 'intro_desc', __('', 'mms'))
            ->set_attribute('placeholder', 'Nhập mô tả của block'),
        Field::make('complex', 'intro_item', __('', 'mms'))
            ->set_layout('tabbed-vertical')
            ->add_fields([
                Field::make('text', 'year', __('Năm', 'mms'))->set_width(30),
                Field::make('text', 'title', __('Tiêu đề', 'mms'))->set_width(70),
                Field::make('rich_text', 'description', __('Mô tả', 'mms')),
            ])->set_header_template('<% if (title) { %><%- title %><% } %>'),
    ])
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $title = !empty($fields['intro_title']) ? esc_html($fields['intro_title']) : '';
        $desc = !empty($fields['intro_desc']) ? wp_kses_post($fields['intro_desc']) : '';
        $items = !empty($fields['intro_item']) ? $fields['intro_item'] : '';
?>
    <section class="intro-block">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="main-content">
                        <h2 class="block-title"> <?= $title; ?> </h2>
                        <div class="desc">
                            <?= apply_filters('the_content', $desc); ?>
                        </div>
                    </div>
                </div>
                <?php
                foreach ($items as $item) :
                    $year = $item['year'];
                    $title = $item['title'];
                    $description = $item['description'];
                ?>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="subheading">
                                <?= $year; ?>
                            </div>
                            <h3 class="heading line">
                                <?= $title; ?>
                            </h3>
                            <div class="desc">
                                <?= apply_filters('the_content', $description); ?>
                            </div>
                        </div>
                    </div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
    </section>
<?php
    });
