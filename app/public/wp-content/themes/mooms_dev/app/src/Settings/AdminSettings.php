<?php

namespace App\Settings;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Intervention\Image\ImageManagerStatic as Image;

class AdminSettings
{
	protected $currentUser;

	protected $superUsers = SUPER_USER;

	protected $errorMessage = '';

	public function __construct()
	{
		$this->currentUser = wp_get_current_user();

		if (in_array($this->currentUser->user_login, $this->superUsers, true)) {
			$this->createAdminOptions();
		} else {
			$this->hideSuperUsers();
			$this->setupErrorMessage();
			$this->checkIsMaintenance();
			$this->disablePluginPage();
			$this->disableOptionsReadPage();
			$this->disableAllUpdate();
			$this->removeUnnecessaryMenus();
		}

		$this->addDashboardContactWidget();
		$this->removeDefaultWidgets();
		$this->removeDashboardWidgets();
		$this->changeHeaderUrl();
		$this->changeHeaderTitle();
		$this->changeFooterCopyright();
		$this->customizeAdminBar();
		$this->resizeOriginalImageAfterUpload();
		$this->renameUploadFileName();
		$this->addCustomResources();
		$this->addCustomExtensionsInMediaUpload();

		if (get_option('_disable_admin_confirm_email') === 'yes') {
			$this->disableChangeAdminEmailRequireConfirm();
		}

		if (get_option('_disable_use_weak_password') === 'yes') {
			$this->disableCheckboxUseWeakPassword();
		}

		if (get_option('_disable_use_jquery_migrate') === 'yes') {
			$this->disableUseJqueryMigrate();
		}

		if (get_option('_hide_post_menu_default') === 'yes') {
			$this->hidePostMenuDefault();
		}

		if (get_option('_hide_comment_menu_default') === 'yes') {
			$this->hideCommentMenuDefault();
		}
	}

	public function addCustomExtensionsInMediaUpload()
	{
		add_filter('upload_mimes', static function ($mimes) {
			return array_merge($mimes, [
				'ac3' => 'audio/ac3',
				'mpa' => 'audio/MPA',
				'flv' => 'video/x-flv',
				'svg' => 'image/svg+xml',
			]);
		});

		add_action('wp_ajax_mm_get_attachment_url_thumbnail', static function () {
			$url          = '';
			$attachmentID = isset($_REQUEST['attachmentID']) ? $_REQUEST['attachmentID'] : '';
			if ($attachmentID) {
				$url = wp_get_attachment_url($attachmentID);
			}
			die($url);
		});
	}

	public function disableCheckboxUseWeakPassword()
	{
		add_action('admin_head', function () {
?>
			<script>
				jQuery(document).ready(function() {
					jQuery('.pw-weak').remove();
				});
			</script>
		<?php
		});

		add_action('login_enqueue_scripts', function () {
		?>
			<script>
				document.addEventListener("DOMContentLoaded", function(event) {
					let elements = document.getElementsByClassName('pw-weak');
					console.log(elements);
					let requiredElement = elements[0];
					requiredElement.remove();
				});
			</script>
			<?php
		});
	}

	public function addDashboardContactWidget()
	{
		add_action('wp_dashboard_setup', static function () {
			wp_add_dashboard_widget('custom_help_widget', 'Giới thiệu', static function () { ?>
				<div style="position: relative;">
					<div style="text-align:center">
						<a target="_blank" href="<?php echo AUTHOR['website'] ?>" title="<?php echo AUTHOR['name'] ?>">
							<img style="width:100%" src="<?php echo get_site_url() . '/wp-content/themes/mooms_dev/resources/images/dev/moomsdev-black.png' ?>" alt="<?php echo AUTHOR['name'] ?>" title="<?php echo AUTHOR['name'] ?>">
						</a>
					</div>
					<h2 style="text-align:center;"><?php echo AUTHOR['name'] ?></h2>
					<div style="margin-top:2rem; display: flex; column-gap: 15px; justify-content: space-between;">
						<p><a style="font: normal normal 500 12px Montserrat; color: black; text-decoration: none;" href="tel:<?php echo str_replace(['.', ',', ' '], '', AUTHOR['phone_number']); ?>"><?php echo AUTHOR['phone_number'] ?></a></p>
						<p><a style="font: normal normal 500 12px Montserrat; color: black; text-decoration: none;" href="mailto:<?php echo AUTHOR['email'] ?>"><?php echo AUTHOR['email'] ?></a></p>
						<p><a style="font: normal normal 500 12px Montserrat; color: black; text-decoration: none;" href="<?php echo AUTHOR['website'] ?>" target="_blank"><?php echo AUTHOR['website'] ?></a></p>
					</div>
				</div>
<?php });
		});
	}

	public function removeDefaultWidgets()
	{
		add_action('widgets_init', static function () {
			unregister_widget('WP_Widget_Pages');
			unregister_widget('WP_Widget_Calendar');
			unregister_widget('WP_Widget_Archives');
			unregister_widget('WP_Widget_Links');
			unregister_widget('WP_Widget_Meta');
			unregister_widget('WP_Widget_Search');
			unregister_widget('WP_Widget_Categories');
			unregister_widget('WP_Widget_Recent_Posts');
			unregister_widget('WP_Widget_Recent_Comments');
			unregister_widget('WP_Widget_RSS');
			unregister_widget('WP_Widget_Tag_Cloud');
			unregister_widget('WP_Nav_Menu_Widget');
		});
	}
	public function removeDashboardWidgets()
	{
		add_action('admin_init', static function () {
			remove_meta_box('dashboard_right_now', 'dashboard', 'normal');       // right now
			remove_meta_box('dashboard_activity', 'dashboard', 'normal');        // WP 3.8
			remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // recent comments
			remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // incoming links
			remove_meta_box('dashboard_plugins', 'dashboard', 'normal');         // plugins
			remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');     // quick press
			remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');   // recent drafts
			remove_meta_box('dashboard_primary', 'dashboard', 'normal');         // wordpress blog
			remove_meta_box('dashboard_secondary', 'dashboard', 'normal');       // other wordpress news
		});
	}

	public function changeHeaderUrl()
	{
		add_filter('login_headerurl', static function ($url) {
			return '' . AUTHOR['website'] . '';
		});
	}

	public function changeHeaderTitle()
	{
		add_filter('login_headertitle', static function () {
			return get_option('blogname');
		});
	}

	public function changeFooterCopyright()
	{
		add_filter('admin_footer_text', static function () {
			echo '<a href="' . AUTHOR['website'] . '" target="_blank">' . AUTHOR['name'] . '</a> © ' . date('Y') . ' - All rights reserved';
		});
	}

	public function customizeAdminBar()
	{
		$author = AUTHOR;
		add_action('wp_before_admin_bar_render', static function () use ($author) {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('wp-logo');          // Remove the Wordpress logo
			$wp_admin_bar->remove_menu('about');            // Remove the about Wordpress link
			$wp_admin_bar->remove_menu('wporg');            // Remove the Wordpress.org link
			$wp_admin_bar->remove_menu('documentation');    // Remove the Wordpress documentation link
			$wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
			$wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
			// $wp_admin_bar->remove_menu('site-name');        // Remove the site name menu
			$wp_admin_bar->remove_menu('view-site');        // Remove the view site link
			$wp_admin_bar->remove_menu('updates');          // Remove the updates link
			$wp_admin_bar->remove_menu('comments');         // Remove the comments link
			$wp_admin_bar->remove_menu('new-content');      // Remove the content link
			$wp_admin_bar->remove_menu('w3tc');             // If you use w3 total cache remove the performance link
			// $wp_admin_bar->remove_menu('my-account');       // Remove the user details tab
		}, 7);

		add_action('admin_bar_menu', static function ($wp_admin_bar) use ($author) {
			$args = [
				'id'    => 'logo_author',
				'title' => '<img src="' . get_site_url() . "/wp-content/themes/mooms_dev/resources/images/dev/moomsdev-white.png" . '" style="height: 1rem; padding-top:.3rem;" alt="' . AUTHOR['name'] . '">',
				'href'  => $author['website'],
				'meta'  => [
					'target' => '_blank',
				],
			];
			$wp_admin_bar->add_node($args);
		}, 10);
	}

	public function renameUploadFileName()
	{
		add_filter('sanitize_file_name', function ($filename) {
			$info        = pathinfo($filename);
			$ext         = empty($info['extension']) ? '' : '.' . $info['extension'];
			$newFileName = str_replace($ext, '', date('YmdHi') . '-' . $filename);
			$unicode     = [
				'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
				'd' => 'đ',
				'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
				'i' => 'í|ì|ỉ|ĩ|ị',
				'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
				'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
				'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
				'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
				'D' => 'Đ',
				'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
				'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
				'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
				'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
				'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
			];
			foreach ($unicode as $nonUnicode => $uni) {
				$newFileName = preg_replace("/($uni)/i", $nonUnicode, $newFileName);
			}
			$newFileName = str_replace(' ', '-', $newFileName);
			$newFileName = preg_replace('/[^A-Za-z0-9\-]/', '', $newFileName);
			$newFileName = preg_replace('/-+/', '-', $newFileName);
			return $newFileName . $ext;
		}, 10);
	}

	public function resizeOriginalImageAfterUpload()
	{
		add_filter('intermediate_image_sizes_advanced', static function ($sizes) {
			$imgSize = [
				'medium',
				'medium_large',
				'large',
				'full',
				'woocommerce_single',
				'woocommerce_gallery_thumbnail',
				'shop_catalog',
				'shop_single',
				'woocommerce_thumbnail',
				'shop_thumbnail',
			];
			foreach ($imgSize as $item) {
				if (array_key_exists($item, $sizes)) {
					unset($sizes[$item]);
				}
			}
			return $sizes;
		});

		add_filter('wp_generate_attachment_metadata', static function ($image_data) {
			try {
				$upload_dir = wp_upload_dir();
				$imgPath    = $upload_dir['basedir'] . '/' . $image_data['file'];
				$image      = Image::make($imgPath);
				$imgWidth   = $image->width();
				$imgHeight  = $image->height();
				$image->resize(null, null, static function ($constraint) {
					$constraint->aspectRatio();
				});
				$image->save($imgPath, 100);
			} catch (\Exception $ex) {
			}
			return $image_data;
		});
	}

	public function addCustomResources()
	{
		add_action('admin_enqueue_scripts', static function ($hook) {
			wp_enqueue_script('jquery_repeater', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js');
			wp_enqueue_script('mm-custom-scripts', adminAsset('js/admin.js'));
		});
	}

	public function disableChangeAdminEmailRequireConfirm()
	{
		remove_action('add_option_new_admin_email', 'update_option_new_admin_email');
		remove_action('update_option_new_admin_email', 'update_option_new_admin_email');

		add_action('add_option_new_admin_email', function ($old_value, $value) {
			update_option('admin_email', $value);
		}, 10, 2);

		add_action('update_option_new_admin_email', function ($old_value, $value) {
			update_option('admin_email', $value);
		}, 10, 2);
	}

	public function createAdminOptions()
	{
		add_action('carbon_fields_register_fields', static function () {
			$options = Container::make('theme_options', __('Admin', 'mms'))
				->set_page_file(__('mms-admin-settings', 'mms'))
				->set_page_menu_position(2)
				->add_tab(__('ADMIN', 'mms'), [
					Field::make('checkbox', 'is_maintenance', __('Turn on website maintenance mode', 'mms')),
					Field::make('checkbox', 'disable_admin_confirm_email', __('Turn off the feature to change email admin need to verify email', 'mms'))->set_default_value('true'),
					Field::make('checkbox', 'disable_use_weak_password', __('Turn off the feature that allows the use of weak passwords', 'mms')),
					Field::make('checkbox', 'disable_use_jquery_migrate', __('Turn off the feature that allows the use of jQuery Migrate', 'mms')),
					Field::make('checkbox', 'hide_post_menu_default', __('Hide post menu default', 'mms')),
					Field::make('checkbox', 'hide_comment_menu_default', __('Hide comment menu default', 'mms')),
				])
				->add_tab(__('SMTP', 'mms'), [
					Field::make('checkbox', 'use_smtp', __('Sử dụng SMTP để gửi mail', 'mms')),
					Field::make('separator', 'smtp_separator_1', __('Thông tin máy chủ SMTP', 'mms')),
					Field::make('text', 'smtp_host', __('Địa chỉ máy chủ', 'mms'))->set_default_value('smtp.gmail.com'),
					Field::make('text', 'smtp_port', __('Cổng máy chủ', 'mms'))->set_default_value('587'),
					Field::make('text', 'smtp_secure', __('Phương thức mã hóa', 'mms'))->set_default_value('TLS'),
					Field::make('separator', 'smtp_separator_2', __('Thông tin email hệ thống', 'mms')),
					Field::make('text', 'smtp_username', __('Địa chỉ email', 'mms'))->set_default_value('mooms.dev@gmail.com'),
					Field::make('text', 'smtp_password', __('Mật khẩu', 'mms'))->set_default_value('utakxthdfibquxos'),
				]);
		});
	}

	public function hideSuperUsers()
	{
		add_action('pre_user_query', function ($user_search) {
			global $wpdb;
			$superUsers               = "('" . implode("','", $this->superUsers) . "')";
			$user_search->query_where = str_replace('WHERE 1=1', "WHERE 1=1 AND {$wpdb->users}.user_login NOT IN " . $superUsers, $user_search->query_where);
		});
	}

	public function setupErrorMessage()
	{
		$this->errorMessage = '
								<div style="position: relative;">
									<div style="text-align:center">
										<a target="_blank" href="' . AUTHOR['website'] . '">
											<img style="width:50%" src="' .  get_site_url() . "/wp-content/themes/mooms_dev/resources/images/dev/moomsdev-black.png" . '" alt="' . AUTHOR['name'] . '">
										</a>
									</div>
									<h1 style="text-align: center; text-transform: uppercase">Sorry, you do not have access to this content</h1>
									<h2  style="text-align: center;"><a href="/wp-admin/">Back to dashboard admin</a></h2>
								</div>';
	}

	public function checkIsMaintenance()
	{
		add_action('after_setup_theme', static function () {
			if (get_option('_is_maintenance') === 'yes') {
				wp_die('

                    <div style="position: relative;">
                        <div style="text-align:center">
                            <a target="_blank" href="' . AUTHOR['website'] . '" title="' . AUTHOR['name'] . '">
                                <img style="width:100%" src="' .  get_site_url() . "/wp-content/themes/mooms_dev/resources/images/dev/moomsdev-black.png" . ' ?>" alt="' . AUTHOR['name'] . '" title="' . AUTHOR['name'] . '">
                            </a>
                        </div>
                        <div style="margin-top:1rem; display: flex; flex-wrap: wrap; column-gap: 15px; justify-content: space-between;">
                            <p><a style="font: normal normal 500 20px Montserrat; color: black; text-decoration: none;" href="tel: ' . str_replace(['.', ',', ' '], '', AUTHOR['phone_number']) . ' "> ' . AUTHOR['phone_number'] . ' </a></p>
                            <p><a style="font: normal normal 500 20px Montserrat; color: black; text-decoration: none;" href="mailto:' . AUTHOR['email'] . '">' . AUTHOR['email'] . '</a></p>
                            <p><a style="font: normal normal 500 20px Montserrat; color: black; text-decoration: none;" href="' . AUTHOR['website'] . '" target="_blank">' . AUTHOR['website'] . '</a></p>
                        </div>
                        <h2 style="font: normal normal 700 22px Montserrat; text-align:center">The system is currently under maintenance, please come back later.<br>Thank you</h2>
                    </div>');
			}
		});
	}

	public function disablePluginPage()
	{
		add_action('admin_menu', static function () {
			global $menu;
			foreach ($menu as $key => $menuItem) {
				switch ($menuItem[2]) {
					case 'plugins.php':
					case 'customize.php':
						// case 'themes.php':
						unset($menu[$key]);
						break;
				}
			}

			global $submenu;
			unset($submenu['themes.php'][5], $submenu['themes.php'][6], $submenu['themes.php'][11]);
		}, 999);

		$errorMessage = $this->errorMessage;
		add_action('current_screen', static function () use ($errorMessage) {
			$deniePage      = [
				'plugins',
				'plugin-install',
				'plugin-editor',
				'themes',
				'theme-install',
				'theme-editor',
				'customize',
				'tools',
				'import',
				'export',
				'tools_page_action-scheduler',
				'tools_page_export_personal_data',
				'tools_page_remove_personal_data',
			];
			$current_screen = get_current_screen();

			if ($current_screen !== null && in_array($current_screen->id, $deniePage, true)) {
				wp_die($errorMessage);
			}
		});
	}

	public function disableOptionsReadPage()
	{
		$removePages = [
			'options-reading.php',
			'options-writing.php',
			'options-discussion.php',
			'options-media.php',
			'privacy.php',
			'options-permalink.php',
			'tinymce-advanced',
		];
		add_action('admin_menu', static function () use ($removePages) {
			foreach ($removePages as $page) {
				remove_submenu_page('options-general.php', $page);
			}
		});

		$errorMessage = $this->errorMessage;
		$denyPages    = [
			'options-reading',
			'options-writing',
			'options-discussion',
			'options-media',
			'privacy',
			'options-permalink',
			'settings_page_tinymce-advanced',
			'toplevel_page_wpseo_dashboard',
		];
		add_action('current_screen', static function () use ($errorMessage, $denyPages) {
			$current_screen = get_current_screen();
			if ($current_screen !== null && in_array($current_screen->id, $denyPages, true)) {
				wp_die($errorMessage);
			}
		});
	}

	public function disableAllUpdate()
	{
		remove_action('load-update-core.php', 'wp_update_plugins');
		add_filter('pre_site_transient_update_plugins', function ($a) {
			return null;
		});
	}

	public function removeUnnecessaryMenus()
	{
		add_action('admin_menu', static function () {
			global $menu;
			global $submenu;
			foreach ($menu as $key => $menuItem) {
				if (in_array($menuItem[2], [
					'tools.php',
					'edit-comments.php',
					'wpseo_dashboard',
					'duplicator',
					'yit_plugin_panel',
					'woocommerce-checkout-manager',
				])) {
					unset($menu[$key]);
				}
			}
		});
	}

	public function hidePostMenuDefault()
	{
		add_action('admin_init', function () {
			remove_menu_page('edit.php');
		});
	}

	public function hideCommentMenuDefault()
	{
		add_action('admin_init', function () {
			remove_menu_page('edit-comments.php');
		});
	}

	public function disableUseJqueryMigrate()
	{
		add_action('wp_default_scripts', function ($scripts) {
			if (!is_admin() && isset($scripts->registered['jquery'])) {
				$script = $scripts->registered['jquery'];
				if ($script->deps) {
					$script->deps = array_diff($script->deps, ['jquery-migrate']);
				}
			}
		});
	}
}
