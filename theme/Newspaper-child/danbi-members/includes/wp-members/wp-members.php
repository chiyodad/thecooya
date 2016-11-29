<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'DM_WpMembers' ) ) :

define('DM_WPMEM_URL', plugin_dir_url( __FILE__ ));
define('DM_WPMEM_DIR', plugin_dir_path( __FILE__ ));

class DM_WpMembers {

	public $group_id;

    public static $DUMMY_ROW;

	function __construct() {

        self::$DUMMY_ROW = array('type' => '', 'row_before' => '', 'field_before' => '', 'field_after' => '', 'row_after' => '', 'label' => '');

        add_filter('login_url', array($this, 'login_url'), 10, 2);

		if (is_admin()) {
			include_once DM_WPMEM_DIR . 'admin/admin.php';
            add_action('wp_ajax_dbmembers_check_password', array($this, 'ajax_check_password') );
		} else {

			/*
			add_filter('register_url', array($this, 'register_url'));

			add_filter('wpmem_login_redirect', array($this, 'wpmem_login_redirect'));
			add_filter('wpmem_login_form', array($this, 'login_form'), 10, 2);
			add_filter('wpmem_login_hidden_fields', array($this, 'do_action_login_form'), 10, 2);
			add_filter('wpmem_inc_login_args', array($this, 'hide_heading'));

            add_action('wpmem_pre_init', array($this,'wpmem_pre_init'));
			add_action('wpmem_post_register_data', array($this, 'wpmem_post_register_data'));
			add_action('wpmem_post_update_data', array($this, 'wpmem_post_update_data'));

			add_filter('wpmem_register_data', array($this, 'wpmem_register_data'), 10, 2);

			add_filter('wpmem_register_fields_arr', array($this, 'check_group'), 10, 2);
			add_filter('wpmem_register_form_args', array($this, 'wpmem_register_form_args'), 10, 2);
			add_filter('wpmem_register_form', array($this, 'register_form'), 10, 4);

			add_filter('wpmem_register_hidden_fields', array($this, 'wpmem_register_hidden_fields'), 10, 2);
			add_action('wpmem_register_redirect', array($this, 'wpmem_register_redirect'));
			add_action('wpmem_register_captcha_row', array($this, 'wpmem_register_captcha_row'), 10, 2);

			// add_filter('wp_setup_nav_menu_item', array($this, 'wp_setup_nav_menu_item'));

            add_action('wpmem_pre_register_data', array($this, 'check_agreement'));
			add_action('wpmem_pre_register_data', array($this, 'register_check_password'), 80, 1);
            add_action('wpmem_post_register_data', array($this, 'wpmem_set_email_html'));
            add_action('wpmem_register_redirect', array($this, 'wpmem_unset_email_html'));

            // add_filter('dbmem_register_form_rows', array($this,'dbmem_register_form_agreement'), 20, 2);
            // add_filter('dbmem_register_form_rows', array($this,'dbmem_register_form_user_info_label'), 30, 2);
			*/
        }
	}

	function init() {
	}

	function wpmem_pre_init() {
		if (defined('GROUPS_CORE_VERSION'))
			$this->pre_wpmem();
			// add_action('init', array($this, 'pre_wpmem'));
	}

	function pre_wpmem() {
		$wpmem_a = ( isset( $_REQUEST['a'] ) ) ? trim( $_REQUEST['a'] ) : '';
		if (($wpmem_a === 'register' || $wpmem_a === 'update') && isset($_REQUEST['group_id']))
			add_filter('option_wpmembers_fields', array($this, 'option_wpmembers_fields'));
	}

	function option_wpmembers_fields($fields) {
        return $this->get_group_fields($fields, $_REQUEST['group_id']);
	}

    function wpmem_set_email_html($fields) {
        if (get_option( 'dbmembers_email_html' ) == '1') {
            add_filter( 'wp_mail_content_type', array( $this, 'wp_mail_content_type' ) );
        }
    }

    function wpmem_unset_email_html() {
        if (get_option( 'dbmembers_email_html' ) == '1') {
            remove_filter( 'wp_mail_content_type', array( $this, 'wp_mail_content_type' ) );
        }
    }

    function wp_mail_content_type() {
        return 'text/html';
    }

	function do_action_login_form($hidden, $action) {
		if ($action == 'login') {
			ob_start();
			do_action( 'login_form' );
			$page = ob_get_contents();
			ob_end_clean();
			$hidden .= $page;
		}
		return $hidden;
	}

	function register_form($form, $toggle, $rows, $hidden) {
		$old = array( '[wpmem_txt]', '[/wpmem_txt]' );
		$new = array( '' );
		$form = str_replace( $old, $new, $form );

		if ($toggle == 'new') {
			ob_start();
			do_action( 'after_signup_form' );
			$page = ob_get_contents();
			ob_end_clean();
			$form .= $page;
		}
		return $form;
	}

	function wpmem_post_register_data($fields) {
		if (get_option( 'dbmembers_force_login' ) == '1') {
			wp_set_auth_cookie($fields['ID'], false, flalse);
		}
	}

	function wpmem_post_update_data() {
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

	function is_seperate_agreement() {
		return get_option('dbmembers_seperate_agreement') === '1';
	}

    function dbmem_register_form_agreement($rows, $toggle = 'new')
    {
		if (($toggle == 'new' && !$this->is_seperate_agreement()) || $toggle == 'agreement') {
			$terms_row = $this->agreement('dbmembers_url_terms');
			$privacy_row = $this->agreement('dbmembers_url_privacy');
			$party_row = $this->agreement('dbmembers_url_3rd_party');
			if (!empty($terms_row) || !empty($privacy_row) || !empty($party_row)) {
				$rows[] = self::make_row('<legend>' . __('Agreement', 'danbi-members') . '</legend>');
				if (!empty($terms_row))
					$rows[] = self::make_row($terms_row);
				if (!empty($privacy_row))
					$rows[] = self::make_row($privacy_row);
				if (!empty($party_row))
					$rows[] = self::make_row($party_row);
				$rows[] = self::make_row('</fieldset><fieldset>');
			}
        }

        return $rows;
    }

	static function make_row($text)
	{
		return array_merge(array('field'=>$text, ''), self::$DUMMY_ROW);
	}

    function dbmem_register_form_user_info_label($rows, $toggle)
    {
		if ($toggle == 'new' && !$this->is_seperate_agreement()) {
            $rows[] = array_merge(array('field'=>'<legend class="user-info-heading">' . __('User Information', 'danbi-members') . '</legend>'), self::$DUMMY_ROW);
        }

        return $rows;
    }

	function agreement($option_name) {

		$postid = get_option($option_name);
		if (!$postid)
			return;

		if (!is_numeric($postid))
			$postid = url_to_postid($postid);

		if ($postid == 0)
			return;

		$post = get_post($postid);
        $content = wpautop(wptexturize($post->post_content));
        $agree_to = sprintf( __('I agree to %s.', 'danbi-members'), $post->post_title);
        $agree_alert = sprintf( __('Please agree to %s.', 'danbi-members'), $post->post_title);
		return <<<EOD
		<div class="agreement-box" style="margin-bottom:14px; clear:both;">
			<label class="text"><b>$post->post_title</b></label>
			<div class="div_textarea" style="height:150px; overflow-y:auto; border:1px solid darkgrey; padding:5px;">$content</div>
			<label style="display: inline-block;">
				<input type="checkbox" id="agree-to-$postid" class="agreement" name="check_$option_name" value="true"
					style="vertical-align:middle; margin-right:5px;" data-title="$post->post_title" data-agree-alert="$agree_alert" />
				<span style="vertical-align: middle">$agree_to</span>
				<span class="req">*</span>
			</label>
		</div>
EOD;
	}

	function check_agreement() {
		if (!$this->check_agreement_to('dbmembers_url_terms'))
			return;

		if (!$this->check_agreement_to('dbmembers_url_privacy'))
			return;

		if (!$this->check_agreement_to('dbmembers_url_3rd_party'))
			return;
	}

	function check_agreement_to($option_name) {
		global $wpmem_themsg;
		$url = get_option($option_name);
		if (!$url)
			return true;

		$postid = url_to_postid($url);
		if ($postid == 0)
			return true;

		if (isset($_POST['check_' . $option_name]) && $_POST['check_' . $option_name] == 'true')
			return true;

		$post = get_post($postid);
        $wpmem_themsg = sprintf( __('Please agree to %s.', 'danbi-members'), $post->post_title);
        return false;
	}

	function register_check_password($fields = array()) {
        global $wpmem_themsg;
        if (isset($fields['password'])) {
            $msg = $this->check_password($fields['password']);
            if ($msg !== FALSE)
                $wpmem_themsg = $msg;
        }
	}

    function ajax_check_password() {
        $msg = FALSE;

        if (!isset($_POST['pass1']) || empty($_POST['pass1']) || !isset($_POST['pass2']) || empty($_POST['pass2'])) {
            $msg = '비밀번호를 입력하십시오.';
        } else {
            $pass1 = $_POST['pass1'];
            $pass2 = $_POST['pass2'];
            if ($pass1 !== $pass2) {
                $msg = '비밀번호가 일치하지 않습니다.';
            } else {
                $msg = $this->check_password($pass1);
            }
        }

        if ($msg)
            echo $msg;

        die();
    }

	function check_password($password) {
        $msg = FALSE;

        $min_length = intval(get_option('dbmembers_password_min_length', '0'));
        $complex = get_option('dbmembers_password_complex', '0');

        if ($min_length > 0 && $complex === '1') {
            if (!preg_match_all('$\S*(?=\S{' . $min_length . ',})(?=\S*[a-zA-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)) {
                $msg = '비밀번호는 영문, 숫자, 특수문자 포함 ' . $min_length . '자 이상입니다.';
            }
        } else if (strlen($password) < $min_length) {
            $msg = '비밀번호는 ' . $min_length . '자 이상입니다.';
        } else if ($complex === '1' && !preg_match_all('$\S*(?=\S*[a-zA-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)) {
            $msg = '비밀번호에는 영문, 숫자, 특수문자가 포함되어야 합니다.';
        }

        return $msg;
	}

    function login_url( $login_url, $redirect ) {
		if (!defined('DBMEMBERS_LOGIN') && is_admin())
    		return $login_url;

        $url = get_option('wpmembers_settings');
        if ($url !== false && isset($url['user_pages']) && isset($url['user_pages']['login']) && !empty($url['user_pages']['login'])) {
        	$url = $url['user_pages']['login'];
        	if (is_numeric($url))
        		$url = get_permalink(intval($url));
        } else {
        	$url = get_option('dbmembers_url_login');
        }
        return $url ? add_query_arg('redirect_to', urlencode($redirect), $url) : $login_url;
    }

	function register_url( $register_url ) {
        return (WPMEM_REGURL != null && !defined(WPMEM_REGURL)) ? WPMEM_REGURL : $register_url;
	}

	function hide_heading() {
		return array('heading' => '');
	}

	function wpmem_register_data($fields, $toggle = 'new') {
		if ($toggle === 'new') {
			$fields['user_nicename']   = ( isset( $_POST['user_nicename'] ) ) ? $_POST['user_nicename'] : $_POST['log'];
			$fields['display_name']    = ( isset( $_POST['display_name'] ) )  ? $_POST['display_name']  : $_POST['log'];
			$fields['nickname']        = ( isset( $_POST['nickname'] ) )      ? $_POST['nickname']      : $_POST['log'];
		}
		return $fields;
	}

	function wpmem_login_redirect($redirect_to) {
		return (get_option('dbmembers_login_redirect') === '1') ? home_url() : $redirect_to;
	}

	function wpmem_register_form_args($args = array(), $toggle = '') {
		$args = array(
			'heading_before'   => '',
			'heading_after'    => '',
		);
		if ($toggle === 'new' && get_option('dbmembers_unique_username') === '1') {
            if (isset($args['form_class']))
                $args['form_class'] .= ' use-unique-username';
            else
                $args['form_class'] = 'use-unique-username';
        }
		if (get_option('dbmembers_show_clear_form') === '1') {
            $args['show_clear_form'] = true;
        } else {
        	$args['show_clear_form'] = false;
        }

		return $args;
	}

	function login_form($form, $action) {
		$old = array( '[wpmem_txt]', '[/wpmem_txt]' );
		$new = array( '' );
		$form = str_replace( $old, $new, $form );

        if ( $action === 'pwdchange' &&
                (get_option('dbmembers_password_min_length', '') !== '' || get_option('dbmembers_password_complex','0') !== '0') ) {
            $form .= '<script type="text/javascript" src="' . DBMEM_URL . 'includes/wp-members/js/pwdchange.js"></script>';
        }
		return $form;
	}

	function wpmem_pre_validate_form($fields) {
		if (!defined('GROUPS_CORE_VERSION') || !isset($_POST['group_id']))
			return $fields;

        return $this->get_group_fields($fields, $_POST['group_id']);
    }

    function get_group_fields($fields, $group_id = '') {
    	if (empty($group_id))
    		return $fields;

        $group_fields = get_option('dbmembers_group_fields_' . $group_id);
        if (empty($group_fields))
            return $fields;

        $group_fields = explode(',', str_replace(' ', '', $group_fields));
        $new_fields = array();
        foreach( $fields as $field ) {
            if (in_array($field[2],$group_fields))
                $new_fields[] = $field;
        }
        return $new_fields;
	}

	function check_group($fields, $toggle) {
		if (!defined('GROUPS_CORE_VERSION'))
			return $fields;

		if ($toggle === 'edit') {
            $guser = new Groups_User(get_current_user_id());
            $groups = $guser->groups;
            foreach ($groups as $group) {
            	if ($group->name !== Groups_Registered::REGISTERED_GROUP_NAME)
            		$this->group_id = $group->group_id;
            }
        }

        return $this->get_group_fields($fields, $this->group_id);
	}

	function settings() {
	}

	function wp_setup_nav_menu_item( $item ) {
		global $pagenow ;

		if( $pagenow!='nav-menus.php' && !defined('DOING_AJAX') && isset( $item->url ) && !is_user_logged_in() ) {
			// echo 'url: ' . $item->url . '<br/>';
			// echo 'msurl: ' . get_option('wpmembers_msurl', '') . '<br/>';
			$url = str_replace('https://', 'http://', $item->url);
			if ( $url == get_option('wpmembers_msurl', '') || $url == get_option('dbmembers_url_password', ''))
				$item->_invalid = true;
		}
		return $item;
	}

    function wpmem_register_hidden_fields($hidden, $toggle) {

    	if ($this->group_id !== NULL) {
            $hidden .= '<input type="hidden" name="group_id" value="' . $this->group_id . '" />';
        }
        if ($toggle === 'new' && $this->is_seperate_agreement()) {
        	if (!isset($_REQUEST['check_dbmembers_url_terms']) && !isset($_REQUEST['check_dbmembers_url_privacy'])) {
        		$hidden = '<script type="text/javascript">alert("약관 동의를 먼저 해주십시오.");location.href="' . wp_registration_url() . '";</script>';
	        } else {
	        	if (isset($_REQUEST['check_dbmembers_url_terms']))
    	    		$hidden .= '<input type="hidden" name="check_dbmembers_url_terms" value="' . $_REQUEST['check_dbmembers_url_terms'] . '">';
	        	if (isset($_REQUEST['check_dbmembers_url_privacy']))
    	    		$hidden .= '<input type="hidden" name="check_dbmembers_url_privacy" value="' . $_REQUEST['check_dbmembers_url_privacy'] . '">';
    	    }
        }

        return $hidden;
    }

    function get_agreement_page()
    {
    	define(WPMEM_CAPTCHA, 0);

        remove_filter('wpmem_register_fields_arr', array($this, 'wpmem_register_fields_arr'));
        remove_filter('wpmem_register_form_rows', array($this, 'wpmem_register_form_rows'));
        remove_filter('wpmem_register_hidden_fields', array($this, 'wpmem_register_hidden_fields'));

        add_filter('wpmem_register_form_args', array($this, 'agreement_page_form_args'), 10, 2);
        add_filter('wpmem_register_fields_arr', array($this, 'agreement_page_fields_arr'), 10, 2);
        add_filter('wpmem_register_form_rows', array($this, 'agreement_page_form_rows'), 10, 2);
        add_filter('wpmem_register_hidden_fields', array($this, 'agreement_page_hidden_fields'), 10, 2);

        $_REQUEST['redirect_to'] = get_permalink(get_option('dbmembers_url_userinfo',''));
        $content = do_shortcode('[wpmem_form register]');
        // $content = do_shortcode('[wp-members page="register"]');
        return $content;
    }

    function agreement_page_form_args($args, $toggle)
    {
    	return array(
    		'submit_register'  => __('Next')
    	);
    }

    function agreement_page_fields_arr($fields, $toggle)
    {
    	return array();
    }

    function agreement_page_hidden_fields($hidden, $toggle = 'new')
    {
    	return ($toggle == 'new') ? '' : $toggle;
    }

    function agreement_page_form_rows($rows, $toggle)
    {
    	return $this->dbmem_register_form_agreement(array(), 'agreement');
    }

    function agreement_page_form($form, $toggle, $rows, $hidden )
    {
    	return preg_replace('/action="[^"]+"/i', 'action="' . get_permalink(get_option('dbmembers_url_userinfo','')) . '"', $form);
    }

}

return new DM_WpMembers;

endif; // class_exists check
