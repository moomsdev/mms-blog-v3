<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

$addLabels = array(
    'plural_name' => 'media',
    'singular_name' => 'media',
);

Block::make(__('Block specials menu', 'mms'))
    ->add_fields([
        Field::make('separator', 'blog_spt', __('BLOCK MENU ĐẶC BIỆT', 'mms')),
        //Title
        Field::make('text', 'specials_title', __('', 'mms'))
            ->set_attribute('placeholder', 'Nhập tiêu đề của block'),
        //Description
        Field::make('textarea', 'specials_desc', __('', 'mms'))
            ->set_attribute('placeholder', 'Nhập mô tả của block'),
        //Manual
        Field::make('association', 'specials_menu', __('Chọn menu', 'mms'))
            ->set_types([
                [
                    'type'      => 'post',
                    'post_type' => 'menu',
                ]
            ]),
    ])
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $title = !empty($fields['specials_title']) ? esc_html($fields['specials_title']) : '';
        $desc = !empty($fields['specials_desc']) ? wp_kses_post($fields['specials_desc']) : '';
        $specials_menu = !empty($fields['specials_menu']) ? $fields['specials_menu'] : '';
?>
    <section class="specials-menu full-width">
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
            </div>

            <div class="row menu-wrapper-2">
                    <?php
                    foreach ($specials_menu as $food) :
                    ?>
                        <div class="col-12 col-sm-6 loop-food mb-5">
                            <div class="menu-wrap">
                                <div class="menu-img">
                                    <img src="<?= getPostThumbnailUrl($food['id']); ?>" alt="<?= get_the_title($food['id']); ?>">
                                </div>
                                <div class="food-info">
                                    <h3 class="heading"><?= get_the_title($food['id']); ?></h3>
                                    <div class="food-desc"><?= getPostMeta('food_desc', $food['id']); ?></div>
                                    <div class="price"><?= getPostMeta('price', $food['id']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </section>
<?php
    });
