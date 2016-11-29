<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'DM_WpMembers_Admin' ) ) :

define('DM_WPMEM_ADMIN_DIR', plugin_dir_path( __FILE__ ));

class DM_WpMembers_Admin {

	function __construct() {
		add_action('admin_init', array($this, 'admin_init') );
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts') );
		add_filter('user_search_columns', array($this, 'user_search_columns') , 10, 3);
		add_filter('manage_users_custom_column', array($this, 'manage_users_custom_column'), 10, 3);
		add_filter('manage_users_columns', array($this, 'manage_users_columns'));

		// add_action('wpmem_pre_admin_init', array($this, 'wpmem_pre_admin_init'));
		add_action('wpmem_admin_do_tab', array($this, 'wpmem_admin_do_tab'), 20);
		add_filter('wpmem_admin_tabs', array($this, 'wpmem_admin_tabs'), 20, 1);

		add_filter('plugin_action_links_danbi-members/danbi-members.php', array($this, 'plugin_action_links'), 10, 4);

		add_action('wp_ajax_dbmembers_update_settings_etc', array($this, 'update_settings_etc') );
		add_action('wp_ajax_dbmembers_update_settings_group', array($this, 'update_settings_group') );
		add_action('wp_ajax_dbmembers_create_pages', array($this, 'create_pages') );
		add_action('wp_ajax_dbmembers_dismiss_creating_pages', array($this, 'dismiss_creating_pages') );

	}

	function admin_init() {
	}

	function user_search_columns($search_columns, $search, $wp_user_query){
		//if(!in_array($search_columns, 'display_name')){
		if(!in_array('display_name', $search_columns)){
			$search_columns[] = 'display_name';
		}
		return $search_columns;
	}

	function manage_users_columns($sortable_columns) {
		$columns = array();
		foreach ( $sortable_columns as $column_name => $column_display_name ) {
			if ($column_name == 'name')
				$columns['display_name'] = __( 'Name' );
			else
				$columns[$column_name] = $column_display_name;

		}
		return $columns;
	}

	function manage_users_custom_column ( $now, $column_name, $uid) {
	  if ( $column_name === 'display_name' ) {
	    $user = new WP_User($uid);
	    $now = $user->display_name;
	  }
	  return $now;
	}

	function plugin_action_links($actions, $plugin_file, $plugin_data, $context) {
		return array_merge( array( '<a href="options-general.php?page=wpmem-settings&tab=etc">' . __( 'Settings' ) . '</a>' ), $actions );
	}

	function wpmem_admin_tabs($tabs) {
		if (defined('GROUPS_CORE_VERSION')) {
			$tabs['group'] = __('Groups', 'danbi-members');
		}
		$tabs['etc'] = __('Miscellaneous', 'danbi-members');
		return $tabs;
	}

	function wpmem_admin_do_tab( $tab ) {
		switch ( $tab ) {
		case 'group':
			include_once( ABSPATH . 'wp-content/plugins/wp-members/admin/tab-options.php' );
			include DM_WPMEM_ADMIN_DIR . 'tab-group.php';
			break;
		case 'etc':
			include_once( ABSPATH . 'wp-content/plugins/wp-members/admin/tab-options.php' );
			include DM_WPMEM_ADMIN_DIR . 'tab-etc.php';
			break;
		case 'fields':
			if (!isset($_GET['edit']))
				include DM_WPMEM_ADMIN_DIR . 'tab-fields.php';
			break;
		default:
			break;
		}
	}

	function enqueue_scripts() {
		wp_enqueue_style('dm-wpmem-adm',  DM_WPMEM_URL . 'css/admin.css');
	    wp_enqueue_script( 'dm-wpmem-adm', DM_WPMEM_URL . 'js/admin.js', array( 'jquery' ), DANBI_MEMBERS_VERSION);
	    wp_localize_script( 'dm-wpmem-adm', 'Ajax', array(
	        'url'       => admin_url( 'admin-ajax.php' )
	        )
	    );
	}

	function create_pages() {
		// check nonce
		if ( ! check_ajax_referer( 'dbmembers_update_settings', '_wpnonce' ) ) {
			die ( 'Busted!');
		}

		$this->insert_post(__('Log In'), 'login', '[wpmem_form login /]', 'wpmembers_settings.user_pages.login');
		$this->insert_post(__('Register'), 'register', '[wpmem_form register /]', 'wpmembers_settings.user_pages.register');
		$this->insert_post(__('Profile'), 'profile', '[wpmem_form user_edit /]', 'wpmembers_msurl');
		// $this->insert_post(__('Profile'), 'profile', '[wp-members page=user-profile]', 'wpmembers_msurl');
		$this->insert_post(__('Change Password', 'danbi-members'), 'password', '[wpmem_form password /]', 'dbmembers_url_password');
		$this->insert_post(__('Username') . ' ' . __('Find', 'danbi-members'), 'find-username', '[db-members page="find_username"]', 'dbmembers_url_find_username');
		$this->insert_post(__('Welcome','danbi-members'), 'welcome', __('Welcome to the wonderful world of xyz.com', 'danbi-members'), 'dbmembers_url_welcome');
		$this->insert_post(__('Terms of Service', 'wp-members'), 'terms', __('This is the Terms of Service page.', 'danbi-members'), 'dbmembers_url_terms');
        $this->insert_post(__('Privacy Policy', 'danbi-members'), 'privacy', __('This is the Privacy Policy page.', 'danbi-members'), 'dbmembers_url_privacy');
        $this->insert_post('개인정보 제3자 제공', 'privacy_3rd_party', '개인정보 제3자 제공 설명 페이지입니다.', 'dbmembers_url_3rd_party');
        $content = '<p style="margin-bottom:20px; margin-left:21%;"><span style="font-weight:bold; color:red;"> ' .
        	__('All of your information will be removed after withdrawal.','danbi-members') .
        	'</span><br />' .
        	__("We'd like to listen to your voice and make better services.",'danbi-members') .
    		'</p>[db-members page="withdrawal"]';
        $this->insert_post(__('Withdrawal', 'danbi-members'), 'withdrawal', $content, 'dbmembers_url_withdrawal');

		update_option('dbmembers_dismiss_creating_pages','1');

		echo '2';

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function dismiss_creating_pages() {
		// check nonce
		if ( ! check_ajax_referer( 'dbmembers_update_settings', '_wpnonce' ) ) {
			die ( 'Busted!');
		}

		update_option('dbmembers_dismiss_creating_pages','1');
		echo '0';

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function update_settings_etc() {
		// check nonce
		if ( ! check_ajax_referer( 'dbmembers_update_settings_etc', '_wpnonce' ) ) {
			die ( 'Busted!');
		}

        update_option( 'dbmembers_password_min_length', $_POST['dbmembers_password_min_length'], 'no' );
        update_option( 'dbmembers_check_unique', ( isset( $_POST['dbmembers_check_unique'] ) ) ? $_POST['dbmembers_check_unique'] : array(), 'no' );

		foreach (array('username_email', 'unique_username', 'force_login', 'confirm_password', 'confirm_mobile', 'notify_on_withdrawal',
				'ajax_register', 'email_html', 'show_clear_form', 'postcode_daum', 'login_redirect',
				'seperate_agreement', 'password_complex', 'withdrawal_delete_user') as $key) {
			update_option( 'dbmembers_' . $key, ( isset( $_POST['dbmembers_' . $key] ) ) ? '1' : '0', 'no' );
		}

		foreach (array('welcome','terms','privacy','3rd_party','withdrawal','agreement','userinfo','password','find_username') as $key) {
			if ( isset($_POST["dbmembers_page_$key"]) && $_POST["dbmembers_page_$key"] !== 'use_custom' )
				update_option('dbmembers_url_'.$key, $_POST["dbmembers_page_$key"], 'no');
			else if ( isset($_POST["dbmembers_url_$key"]) && $_POST["dbmembers_url_$key"] != 'http://' )
				update_option('dbmembers_url_'.$key, trim($_POST["dbmembers_url_$key"]), 'no');
		}

		do_action('dbmem_settings_etc_after_update');

		echo '1';

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function starts_with($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	function update_settings_group() {
		// check nonce
		if ( ! check_ajax_referer( 'dbmembers_update_settings_group', '_wpnonce' ) )
			die ( 'Busted!');

		foreach ($_POST as $name => $value) {
			if ($this->starts_with($name, 'dbmembers_group_fields_'))
				update_option( $name, $value, false);
		}

		echo '1';

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function insert_post($title, $name, $content, $option_name = '', $use_ssl = false) {
		$post = array(
			'post_title' => $title,
			'post_name' => $name,
			'post_content' => $content,
			'post_status' => 'publish',
			'post_type' => 'page',
			'ping_status' => 'closed',
			'comment_status' => 'closed'
			);
		$post_id = wp_insert_post($post);

		if (!empty($option_name)) {
			$permalink = get_permalink( $post_id );
			update_option($option_name, $post_id);
		}

		if ($use_ssl)
			update_post_meta($post_id, 'force_ssl', '1');
	}

	static function select_page($key, $title = '', $desc = '', $li_tag = true) {
		if (empty($title))
			$title = '&nbsp;';
		else
			$title .= ' ' . __('Page','danbi-members') . ':';
		$value = get_option('dbmembers_url_'.$key);
		$hide = '';
		if (!$value)
			$value = 'http://';
		else if (is_numeric($value)) {
			$value = get_page_link($value);
			$hide = 'style="display:none;"';
		}
		if ($li_tag)
			echo '<li class="dbmembers_page_' . $key . '">';
		echo '<label>' . $title . '</label><select name="dbmembers_page_' . $key . '" id="dbmembers_' . $key . '_select">';
		wpmem_admin_page_list($value);

		echo <<<EOT
			</select>
			<span class="description">$desc</span>
			<br>
			<div id="dbmembers_{$key}_custom" $hide>
				<label>&nbsp;</label>
				<input class="regular-text code" type="text" name="dbmembers_url_$key" value="$value" size="50" />
			</div>
EOT;
		echo ($li_tag ? '</li>' : '<div style="clear:both;"></div>');
	}

	static function select($name, $options, $value) {
		echo '<select name="' . $name . '">';
		foreach ($options as $option_value => $display) {
			echo '<option value="' . $option_value . '"';
			if ($value == $option_value)
				echo ' selected';
			echo '>' . $display;
		}
		echo '</select>';
	}

	static function checkbox($key, $title, $description) {
		$key = 'dbmembers_' . $key;
		$value = get_option($key);
		$checked = ($value == '1') ? 'checked' : '';
		if (empty($title))
			$title = '&nbsp;';
		echo <<<EOT
			<li>
				<div style="float:left;"><label>$title</label></div>
				<div style="float:left;"><input name="$key" type="checkbox" id="$key" value="1" $checked /></div>
				<div style="margin-left:190px;">
					<span class="description">$description</span>
				</div>
			</li>
EOT;
	}

	static function input_text($key, $title, $desc = '', $class = '') {
		$key = 'dbmembers_' . $key;
		$value = get_option($key);
		if (!empty($class))
			$class .= '-text';
		echo <<<EOT
			<li>
				<label>$title</label>
				<input name="$key" type="text" id="$key" value="$value" class="$class" />
				<span class="description">$desc</span>
			</li>
EOT;
	}
}

new DM_WpMembers_Admin;

endif; // class_exists check
