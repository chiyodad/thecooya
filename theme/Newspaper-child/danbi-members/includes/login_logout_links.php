<?php
if( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DP_Login_Logout_Links' ) ) :

class DP_Links_Items {
	public $db_id = 0;
	public $object = 'DP_Links_Items';
	public $object_id;
	public $parent_id;
	public $menu_item_parent = 0;
	public $type = 'custom';
	public $title;
	public $url;
	public $target = '';
	public $attr_title = '';
	public $classes = array();
	public $xfn = '';

	function __construct($object_id, $url, $title, $parent_id = 0) {
		$this->object_id = $object_id;
		$this->url = $url;
		$this->title = $title;
		$this->parent_id = $parent_id;
	}
}

class DP_Login_Logout_Links {

	function __construct() {
		if (is_admin()) {
			add_action('admin_head-nav-menus.php', array($this, 'add_nav_menu_metabox'));
		} else {
			add_filter('wp_setup_nav_menu_item', array($this, 'wp_setup_nav_menu_item'));
			add_filter('edit_profile_url', array($this, 'edit_profile_url'), 10, 3);
		}
	}

	function add_nav_menu_metabox() {
		add_meta_box( 'dp-members', __('User'), array($this,'nav_menu_metabox'), 'nav-menus', 'side', 'default' );
	}

	function nav_menu_metabox( $object ) {
		global $nav_menu_selected_id;

		$elems = array();
		$elems[] = new DP_Links_Items(1,'#dbmem-login#', __( 'Log in' ));
		$elems[] = new DP_Links_Items(2,'#dbmem-register#', __( 'Register' ));
		$elems[] = new DP_Links_Items(3,'#dbmem-profile#', '<i>' . __('User Name') . '</i> (' . __( 'Profile' ) . ')');
		$elems[] = new DP_Links_Items(4,'#dbmem-profile-edit#', __( 'Update Profile', 'wp-members'), 3);
		$elems[] = new DP_Links_Items(5,'#dbmem-password#', __( 'Change Password', 'wp-members'), 3);
		$elems[] = new DP_Links_Items(6,'#dbmem-wc-my-account#', sprintf('%s(%s)', __('My Account','woocommerce') , __( 'Woocommerce','woocommerce')), 3);
        $elems[] = new DP_Links_Items(7,'#dbmem-logout#', __( 'Log out' ));
        $elems[] = new DP_Links_Items(8,'#dbmem-withdrawal#', __('Withdrawal', 'danbi-members'));

		// $elems = array(
		// 	'#dpmem-login#'    => __( 'Log in' ),
		// 	'#dpmem-register#' => __( 'Register' ),
		// 	'#dpmem-profile#'  => __( 'Profile' ) . '(XXX님)',
		// 	'#dpmem-profile-edit#' => '회원정보',
		// 	'#dpmem-password#' => '비밀번호 변경',
		// 	'#dpmem-logout#'   => __( 'Log out' )
		// );

		// foreach ( $elems as $value => $title ) {
		// 	$elems_obj[$title] = new DP_Links_Items();
		// 	$elems_obj[$title]->object_id	= esc_attr( $value );
		// 	$elems_obj[$title]->title		= esc_attr( $title );
		// 	$elems_obj[$title]->url			= esc_attr( $value );
		// }

		$walker = new Walker_Nav_Menu_Checklist( array('id'=>'object_id', 'parent'=>'parent_id') );
		?>
		<div id="login-links" class="posttypediv">

			<div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $elems ), 0, (object)array( 'walker' => $walker ) ); ?>
				</ul>
			</div>

			<p class="button-controls">
				<span class="list-controls">
					<a href="/wp-admin/nav-menus.php?login-links-tab=all&amp;selectall=1#login-links" class="select-all"><?php _e('Select All'); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-login-links-menu-item" id="submit-login-links" />
					<span class="spinner"></span>
				</span>
			</p>

		</div>
		<?php
	}

	function wp_setup_nav_menu_item( $item ) {
		global $pagenow ;

		// if( $pagenow!='nav-menus.php' && !defined('DOING_AJAX') && isset( $item->url ) ) {
		if( $pagenow!='nav-menus.php' && isset( $item->url ) ) {
			if (strstr( $item->url, '#dbmem-' ) != '') {
				switch( $item->url ) {
					case '#dbmem-profile#' :
						if( is_user_logged_in() ) {
							$user_id      = get_current_user_id();
							$current_user = wp_get_current_user();
							$item->title = $this->replace_menu_title($item->title);
							$item->url = add_query_arg( array('a'=>'edit'), get_edit_profile_url( $user_id ) );
						} else
							$item->_invalid = true;
						break;
					case '#dbmem-profile-edit#' :
						if( is_user_logged_in() ) {
							$user_id      = get_current_user_id();
							$current_user = wp_get_current_user();
							$item->url = add_query_arg( array('a'=>'edit'), get_edit_profile_url( $user_id ) );
						} else
							$item->_invalid = true;
						break;
					case '#dbmem-password#' :
						if( is_user_logged_in() ) {
	                        $user_id      = get_current_user_id();
							$link = get_option('dbmembers_url_password');
							$item->url = ($link !== false) ?
    	                        	get_permalink($link) :
    							    add_query_arg( array('a'=>'pwdchange'), get_edit_profile_url( $user_id ) );
						} else {
							$item->_invalid = true;
                        }
						break;
					case '#dbmem-logout#' :
						if( is_user_logged_in() ) {
							$item->url = wp_logout_url( home_url() );
						} else
							$item->_invalid = true;
						break;
					case '#dbmem-login#' :
						if( !is_user_logged_in() ) {
							global $wp;
							$item->url = wp_login_url( add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
						} else
							$item->_invalid = true;
						break;
	                case '#dbmem-register#' :
	                    if( !is_user_logged_in() ) {
	                        $item->url = wp_registration_url();
	                    } else
	                        $item->_invalid = true;
						break;
	                case '#dbmem-withdrawal#' :
						if( is_user_logged_in() ) {
							$link = get_option('dbmembers_url_withdrawal');
							if ($link === false) {
		                        $item->_invalid = true;
							} else {
	                        	$item->url = get_permalink($link);
							}
	                    } else
	                        $item->_invalid = true;
						break;
	                case '#dbmem-wc-my-account#' :
						if( is_user_logged_in() ) {
	                        $item->url = get_permalink( get_option('woocommerce_myaccount_page_id') );
	                    } else
	                        $item->_invalid = true;
						break;
					default:
						break;
				}
				$item->url = esc_url( $item->url );
			} else if (in_array('dbmem-if-login', $item->classes) ) {
				if ( is_user_logged_in() )
					$item->title = $this->replace_menu_title($item->title);
				else
                	$item->_invalid = true;
            } else if (in_array('dbmem-if-logout', $item->classes) && is_user_logged_in() ) {
                $item->_invalid = true;
            }
		}
		return $item;
	}

	function replace_menu_title($title) {
		$user = wp_get_current_user();
		return str_replace(
			array('{{display_name}}', '{{user_login}}', '{{user_firstname}}', '{{user_lastname}}'),
			array($user->display_name, $user->user_login, $user->user_firstnamem, $user->user_lastname),
			$title
		);
	}

	function edit_profile_url( $url, $user_id, $scheme ) {
		return (WPMEM_MSURL != null && !defined(WPMEM_MSURL)) ? set_url_scheme(WPMEM_MSURL,$scheme) : $url;
	}

	function ends_with($haystack, $needle) {
    	// search forward starting from end minus needle length characters
    	return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
	}

}

endif;

new DP_Login_Logout_Links;
