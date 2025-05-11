<?php

use Overtrue\Socialite\SocialiteManager;

add_action('wp_ajax_nopriv_user_login', 'mm_user_login');
add_action('wp_ajax_user_login', 'mm_user_login');
function mm_user_login()
{
    if (empty($_POST)) {
        return '';
    }

    if (!wp_verify_nonce($_POST['_token'], 'user_dang_nhap')) {
        return __('Token mismatch!', 'mms');
    }

    if (empty($_POST['user_login']) || empty($_POST['password'])) {
        return __('Tài khoản hoặc mật khẩu không đúng, vui lòng kiểm tra lại', 'mms');
    }

    $user = wp_signon([
        'user_login'    => $_POST['user_login'],
        'user_password' => $_POST['password'],
        'remember'      => true,
    ], false);

    if (is_wp_error($user)) {
        return $user->get_error_message();
    }

    echo '<script>localStorage.setItem("show_alert", JSON.stringify({title: "' . __('Xin chào, ', 'mms') . $user->user_email . '", message: "Chúc mừng bạn đã đăng nhập thành công"})); window.location.href = "' . $_POST['redirect_to'] . '";</script>';
    return '';
}

add_action('wp_ajax_nopriv_user_register', 'mm_user_register');
add_action('wp_ajax_user_register', 'mm_user_register');
function mm_user_register()
{
    if (empty($_POST)) {
        return '';
    }

    /* Kiem tra captcha */
    //    $captcha = $_POST['g-recaptcha-response'];
    //    if (empty($captcha)) return [
    //      'status'   => false,
    //      'loi_nhan' => __("Bạn chưa nhập mã xác nhận (chọn vào I'm not robot)", 'mtdev'),
    //    ];
    //    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfIuzYUAAAAADoy5KWNcnYkDumOexP1apz9Vv3v&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
    //    $response = json_decode($response, true);
    //    if (!$response['success']) return [
    //      'status'   => 'alert-success',
    //      'title'    => 'Cảnh báo',
    //      'loi_nhan' => __("Mã xác nhận chưa chính xác", 'mtdev'),
    //    ];

    /* Kiem tra token truoc khi xu ly */
    if (!wp_verify_nonce($_REQUEST['_token'], 'user_dang_ky_thanh_vien')) {
        return __('Token mismatch!', 'mms');
    }

    if (empty($_POST['first_name'])) {
        return __('Vui lòng nhập họ', 'mms');
    }

    if (empty($_POST['last_name'])) {
        return __('Vui lòng nhập tên', 'mms');
    }

    if (empty($_POST['email'])) {
        return __('Vui lòng nhập email', 'mms');
    }

    if (empty($_POST['password'])) {
        return __('Vui lòng nhập mật khẩu', 'mms');
    }

    if ($_POST['password'] !== $_POST['password_confirmation']) {
        return __('Vui lòng kiểm tra lại mật khẩu', 'mms');
    }

    $userParams = [
        'user_login'   => $_POST['user_login'],
        'user_email'   => $_POST['email'],
        'user_pass'    => $_POST['password_confirmation'],  // When creating an user, `user_pass` is expected.
        'display_name' => $_POST['last_name'],
    ];

    $idUser = wp_insert_user($userParams);

    update_user_meta($idUser, '_user_birthday', sanitize_text_field($_POST['birthday']));
    update_user_meta($idUser, '_user_gender', sanitize_text_field($_POST['sex']));

    if (is_wp_error($idUser)) {
        return $idUser->get_error_message();
    }

    return true;
}

add_action('wp_ajax_nopriv_user_reset_password', 'mm_user_reset_password');
add_action('wp_ajax_user_reset_password', 'mm_user_reset_password');
function mm_user_reset_password()
{
    wp_send_json_success(true);
}

// add_action('wp_ajax_nopriv_google_login', 'googleLogin');
// add_action('wp_ajax_google_login', 'googleLogin');
// function googleLogin() {
//     if (is_user_logged_in()) {
//         socialCallbackRedirectUrl();
//         die();
//     }
//
//     $socialite = new SocialiteManager(SOCIAL_DRIVER);
//     $response  = $socialite->driver('google')->redirect();
//     echo $response;
// }
//
// add_action('wp_ajax_nopriv_facebook_login', 'facebookLogin');
// add_action('wp_ajax_facebook_login', 'facebookLogin');
// function facebookLogin() {
//     if (is_user_logged_in()) {
//         socialCallbackRedirectUrl();
//         die();
//     }
//
//     $socialite = new SocialiteManager(SOCIAL_DRIVER);
//     $response  = $socialite->driver('facebook')->redirect();
//     echo $response;
// }

add_action('wp_ajax_nopriv_social_login_callback', 'facebookLoginCallback');
add_action('wp_ajax_social_login_callback', 'facebookLoginCallback');
function facebookLoginCallback()
{
    if (is_user_logged_in()) {
        socialCallbackRedirectUrl();
        die();
    }

    try {
        $socialite = new SocialiteManager(SOCIAL_DRIVER);

        $fbUser = $socialite->driver($_GET['driver'])->user();

        $args = [
            'id'       => $fbUser->getId(),           // 1472352
            'nickname' => $fbUser->getNickname(),     // "overtrue"
            'username' => $fbUser->getName(),         // "overtrue"
            'name'     => $fbUser->getName(),         // "安正超"
            'email'    => $fbUser->getEmail(),        // "anzhengchao@gmail.com"
            'provider' => $fbUser->getProviderName(), // GitHub
        ];

        $user = get_user_by_email($args['email']);
        if (!$user) {
            $userId = wp_insert_user([
                'user_login'   => $args['email'],
                'user_email'   => $args['email'],
                'display_name' => $args['username'],
            ]);

            if (!is_wp_error($userId)) {
                updateUserMeta($userId, 'billing_first_name', $args['name']);
                updateUserMeta($userId, 'billing_email', $args['email']);

                $user = get_user_by_email($args['email']);
            }
        }

        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);
        socialCallbackRedirectUrl();
    } catch (\Exception $ex) {
        dump($ex);
    }
}

function socialCallbackRedirectUrl()
{
    $user = wp_get_current_user();

    echo '<script>opener.socialLoginReturn({
                success: true,
                notification: {
                    title: "' . __('Xin chào, ', 'mms') . $user->user_email . '", 
                    message: "' . __('Chúc mừng bạn đã đăng nhập thành công', 'mms') . '"
                },
                redirect: "/"
            });window.close();</script>';
}
