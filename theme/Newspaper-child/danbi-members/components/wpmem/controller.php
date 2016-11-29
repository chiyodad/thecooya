<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wpmem_Controller extends Abstract_Controller
{
    protected $component_id = 'wpmem';

    function __construct()
    {
        parent::__construct(__FILE__);

		$this->add_action('pre_register_data');
		$this->add_action('post_register_data', 10, 1, 'set_email_html');
		$this->add_action('register_redirect', 10, 1, 'unset_email_html');
		$this->add_action('post_register_data');
		$this->add_action('post_update_data');

        $this->add_filter('pre_validate_form');
        $this->add_filter('user_edit_heading');
        $this->add_filter('default_text_strings');
        $this->add_filter('inc_resetpassword_args', 10, 1, 'hide_heading');
        $this->add_filter('inc_changepassword_args', 10, 1, 'hide_heading');
        $this->add_filter('inc_forgotusername_args', 10, 1, 'hide_heading');
    }

    function default_text_strings($text = array())
    {
        $text['username_email'] = '이메일 주소';
        $text['username_button'] = __('Username') . ' 찾기';
        $text['username_link_before'] = __('Username') . ' 잊으셨습니까? ';
        $text['username_link'] = '찾기';
        $text['usernamefailed'] = '죄송합니다. 이메일 주소가 존재하지 않습니다.';
        $text['usernamesuccess'] = '이메일로 사용자명을 발송하였습니다.';
        $text['pwdchangesuccess'] = '비밀번호를 변경하였습니다.';
        $text['pwdreseterr'] = '사용자명 또는 이메일 주소가 존재하지 않습니다.';
        $text['pwdresetsuccess'] = '비밀번호를 재설정하였습니다!<br /><br />이메일로 신규 비밀번호를 발송하였습니다.';
        if (get_option('dbmembers_username_email') === '1') {
            $text = array('register_username' => sprintf('%s(%s)', __('Username'), __('Email')));
        }
        return $text;
    }

    function hide_heading($args)
    {
        return array('heading' => '');
    }

    function user_edit_heading($heading)
    {
        return '';
    }

    function pre_register_data($fields = array())
    {
        do_action('dbmem_register_pre_data', $fields);
    }

    function set_email_html($fields) {
        if (get_option( 'dbmembers_email_html' ) == '1') {
            add_filter( 'wp_mail_content_type', array( $this, 'wp_mail_content_type' ) );
        }
    }

    function unset_email_html() {
        if (get_option( 'dbmembers_email_html' ) == '1') {
            remove_filter( 'wp_mail_content_type', array( $this, 'wp_mail_content_type' ) );
        }
    }

    function wp_mail_content_type() {
        return 'text/html';
    }

	function post_register_data($fields) {
		if (get_option( 'dbmembers_force_login' ) == '1') {
			wp_set_auth_cookie($fields['ID'], false, flalse);
		}
	}

	function post_update_data() {
		global $current_user;

		if ( ! empty( $current_user ) ) {
			$cur_id = 0;
			if ( $current_user instanceof WP_User ) {
				$cur_id = $current_user->ID;
			} else if ( is_object( $current_user ) && isset( $current_user->ID ) ) {
				$cur_id = $current_user->ID;
			}
			if ($cur_id != 0) {
				$current_user = null;
				wp_set_current_user( $cur_id );
			}
		}
	}

    function pre_validate_form($fields)
    {
        return apply_filters('dbmem_register_validate_form', $fields);
    }
}

return new Wpmem_Controller;
