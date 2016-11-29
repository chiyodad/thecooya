<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'Danbi_Manager_Admin' ) ) return 1;

class Danbi_Manager_Admin {

	private $plugins;

	private $themes;

    function __construct() {
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_danbi_manager_activate_license', array($this, 'activate_license'));
        add_action('wp_ajax_danbi_manager_deactivate_license', array($this, 'deactivate_license'));
	}

	function init() {
		$this->plugins = array();
		$this->themes = array();

		$plugins = apply_filters('danbistore_plugins', array());

		if (!empty($plugins)) {
			include_once dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php';
			foreach ($plugins as $id => $plugin) {
				$data = get_plugin_data($plugin['path'], false, true);
				$data['ID'] = $id;
				if (isset($plugin['license']))
					$data['license'] = $plugin['license'];
				$this->plugins[$data['Name']] = $data;
				$danbi_plugin = array(
					'version' 	=> $data['Version'], 				// current version number
					'license' 	=> isset($plugin['license']) ? $plugin['license'] : danbi_manager()->get_license_key($id), 		// license key (used get_option above to retrieve from DB)
					'item_name' => $data['Name'], 	// name of this plugin
					'author' 	=> $data['Author']  // author of this plugin
				);
				new Danbi_EDD_SL_Plugin_Updater( DANBISTORE_URL, $plugin['path'], $danbi_plugin);
			}
		}
		$themes = apply_filters('danbistore_themes', array());
		if (!empty($themes)) {
			include_once dirname( __FILE__ ) . '/theme-updater-class.php';
			$theme_root = get_theme_root();
			foreach ($themes as $id => $template_dir) {
				$dir = dirname($template_dir);
				$stylesheet = substr($dir, strrpos($dir, DIRECTORY_SEPARATOR) + 1);
				$theme = wp_get_theme($stylesheet);
				$this->themes[$theme->Name] = array(
					'ID' => $id,
					'Name' => $theme->Name
				);
				$danbi_theme = array(
					'remote_api_url' => DANBISTORE_URL,
					'version' 	 => $theme->Version, 				// current version number
					'license' 	 => danbi_manager()->get_license_key($id), 		// license key (used get_option above to retrieve from DB)
					'item_name'  => $theme->Name, 	// name of this plugin
					'author' 	 => $theme->get('Author'),  // author of this plugin
					'theme_slug' => $id
				);
				new EDD_Theme_Updater( $danbi_theme );
			}
		}

		// register_setting('danbi_manager_license', 'danbi_plugins_license', array($this, 'sanitize_license'));
		add_action('load-plugins.php', array($this, 'plugins_php'));
	}

	function admin_notices() {
		$license = danbi_manager()->get_license();
		$invalid_items = array();
		foreach ($this->plugins as $name => $data) {
			if (!isset($data['license'])) {
				$id = $data['ID'];
				if (!isset($license[$id]) || $license[$id]['status'] !== 'valid') {
					$invalid_items[] = $name;
				}
			}
		}
		foreach ($this->themes as $name => $data) {
			if (!isset($data['license'])) {
				$id = $data['ID'];
				if (!isset($license[$id]) || $license[$id]['status'] !== 'valid') {
					$invalid_items[] = $name;
				}
			}
		}
		if (!empty($invalid_items))
			echo '<div class="updated" style="border-color: #02bbd7;"><p><b><a href="http://danbistore.com" target="_blank" style="border-color: #02bbd7;">단비스토어</a></b>를 이용해 주셔서 감사합니다. ' . implode(', ', $invalid_items) . '</b>의 업데이트를 위해  <b><a href="' . admin_url('admin.php?page=danbi_manager&tab=license') . '">라이센스 키 입력</a></b> 후 활성화 해주십시오. </p></div>';
	}

	function plugins_php() {
		$license = danbi_manager()->get_license();
		foreach ($this->plugins as $name => $plugin) {
			if (isset($plugin['license']) && !isset($license[$plugin['ID']]))
				$this->activate($plugin['license'], $plugin['ID'], $plugin['Name']);
		}
	}

	function sanitize_license( $new ) {
		// $old = get_option( 'edd_sample_license_key' );
		// if( $old && $old != $new ) {
		// 	delete_option( 'edd_sample_license_status' ); // new license has been entered, so must reactivate
		// }
		return $new;
	}

	function admin_menu() {
		$menu_page = add_menu_page('단비 매니저', '단비 매니저', 'manage_options', Danbi_Manager::$PLUGIN_ID, array($this, 'menu_page'), 'none');
		add_action('admin_print_scripts-' . $menu_page, array( $this, 'load_scripts' ) );
  		add_action('admin_print_styles-' . $menu_page, array( $this, 'load_styles' ) );
	}

	function admin_enqueue_scripts() {
		wp_enqueue_style( 'danbi-manager-icon', Danbi_Manager::$PLUGIN_URL . 'css/icon.css', false, Danbi_Manager::$VERSION, 'all' );
	}

	function load_scripts() {
		wp_enqueue_script('danbi-manager', Danbi_Manager::$PLUGIN_URL . 'js/admin.js', array( 'jquery' ) );
		wp_enqueue_script('jquery-purl', Danbi_Manager::$PLUGIN_URL . 'js/purl.js', array( 'jquery' ) );
	}

	function load_styles() {
		wp_enqueue_style( 'danbi-manager', Danbi_Manager::$PLUGIN_URL . 'css/style.css', false, Danbi_Manager::$VERSION, 'all' );
	}

	function menu_page() {
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'license';

        include_once 'menu_page.php';
	}

	function activate($key, $item_id, $item_name) {

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $key,
			'item_name' => urlencode( $item_name ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		// $response = wp_remote_post( DANBISTORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		$response = wp_remote_post( DANBISTORE_URL,
			array(
				// 'headers' => array(
				// 	'Authorization' => 'Basic ' . base64_encode( 'danbi:danbi' )
				// ),
				'timeout' => 15, 'sslverify' => false, 'body' => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			$response = "오류가 발생했습니다. 잠시 뒤에 다시 시도해주십시오.\n" . $response['response']['message'];
		} else {
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			// $license_data->license will be either "valid" or "invalid"

			danbi_manager()->set_license($item_id, array(
				'status' => $license_data->license,
				'expires' => $license_data->expires,
				'key' => $key
			));

			$response = $license_data->license;
		}

		return $response;
	}

	function activate_license() {
		// run a quick security check
	 	// if( ! check_admin_referer( 'danbi_manager_license', 'danbi_manager_license' ) )
			// return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$key = isset($_POST['key']) ? trim($_POST['key']) : '';
		$item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
		$item_name = isset($_POST['item_name']) ? $_POST['item_name'] : '';

		$license = danbi_manager()->get_license();
		$license[$item_id] = array('key' => $key);

		echo $this->activate($key, $item_id, $item_name);

		die;
	}

	function deactivate_license() {
		// run a quick security check
	 	// if( ! check_admin_referer( 'danbi_manager_license', 'danbi_manager_license' ) )
			// return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$key = isset($_POST['key']) ? trim($_POST['key']) : '';
		$item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
		$item_name = isset($_POST['item_name']) ? $_POST['item_name'] : '';

		$license = danbi_manager()->get_license();
		$license[$item_id] = array('key' => $key);

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $key,
			'item_name' => urlencode( $item_name ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		// $response = wp_remote_post( DANBISTORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		$response = wp_remote_post( DANBISTORE_URL,
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'danbi:danbi' )
				),
				'timeout' => 15, 'sslverify' => false, 'body' => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			echo "오류가 발생했습니다. 잠시 뒤에 다시 시도해주십시오.\n" . $response['response']['message'];
		} else {
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			// $license_data->license will be either "valid" or "invalid"

			danbi_manager()->remove_license($item_id);

			echo 'valid';
		}

		die;
	}
}

return new Danbi_Manager_Admin;
