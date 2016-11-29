<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wpmem_Register_Controller extends Abstract_Controller
{
    protected $component_id = 'wpmem_register';

    function __construct()
    {
        parent::__construct(__FILE__);
        $this->add_filter('heading', 10, 2);
        $this->add_filter('form_args', 10, 2);
        $this->add_filter('form_before', 10, 2);
        $this->add_filter('form_rows', 10, 2);
        $this->add_filter('hidden_fields', 10, 2);
        // $this->add_filter('data', 10, 2);
        $this->add_filter('fields_arr', 10, 2);
		$this->add_action('redirect');
		$this->add_action('captcha_row', 10, 2);
    }

	function heading($heading, $toggle = '') {
		return '';
	}

	function fields_arr($fields = array(), $toggle = '') {
		return apply_filters('dbmem_register_fields_arr', $fields, $toggle);
	}

	function form_args($args = array(), $toggle = '') {
		return apply_filters('dbmem_register_form_args', $args, $toggle);
	}

	function form_before($content, $toggle = '')
    {
        return apply_filters('dbmem_register_form_before', $content, $toggle);
	}

    function hidden_fields($hidden, $toggle)
    {
        return apply_filters('dbmem_register_hidden_fields', $hidden, $toggle);
    }

	function form_rows($rows, $toggle) {
		return apply_filters('dbmem_register_form_rows', $rows, $toggle);
	}

	function redirect() {
        if (get_option('dbmembers_ajax_register') !== '1') {
            $welcome = get_permalink(get_option( 'dbmembers_url_welcome' ));
            if (!empty($welcome)) {
                wp_redirect($welcome);
                exit;
            }
        }
	}

	function captcha_row($form, $toggle) {
		return '<label class="text">' . __('CAPTCHA', 'danbi-members') . '</label>' . $form;
	}

	function data($fields, $toggle = 'new') {
		if ($toggle === 'new') {
			$fields['user_nicename']   = ( isset( $_POST['user_nicename'] ) ) ? $_POST['user_nicename'] : $_POST['log'];
			$fields['display_name']    = ( isset( $_POST['display_name'] ) )  ? $_POST['display_name']  : $_POST['log'];
			$fields['nickname']        = ( isset( $_POST['nickname'] ) )      ? $_POST['nickname']      : $_POST['log'];
		}
		return $fields;
	}

}

return new Wpmem_Register_Controller;
