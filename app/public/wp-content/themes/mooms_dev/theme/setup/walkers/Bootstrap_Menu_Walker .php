<?php

if (!defined('ABSPATH')) {
    exit;
}

class Bootstrap_Menu_Walker extends Walker_Nav_Menu
{
    /**
     * Bắt đầu cấp độ menu con <ul>
     */
    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }

    /**
     * Bắt đầu một mục menu <li>
     */
    public function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        // Các lớp CSS mặc định cho item
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        // Kiểm tra nếu item có menu con
        if (!empty($args->has_children)) {
            $classes[] = 'has-submenu';
        }

        // Lọc class CSS
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        // ID của menu item
        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        // Tạo phần mở đầu <li>
        $output .= $indent . '<li' . $id . $class_names . '>';

        // Tạo thuộc tính cho thẻ <a>
        $atts = [];
        $atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target) ? $item->target : '';
        $atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
        $atts['href']   = !empty($item->url) ? $item->url : '#';

        // Nếu item có submenu, thêm thuộc tính dropdown
        if (!empty($args->has_children)) {
            $atts['class'] = 'menu-link has-dropdown';
        } else {
            $atts['class'] = 'menu-link';
        }

        // Lọc thuộc tính của thẻ <a>
        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

        // Tạo chuỗi thuộc tính
        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $attributes .= ' ' . $attr . '="' . esc_attr($value) . '"';
            }
        }

        // Nội dung của menu item
        $title = apply_filters('the_title', $item->title, $item->ID);
        $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

        // Tạo output cho mục menu
        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . $title . $args->link_after;

        // Nếu có submenu, thêm dấu mũi tên
        if (!empty($args->has_children)) {
            $item_output .= '<span class="span-drop"></span>';
        }

        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    /**
     * Kết thúc một mục menu <li>
     */
    public function end_el(&$output, $item, $depth = 0, $args = [])
    {
        $output .= "</li>\n";
    }

    /**
     * Kiểm tra và hiển thị submenu
     */
    public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output)
    {
        if (!$element) {
            return;
        }

        // Kiểm tra nếu có submenu
        $id_field = $this->db_fields['id'];
        if (isset($children_elements[$element->$id_field])) {
            $args[0]->has_children = true;
        }

        parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
    }
}
