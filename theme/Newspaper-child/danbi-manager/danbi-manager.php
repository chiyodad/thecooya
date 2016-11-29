<?php
/*킷
Plugin Name: 단비 매니저
Plugin URI: http://danbistore.com/item/danbi-manager/
Description: 단비스토어에서 구입한 테마와 플러그인의 업데이트를 지원합니다.
Version: 1.0.1
Author: 단비스토어
Author URI: http://danbistore.com
License: 
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Danbi_Manager' ) )  return 1;

define( 'DANBISTORE_URL', 'http://danbistore.com' );

/*
 * Register to DanbiManager
 */
add_filter('danbistore_plugins', 'danbistore_plugins_danbi_manager');
function danbistore_plugins_danbi_manager($plugins) {
    $plugins['danbi-manager'] = array('path'=>__FILE__, 'license'=>'bacf625399a73ae3c51d5d8045853f5c');
    return $plugins;
}

class Danbi_Manager {

    public static $VERSION;
    public static $PLUGIN_ID;
    public static $PLUGIN_DIR;
    public static $PLUGIN_URL;
    public static $INCLUDES;

    public static $instance;

    function __construct() {
        self::init();
        self::$instance = $this;
        
        if (is_admin()) {
            include_once self::$PLUGIN_DIR . 'admin/admin.php';
        }

		// add_filter('danbistore_plugins', array($this, 'danbi_manager'));
    }

    public static function init() {
        self::$VERSION = '1.0';
        self::$PLUGIN_ID = 'danbi_manager';
        self::$PLUGIN_DIR = plugin_dir_path( __FILE__ );
        self::$PLUGIN_URL = plugin_dir_url( __FILE__ );
        self::$INCLUDES = self::$PLUGIN_DIR . 'includes/';
    }

	/*
	 * Register to DanbiManager
	 */
	function danbi_manager($plugins) {
	    $plugins['danbi-manager'] = __FILE__;
	    return $plugins;
	}

    function get_license_key($id) {
    	$license = $this->get_license();
    	return isset($license[$id]) && isset($license[$id]['key']) ? $license[$id]['key'] : false;
    }

    function get_license() {
    	return get_option('danbi_manager_license', array());
    }

    function set_license($id, $license_data) {
        $license = $this->get_license();
        $license[$id] = $license_data;
        update_option('danbi_manager_license', $license);
    }

    function remove_license($id) {
        $license = $this->get_license();
        unset($license[$id]);
    	update_option('danbi_manager_license', $license);
    }
}

new Danbi_Manager;

function danbi_manager() {
	return Danbi_Manager::$instance;
}
