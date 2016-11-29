<?php
/*
 * Plugin Name: 단비 본인인증
 * Plugin URI: http://danbistore.com/item/danbi-namecheck/
 * Description: NICE평가정보㈜의 본인인증, 아이핀 모듈을 이용하여 본인인증을 처리합니다.
 * Version: 1.0
 * Author: 단비스토어
 * Author URI: http://danbistore.com
 * License:
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Danbi_Namecheck' ) ) :

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
		$this->loader->load('dbmembers');
		$this->loader->load('wpmembers');
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
