<?php
/**
 * Theme Options.
 *
 * Here, you can register Theme Options using the Carbon Fields library.
 *
 * @link    https://carbonfields.net/docs/containers-theme-options/
 *
 * @package WPEmergeCli
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

$optionsPage = Container::make('theme_options', __('Theme Options', 'mms'))
	->set_page_file('app-theme-options.php')
	->add_tab(__('Branding | Thương hiệu', 'mms'), [
		Field::make('image', 'logo' . currentLanguage(), __('Logo', 'mms'))
			->set_width(33.33),
		Field::make('image', 'logo_dark' . currentLanguage(), __('Logo Dark', 'mms'))
			->set_width(33.33),
		Field::make('image', 'default_image' . currentLanguage(), __('Default image | Hình ảnh mặc định', 'mms'))
			->set_width(33.33),
		Field::make('textarea', 'slogan' . currentLanguage(), __('', 'mms'))
			->set_attribute('placeholder', 'mooms.dev slogan'),
	])

	->add_tab(__('Contact | Liên hệ', 'mms'), [
		Field::make('html', 'info', __('', 'mms'))
			->set_html('----<i> Information | Thông tin </i>----'),
		Field::make('text', 'company' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'Company | Công ty'),
		Field::make('text', 'address' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'Address | Địa chỉ'),
		Field::make('textarea', 'googlemap' . currentLanguage(), __('', 'mms'))
			->set_attribute('placeholder', 'Google map'),
		Field::make('text', 'email' . currentLanguage(), __('', 'mms'))->set_width(33.33)
			->set_attribute('placeholder', 'Email'),
		Field::make('text', 'phone_number' . currentLanguage(), __('', 'mms'))->set_width(33.33)
			->set_attribute('placeholder', 'Phone number | Số điện thoại'),
		Field::make('text', 'hour_working' . currentLanguage(), __('', 'mms'))->set_width(33.33)
			->set_attribute('placeholder', 'Hour working | Giờ làm việc'),
		Field::make('html', 'socials', __('', 'mms'))
			->set_html('----<i> Socials | Mạng xã hội </i>----'),
		Field::make('text', 'facebook' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'facebook'),
		Field::make('text', 'instagram' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'instagram'),
		Field::make('text', 'tiktok' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'tiktok'),
		Field::make('text', 'youtube' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'youtube'),
	])

	->add_tab(__('Header  |  Footer', 'mms'), [
		Field::make('html', 'header', __('', 'mms'))
			->set_html('----<i> Header </i>----'),
		Field::make('text', 'header_label' . currentLanguage(), __('', 'mms'))->set_width(50),

		Field::make('html', 'footer', __('', 'mms'))
			->set_html('----<i> Footer </i>----'),
		Field::make('text', 'contact_label' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'Contact label | Nhãn liên hệ'),
		Field::make('text', 'contact_url' . currentLanguage(), __('', 'mms'))->set_width(50)
			->set_attribute('placeholder', 'Contact URL | Liên kết liên hệ'),
		Field::make('textarea', 'contact_message' . currentLanguage(), __('', 'mms'))
			->set_attribute('placeholder', 'Contact description | Mô tả liên hệ'),
	])

	->add_tab(__('Scripts', 'mms'), [
		Field::make('header_scripts', 'crb_header_script', __('Header Script', 'app')),
		Field::make('footer_scripts', 'crb_footer_script', __('Footer Script', 'app')),
	]);