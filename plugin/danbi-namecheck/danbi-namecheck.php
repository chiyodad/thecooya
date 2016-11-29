<?php
/*
 * Plugin Name: 단비 본인인증
 * Plugin URI: http://danbistore.com/item/danbi-namecheck/
 * Description: NICE평가정보㈜의 본인인증, 아이핀 모듈을 이용하여 본인인증을 처리합니다.
 * Version: 1.1
 * Author: 단비스토어
 * Author URI: http://danbistore.com
 * License:
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Danbi_Namecheck' ) ) :

/*
 * Register plugin to DanbiManager
 */
add_filter('danbistore_plugins', 'danbistore_plugins_danbi_namecheck');
function danbistore_plugins_danbi_namecheck($plugins) {
    $plugins['danbi-namecheck'] = array('path' => __FILE__);
    return $plugins;
}

/*
 * 단비매니저 설치 알림
 */
add_action('admin_notices', 'danbimanager_danbi_namecheck');
function danbimanager_danbi_namecheck() {
    if (!class_exists('Danbi_Manager') && !defined('DANBI_MANAGER_NOTICE')) {
        echo '<div class="updated"><p><b><a href="http://danbistore.com/item/danbi-manager/" target="_blank" style="text-decoration: none;">단비 매니저</a></b> 플러그인 설치 후 라이센스 키를 입력하십시오. <b><a href="http://danbistore.com/item/danbi-manager/" target="_blank" style="text-decoration: none;">단비 매니저</a></b>는 단비스토어에서 구입하신 테마, 플러그인 업데이트를 제공합니다.</p></div>';
        define('DANBI_MANAGER_NOTICE', true);
    }
}

class Danbi_Namecheck {

	var $loader;

	function __construct()
	{
		$this->loader = require_once 'components/component_loader.php';

		add_action('plugins_loaded', array($this,'plugins_loaded'));
		add_action('wp_enqueue_scripts', array($this, 'scripts'));
		register_activation_hook(__FILE__, array($this, 'activate'));
    }

	function activate()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			$path = 'Linux/' . (PHP_INT_SIZE * 8) . '/CPClient';
			exec('chmod a+x ' . __DIR__ . '/components/nice_checkplus/lib/' . $path);
			$path = (PHP_OS === 'Unix') ?
					'Unix/IPINClient' :
	                (PHP_OS === 'FreeBSD') ?
						'FreeBSD/' . (PHP_INT_SIZE * 8) . '/IPINClient' :
						'Linux/' . (PHP_INT_SIZE * 8) . '/IPINClient';
			exec('chmod a+x ' . __DIR__ . '/components/nice_ipin/lib/' . $path);
		}
	}

	function plugins_loaded()
	{
		$this->loader->load('namecheck');
		$this->loader->load('dbmembers', array('WPMEM_VERSION', 'DanbiMembers'));
		$this->loader->load('wpmembers', array('WPMEM_VERSION'));
		$this->loader->load('nice_checkplus');
		$this->loader->load('nice_ipin');
	}

	function scripts()
	{
		// wp_enqueue_style('dashicons');
		wp_enqueue_style('danbi-namecheck', plugins_url( 'assets/css/front.css', __FILE__ ), array('dashicons'));
	}
}

endif;

new Danbi_Namecheck;
