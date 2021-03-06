<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wpmembers_Controller extends Abstract_Controller
{
    protected $id = 'wpmem';

    function __construct()
    {
        parent::__construct(__FILE__);
        $this->add_filter('register_form_before', 10, 2);
        $this->add_filter('register_hidden_fields', 10, 2);
        $this->add_filter('register_data', 10, 2);
		$this->add_filter('admin_tabs', 20);
		$this->add_action('admin_do_tab', 30);
        $this->add_action('post_register_data', 10, 1);
        $this->add_ajax('update_settings_namecheck');
    }

    function admin_tabs($tabs)
    {
		$tabs['namecheck'] = '본인인증';
		return $tabs;
    }

    function admin_do_tab($tab)
    {
        if ($tab === 'namecheck') {
            $context = array();
            $this->view('tab-settings', $context);
        }
    }

    function update_settings_namecheck()
    {
        foreach (array('', 'profile') as $type) {
            foreach (array('nice_checkplus', 'nice_ipin', 'namecheck_save') as $option) {
                update_option( "dbmembers_{$type}_{$option}", ( isset( $_POST["dbmembers_{$type}_{$option}"] ) ) ? '1' : '0', 'no' );
            }
        }
        update_option( "dbmembers_profile_namecheck_display", ( isset( $_POST["dbmembers_profile_namecheck_display"] ) ) ? '1' : '0', 'no' );

        wp_die('1');
    }

	function register_form_before($content, $toggle = '')
    {
        $namecheck = apply_filters('dbmem_settings_namecheck', false, $toggle);
        if ($namecheck)
            $this->script('namecheck');

		return $content;
	}

    function register_hidden_fields($hidden, $toggle)
    {
        if ($toggle !== 'new')
            return $hidden;

        $namecheck = apply_filters('dbmem_settings_namecheck', false);
        if (!$namecheck)
            return $hidden;

        foreach (array('component','reqseq','encode') as $field) {
            $name = 'namecheck_' . $field;
            $hidden .= sprintf('<input type="hidden" name="%s" value="%s" />',
                    $name,
                    isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''
            );
        }
        return $hidden;
    }

    function register_data($fields, $toggle)
    {
        if ($toggle !== 'new')
            return $fields;

        global $wpmem_themsg;

        $namecheck = apply_filters('dbmem_settings_namecheck', FALSE);
        if ($namecheck) {
            if (!isset($_REQUEST['namecheck_component']) || empty($_REQUEST['namecheck_component']) ||
                    !isset($_REQUEST['namecheck_reqseq']) || empty($_REQUEST['namecheck_reqseq']) ||
                    !isset($_REQUEST['namecheck_encode']) || empty($_REQUEST['namecheck_encode']) )
                $wpmem_themsg = '본인인증을 해주십시오.';

            try {
                $decode = apply_filters('nice_' . $_REQUEST['namecheck_component'] . '_decode', $_REQUEST['namecheck_encode'], $_REQUEST['namecheck_reqseq']);
                $fields['namecheck_data'] = $decode;
            } catch(\Exception $e) {
                $wpmem_themsg = $e->getMessage();
            }
        }

        return $fields;
    }

    function post_register_data($fields)
    {
        if (get_option('dbmembers_namecheck_save', '0') === '1' && is_array($fields['namecheck_data'])) {
            foreach ($fields['namecheck_data'] as $name => $value) {
                update_user_meta( $fields['ID'], 'namecheck_'. strtolower($name), $value );
            }
        }
    }
}

return new Wpmembers_Controller;
