<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wpmem_Login_Controller extends Abstract_Controller
{
    protected $component_id = 'wpmem';

    function __construct()
    {
        parent::__construct(__FILE__);

		$this->add_filter('login_redirect');
		$this->add_filter('login_form', 10, 2);
		$this->add_filter('login_hidden_fields', 10, 2);
		$this->add_filter('inc_login_args');
        $this->add_filter('forgot_link');
        $this->add_filter('username_link');
    }

    function username_link($link)
    {
        $link = get_option('dbmembers_url_find_username');
        return ($link !== false) ? get_permalink($link) : $link;
    }

    function forgot_link($link)
    {
        $link = get_option('dbmembers_url_password');
        return ($link !== false) ? get_permalink($link) : $link;
    }

	function login_redirect($redirect_to) {
		return (get_option('dbmembers_login_redirect') === '1') ? home_url() : $redirect_to;
	}

	function login_form($form, $action) {
		$old = array( '[wpmem_txt]', '[/wpmem_txt]' );
		$new = array( '' );
		$form = str_replace( $old, $new, $form );

        if ( $action === 'pwdchange' &&
                (get_option('dbmembers_password_min_length', '') !== '' || get_option('dbmembers_password_complex','0') !== '0') ) {
            // $form .= '<script type="text/javascript" src="' . DBMEM_URL . 'includes/wp-members/js/pwdchange.js"></script>';
            $this->script('pwdchange');
            $local = array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            );
            $this->localize('pwdchange', 'DanbiMembers', $local);

        }
		return $form;
	}

	function login_hidden_fields($hidden, $action) {
		if ($action == 'login') {
			ob_start();
			do_action( 'login_form' );
			$page = ob_get_contents();
			ob_end_clean();
			$hidden .= $page;
		} else if ($action === 'pwdchange') {
            $hidden .= sprintf('<input type="hidden" name="action" value="dbmem_register_password_check"><input type="hidden" name="_wpnonce" value="%s">',
                    wp_create_nonce('dbmem_register_password_check'));
        }
		return $hidden;
	}

	function inc_login_args() {
		return array('heading' => '');
	}

}

return new Wpmem_Login_Controller;
