<?php

use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;

// =============================================================================
// ARRAY & STRING MANIPULATION
// =============================================================================

function insertArrayAtPosition($array, $insert, $position)
{
    return array_slice($array, 0, $position, true) + $insert + array_slice($array, $position, null, true);
}

function subString($str, $limit)
{
    return wp_trim_words($str, $limit, '...');
}

// =============================================================================
// LANGUAGE & INTERNATIONALIZATION
// =============================================================================

function currentLanguage()
{
    return defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '';
}

// =============================================================================
// ASSETS & RESOURCES
// =============================================================================

function adminAsset($path)
{
    return get_stylesheet_directory_uri() . '/resources/admin/' . $path;
}

function loadStyles($files = [])
{
    add_action('wp_enqueue_scripts', function () use ($files) {
        $theme_version = wp_get_theme()->get('Version');
        foreach ($files as $index => $file) {
            wp_enqueue_style('theme-css-' . $index, $file, [], $theme_version);
        }
        wp_enqueue_style('theme-style', get_stylesheet_directory_uri() . '/style.css', [], $theme_version);
    });
}

function loadScripts($files = [])
{
    add_action('wp_enqueue_scripts', function () use ($files) {
        $theme_version = wp_get_theme()->get('Version');
        foreach ($files as $index => $file) {
            wp_enqueue_script('theme-js-' . $index, $file, [], $theme_version, true);
        }
    });
}

// =============================================================================
// META & POST MANAGEMENT
// =============================================================================

function updatePostMeta($post_id, $field_name, $value = '')
{
    if (empty($value)) {
        return delete_post_meta($post_id, $field_name);
    }
    return update_post_meta($post_id, $field_name, $value) ?: add_post_meta($post_id, $field_name, $value);
}

function updateUserMeta($idUser, $key, $value)
{
    if (empty($value)) {
        return delete_user_meta($idUser, $key);
    }
    return update_user_meta($idUser, $key, $value) ?: add_user_meta($idUser, $key, $value);
}

// =============================================================================
// IMAGE HANDLING
// =============================================================================

function updateAttachmentSize($attachment_id, $fileName, $width, $height, $type)
{
    $metadata = wp_get_attachment_metadata($attachment_id);
    if (is_array($metadata) && array_key_exists('sizes', $metadata)) {
        $size = $metadata['sizes'];
        $sizeName = $width . 'x' . $height;
        if (!array_key_exists($sizeName, $size)) {
            $metadata['sizes'][$sizeName] = [
                'file' => $fileName,
                'width' => $width,
                'height' => $height,
                'mime-type' => $type,
            ];
        }
        wp_update_attachment_metadata($attachment_id, $metadata);
    }
}

function resizeImage($srcPath, $destinationPath, $maxWidth, $maxHeight, $type = 'webp')
{
    try {
        $image = Image::make($srcPath);
        if ($maxWidth || $maxHeight) {
            $image->fit($maxWidth, $maxHeight, static function ($constraint) {
                $constraint->upsize();
            });
        }
        $image->encode($type)->save($destinationPath, 85);
    } catch (\Exception $ex) {
        error_log($ex->getMessage());
    }
}

function getImageUrlById($attachment_id, $width = null, $height = null)
{
    if ($width === null && $height === null) {
        return wp_get_attachment_image_url($attachment_id, 'full');
    }

    $width = $width ? absint($width) : 0;
    $height = $height ? absint($height) : 0;
    $upload_dir = wp_upload_dir();
    $attachment_realpath = crb_normalize_path(get_attached_file($attachment_id));

    if (empty($attachment_realpath)) {
        return "https://via.placeholder.com/{$width}x{$height}";
    }

    $filename = basename($attachment_realpath);
    $fileParts = explode('.', $filename);
    $fileExt = $fileParts[count($fileParts) - 1];
    if (in_array($fileExt, ['gif', 'svg'])) {
        return wp_get_attachment_image_url($attachment_id, 'full');
    }

    $filename = preg_replace('/(\.[^\.]+)$/', '-' . $width . 'x' . $height, $filename);
    $filepath = crb_normalize_path($upload_dir['basedir'] . '/' . $filename);
    $url = trailingslashit($upload_dir['baseurl']) . $filename;

    if (!file_exists($filepath)) {
        return wp_get_attachment_image_url($attachment_id, 'full');
    }

    return $url;
}

function resizeImageFly($url, $width = null, $height = null, $crop = true, $retina = false)
{
    global $wpdb;
    if (empty($url)) {
        return new WP_Error('no_image_url', __('No image URL has been entered.', 'wta'), $url);
    }

    $width = $width ?: get_option('thumbnail_size_w');
    $height = $height ?: get_option('thumbnail_size_h');
    $retina = $retina ? ($retina === true ? 2 : $retina) : 1;

    $file_path = parse_url($url);
    $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

    if (is_multisite()) {
        global $blog_id;
        $blog_details = get_blog_details($blog_id);
        $file_path = str_replace($blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path);
    }

    $dest_width = $width * $retina;
    $dest_height = $height * $retina;
    $suffix = "{$dest_width}x{$dest_height}";

    $info = pathinfo($file_path);
    $dir = $info['dirname'];
    $ext = $info['extension'];
    $name = wp_basename($file_path, ".$ext");

    if ('bmp' === $ext) {
        return new WP_Error('bmp_mime_type', __('Image is BMP. Please use either JPG or PNG.', 'wta'), $url);
    }

    $dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

    if (!file_exists($dest_file_name)) {
        $query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE guid='%s'", $url);
        $get_attachment = $wpdb->get_results($query);
        if (!$get_attachment) {
            return ['url' => $url, 'width' => $width, 'height' => $height];
        }

        $editor = wp_get_image_editor($file_path);
        if (is_wp_error($editor)) {
            return ['url' => $url, 'width' => $width, 'height' => $height];
        }

        $size = $editor->get_size();
        $orig_width = $size['width'];
        $orig_height = $size['height'];
        $src_x = $src_y = 0;
        $src_w = $orig_width;
        $src_h = $orig_height;

        if ($crop) {
            $cmp_x = $orig_width / $dest_width;
            $cmp_y = $orig_height / $dest_height;
            if ($cmp_x > $cmp_y) {
                $src_w = round($orig_width / $cmp_x * $cmp_y);
                $src_x = round(($orig_width - ($orig_width / $cmp_x * $cmp_y)) / 2);
            } else if ($cmp_y > $cmp_x) {
                $src_h = round($orig_height / $cmp_y * $cmp_x);
                $src_y = round(($orig_height - ($orig_height / $cmp_y * $cmp_x)) / 2);
            }
        }

        $editor->crop($src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height);
        $saved = $editor->save($dest_file_name);

        $resized_url = str_replace(basename($url), basename($saved['path']), $url);
        $resized_width = $saved['width'];
        $resized_height = $saved['height'];
        $resized_type = $saved['mime-type'];

        $metadata = wp_get_attachment_metadata($get_attachment[0]->ID);
        if (isset($metadata['image_meta'])) {
            $metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
            wp_update_attachment_metadata($get_attachment[0]->ID, $metadata);
        }

        $image_array = [
            'url' => $resized_url,
            'width' => $resized_width,
            'height' => $resized_height,
            'type' => $resized_type,
        ];
    } else {
        $image_array = [
            'url' => str_replace(basename($url), basename($dest_file_name), $url),
            'width' => $dest_width,
            'height' => $dest_height,
            'type' => $ext,
        ];
    }

    return $image_array;
}

// =============================================================================
// POST QUERIES & RELATED CONTENT
// =============================================================================

function getRelatePosts($postId = null, $postCount = null)
{
    global $post;
    $postCount = $postCount ?: get_option('posts_per_page');
    $thisPost = $postId ? get_post($postId) : $post;

    $taxonomies = get_post_taxonomies($thisPost->ID);
    $arrTaxQuery = ['relation' => 'OR'];
    foreach ($taxonomies as $taxonomy) {
        $terms = get_the_terms($thisPost->ID, $taxonomy);
        if ($terms) {
            $arrTaxQuery[] = [
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => wp_list_pluck($terms, 'term_id'),
            ];
        }
    }

    return new WP_Query([
        'post_type' => $thisPost->post_type,
        'post_status' => 'publish',
        'posts_per_page' => $postCount,
        'post__not_in' => [$thisPost->ID],
        'tax_query' => $arrTaxQuery,
    ]);
}

function getLatestPosts($postType = 'post', $postCount = null)
{
    return new WP_Query([
        'post_type' => $postType,
        'post_status' => 'publish',
        'posts_per_page' => $postCount ?: get_option('posts_per_page'),
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}

function getTopViewPosts($postType = 'post', $postCount = null)
{
    return new WP_Query([
        'post_type' => $postType,
        'post_status' => 'publish',
        'posts_per_page' => $postCount ?: get_option('posts_per_page'),
        'meta_key' => '_gm_view_count',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
    ]);
}

function getListAllPages()
{
    $pages = get_posts([
        'post_type' => 'page',
        'posts_per_page' => -1,
        'lang' => get_icl_language_code(),
    ]);

    $list = [];
    foreach ($pages as $page) {
        $list[$page->ID] = $page->post_title;
    }

    return $list;
}

// =============================================================================
// TIME & DATE FORMATTING
// =============================================================================

function formatHumanTime($time)
{
    $diff = Carbon::now()->diffInSeconds(Carbon::parse($time));
    if ($diff < 60)
        return __('Vừa mới đây', 'mms');
    if ($diff < 3600)
        return sprintf(__('Khoảng %d phút trước', 'mms'), round($diff / 60));
    if ($diff < 86400)
        return sprintf(__('Khoảng %d giờ trước', 'mms'), round($diff / 3600));
    if ($diff < 604800)
        return sprintf(__('Khoảng %d ngày trước', 'mms'), round($diff / 86400));
    return sprintf(__('Khoảng %d tuần trước', 'mms'), round($diff / 604800));
}

// =============================================================================
// VIDEO HANDLING
// =============================================================================

function getYoutubeEmbedUrl($url)
{
    $youtube_id = '';
    if (preg_match('/(youtube\.com.*(\?v=|\/embed\/|\/v\/|\/.+\/|youtu\.be\/|\/v\/)|\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $youtube_id = $matches[3];
    }

    if (!empty($youtube_id)) {
        return 'https://www.youtube.com/embed/' . $youtube_id . '?modestbranding=1&showinfo=0&controls=1&frameborder=0&allow=accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture&allowfullscreen';
    }

    return '';
}

function getVideoUrl($video_link)
{
    $video_html = '';

    if (!empty($video_link)) {
        if (strpos($video_link, 'youtube.com') !== false || strpos($video_link, 'youtu.be') !== false) {
            $youtube_embed_url = getYoutubeEmbedUrl($video_link);
            if (!empty($youtube_embed_url)) {
                $video_html = '<div class="video-embed"><iframe title="YouTube video" src="' . $youtube_embed_url . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
            }
        } elseif (strpos($video_link, 'vimeo.com') !== false) {
            $video_ID = substr(parse_url($video_link, PHP_URL_PATH), 1);
            $vimeo_api_url = "https://vimeo.com/api/v2/video/{$video_ID}.json";

            $hash = @file_get_contents($vimeo_api_url);
            if ($hash) {
                $hash_data = json_decode($hash);
                if (isset($hash_data[0])) {
                    $title = $hash_data[0]->title;
                    $video_html = '<div class="video-embed"><iframe title="Video: ' . $title . '" src="https://player.vimeo.com/video/' . $video_ID . '" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe></div>';
                }
            }
        }
    }

    return $video_html;
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

function crb_normalize_path($path)
{
    return preg_replace('~[/' . preg_quote('\\', '~') . ']~', DIRECTORY_SEPARATOR, $path);
}

// =============================================================================
// PERFORMANCE OPTIMIZATIONS
// =============================================================================

function contactform_dequeue_scripts()
{
    if (!is_singular() || !has_shortcode(get_post()->post_content, 'contact-form-7')) {
        wp_dequeue_script('contact-form-7');
        wp_dequeue_script('google-recaptcha');
        wp_dequeue_style('contact-form-7');
    }
}
add_action('wp_enqueue_scripts', 'contactform_dequeue_scripts', 99);

add_action('wp_default_scripts', function ($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
});

// =============================================================================
// CONTACT FORM 7 SPAM PROTECTION
// =============================================================================

add_filter('wpcf7_form_elements', 'moomsdev_check_spam_form_cf7');
function moomsdev_check_spam_form_cf7($html)
{
    $html = '<div style="display: none"><p><span class="wpcf7-form-control-wrap" data-name="moomsdev"><input size="40" class="wpcf7-form-control wpcf7-text" aria-invalid="false" value="" type="text" name="moomsdev"></span></p></div>' . $html;
    return $html;
}

add_action('wpcf7_posted_data', 'mms_check_spam_form_cf7_vaild');
function mms_check_spam_form_cf7_vaild($posted_data)
{
    $submission = WPCF7_Submission::get_instance();
    if (!empty($posted_data['moomsdev'])) {
        $submission->set_status('spam');
        $submission->set_response('You are Spamer');
    }
    unset($posted_data['moomsdev']);
    return $posted_data;
}

// Enable excerpt for pages
add_post_type_support('page', 'excerpt');
