<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Block Menu', 'mms'))
    ->add_fields([
        Field::make('separator', 'menu_spt', __('BLOCK MENU', 'mms')),
        //Title
        Field::make('text', 'menu_title', __('', 'mms'))
            ->set_attribute('placeholder', 'Nhập tiêu đề của block'),

        //Description
        Field::make('textarea', 'menu_desc', __('', 'mms'))
            ->set_attribute('placeholder', 'Nhập mô tả của block'),
    ])
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $title = !empty($fields['menu_title']) ? esc_html($fields['menu_title']) : '';
        $desc = !empty($fields['menu_desc']) ? wp_kses_post($fields['menu_desc']) : '';
?>
    <section class="menu-block">
        <div class="container">
            <div class="row mb-40">
                <div class="col-12">
                    <div class="section-heading">
                        <?php
                        if ($title) :
                            echo '<h2 class="block-title">' . $title . '</h2>';
                        endif;
                        ?>

                        <?php
                        if ($desc) :
                            echo '<div class="desc">' . $desc . '</div>';
                        endif;
                        ?>
                    </div>
                </div>
                <div class="col-12 menu-list">
                    <ul class="menu-filter">
                        <li class="active" data-filter="*">All</li>
                        <?php
                        $categories = get_terms('menu_cat', [
                            'hide_empty' => true,
                            'parent' => 0,
                        ]);
                        foreach ($categories as $category) :
                            echo '<li data-filter=".' . sanitize_title($category->name) . '">' . $category->name . '</li>';
                        endforeach;
                        ?>
                    </ul>
                </div>
            </div>
            <div class="menu-items full-width">
                <div class="container">
                    <div class="row menu-wrapper">
                            <?php
                            $post_query = new WP_Query([
                                'post_type' => 'menu',
                                'posts_per_page' => -1,
                                'post_status' => 'publish',
                            ]);
                            if ($post_query->have_posts()) :
                                while ($post_query->have_posts()) : $post_query->the_post();
                                    $categories = wp_get_post_terms(get_the_ID(), 'menu_cat');
                            ?>
                                    <div class="col-12 col-sm-6 loop-food mb-5 <?php foreach ($categories as $category) echo sanitize_title($category->name) . ' ' ?>">
                                        <div class="menu-wrap">
                                            <div class="menu-img">
                                                <img src="<?= getPostThumbnailUrl(get_the_ID()); ?>" alt="<?= get_the_title(); ?>">
                                            </div>
                                            <div class="food-info">
                                                <h3 class="heading"><?= get_the_title(); ?></h3>
                                                <div class="food-desc"><?= getPostMeta('food_desc'); ?></div>
                                                <div class="price"><?= getPostMeta('price'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                            <?php
                                endwhile;
                            endif;
                            wp_reset_postdata();
                            wp_reset_query();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
    });
