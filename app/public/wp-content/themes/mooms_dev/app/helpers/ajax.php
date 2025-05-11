<?php

// =============================================================================
// AJAX HANDLERS - CUSTOM SORT, THUMBNAIL, CONTACT FORM, LOAD PAGE
// =============================================================================

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// AJAX: Update Custom Sort Order
// -----------------------------------------------------------------------------
/**
 * Cập nhật thứ tự sắp xếp (menu_order) cho các post qua Ajax.
 *
 * @action wp_ajax_update_custom_sort_order
 */
add_action('wp_ajax_update_custom_sort_order', 'updateCustomSortOrder');
function updateCustomSortOrder() {
    // Kiểm tra tham số đầu vào
    if (empty($_POST['post_ids']) || empty($_POST['current_page'])) {
        wp_send_json_error(['message' => 'Missing parameters.']);
    }

    $postIds = array_map('absint', $_POST['post_ids']);
    $currentPage = absint($_POST['current_page']);
    $order = (($currentPage - 1) * count($postIds)) + 1;

    // Cập nhật menu_order cho từng post
    foreach ($postIds as $postId) {
        wp_update_post([
            'ID'         => $postId,
            'menu_order' => $order,
        ]);
        $order++;
    }

    wp_send_json_success();
}

// -----------------------------------------------------------------------------
// AJAX: Update Post Thumbnail ID
// -----------------------------------------------------------------------------
/**
 * Cập nhật thumbnail (ảnh đại diện) cho post qua Ajax.
 *
 * @action wp_ajax_nopriv_update_post_thumbnail_id
 * @action wp_ajax_update_post_thumbnail_id
 */
add_action('wp_ajax_nopriv_update_post_thumbnail_id', 'updatePostThumbnailId');
add_action('wp_ajax_update_post_thumbnail_id', 'updatePostThumbnailId');

function updatePostThumbnailId() {
    // Kiểm tra các tham số post_id và attachment_id
    if (empty($_POST['post_id']) || empty($_POST['attachment_id'])) {
        wp_send_json_error(['message' => 'Missing parameters.']);
    }

    $postId = absint($_POST['post_id']);
    $attachmentId = absint($_POST['attachment_id']);

    // Cập nhật _thumbnail_id bằng hàm update_post_meta
    if (update_post_meta($postId, '_thumbnail_id', $attachmentId)) {
        wp_send_json_success(['message' => 'Thumbnail updated.']);
    } else {
        wp_send_json_error(['message' => 'Failed to update thumbnail.']);
    }
}

// -----------------------------------------------------------------------------
// AJAX: Gửi form liên hệ (Contact Form)
// -----------------------------------------------------------------------------
/**
 * Xử lý gửi form liên hệ qua Ajax, gửi email đến quản trị viên.
 *
 * @action wp_ajax_nopriv_send_contact_form
 * @action wp_ajax_send_contact_form
 */
add_action('wp_ajax_nopriv_send_contact_form', 'sendContactForm');
add_action('wp_ajax_send_contact_form', 'sendContactForm');

function sendContactForm() {
    // Kiểm tra nonce để bảo mật
    if (!check_ajax_referer('send_contact_form', '_token', false)) {
        wp_send_json_error(['message' => __('Token mistake.')]);
    }

    // Kiểm tra các trường bắt buộc
    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['phone_number']) || empty($_POST['message'])) {
        wp_send_json_error(['message' => __('Please fill in all required fields.', 'mms')]);
    }

    // Lấy thông tin từ form
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $message = sanitize_textarea_field($_POST['message']);

    // Lấy thông tin blog
    $blogName = get_bloginfo('name');
    $blogUrl = get_bloginfo('url');

    // Nội dung email
    $html = sprintf(
        '<p>Send from: %s %s (%s)</p><p>Contact phone number: %s</p><p>Contact message:</p><p>%s</p>',
        esc_html($first_name),
        esc_html($last_name),
        esc_html($email),
        esc_html($phone_number),
        esc_html($message)
    );

    // Thiết lập header
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . esc_html($first_name . ' ' . $last_name) . ' <' . sanitize_email($email) . '>',
    ];

    // Gửi email đến quản trị viên
    $success = wp_mail(get_option('admin_email'), $blogName . ': New Contact Form Submission', $html, $headers);

    // Kiểm tra kết quả gửi email và phản hồi JSON
    if ($success) {
        wp_send_json_success(['message' => __('Your request has been successfully submitted.', 'mms')]);
    } else {
        // Ghi lại log nếu gửi email thất bại
        error_log('Email failed to send.');
        wp_send_json_error(['message' => __('An error occurred. Please try again later.', 'mms')]);
    }
}

// -----------------------------------------------------------------------------
// AJAX: Load Page Content
// -----------------------------------------------------------------------------
/**
 * Tải nội dung trang qua Ajax (dùng cho các yêu cầu động).
 *
 * @action wp_ajax_nopriv_get_page
 * @action wp_ajax_get_page
 */
add_action('wp_ajax_nopriv_get_page', 'ajaxGetPage');
add_action('wp_ajax_get_page', 'ajaxGetPage');

function ajaxGetPage() {
    ob_start();
    get_template_part('page');
    $content = ob_get_clean();
    wp_send_json_success($content);
}
