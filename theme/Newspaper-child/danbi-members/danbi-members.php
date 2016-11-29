<?php
/*
 * Plugin Name: 단비 멤버스
 * Plugin URI: http://danbistore.com/item/danbi-members
 * Description: An extension of the WP-Members plugin providing the agreement of the TOS and privacy policy, and menus for login/logout/register.
 * Version: 3.1.0.3
 * Author: DanbiStore
 * Author URI: http://danbistore.com
 * License:
*/

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'DanbiMembers' ) ) :

/*
 * Register plugin to DanbiManager
 */
add_filter('danbistore_plugins', 'danbistore_plugins_danbi_members');
function danbistore_plugins_danbi_members($plugins) {
    $plugins['danbi-members'] = array('path' => __FILE__);
    return $plugins;
}

/*
 * 단비매니저 설치 알림
 */
add_action('admin_notices', 'danbimanager_danbi_members');
function danbimanager_danbi_members() {
    if (!class_exists('Danbi_Manager') && !defined('DANBI_MANAGER_NOTICE')) {
        echo '<div class="updated"><p><b><a href="http://danbistore.com/item/danbi-manager/" target="_blank" style="text-decoration: none;">단비 매니저</a></b> 플러그인 설치 후 라이센스 키를 입력하십시오. <b><a href="http://danbistore.com/item/danbi-manager/" target="_blank" style="text-decoration: none;">단비 매니저</a></b>는 단비스토어에서 구입하신 테마, 플러그인 업데이트를 제공합니다.</p></div>';
        define('DANBI_MANAGER_NOTICE', true);
    }
}

define('DBMEM_URL', plugin_dir_url( __FILE__ ));
define('DBMEM_DIR', plugin_dir_path( __FILE__ ));
define('DBMEM_INCLUDES', DBMEM_DIR . 'includes/');
define('DBMEM_ASSETS', DBMEM_URL . 'assets/');
define('DBMEM_ROLE_WITHDRAWER', 'withdrawer');
define('DANBI_MEMBERS_VERSION', '3.1');

class DanbiMembers {

    private $wp_members;

	function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action('plugins_loaded', array($this, 'plugins_loaded'));

        include_once DBMEM_INCLUDES . 'login_logout_links.php';

        if (is_admin()) {
            include_once DBMEM_DIR . 'admin.php';
        } else {
            add_shortcode('db-members', array($this, 'shortcode'));
            add_shortcode('danbi-members', array($this, 'shortcode'));
            add_filter('authenticate', array($this, 'authenticate'), 9999, 3);

			add_action('wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
            add_action('user_register', array($this, 'user_register_group' ) );
        }

        $loader = require_once 'components/component_loader.php';
        $loader->version = DANBI_MEMBERS_VERSION;
        // $loader->load('base');
        $loader->load('wpmem');
        $loader->load('wpmem_login');
        $loader->load('wpmem_register');
        $loader->load('dbmem_register');
        $loader->load('groups');
    }

    function wp_enqueue_scripts() {
        if (defined('WPMEM_VERSION')) {
		    wp_enqueue_style('dm-wp-members', DBMEM_ASSETS . 'css/wp-members.css', array('wp-members'), DANBI_MEMBERS_VERSION);
        }
		wp_enqueue_style('dm-front', DBMEM_ASSETS . 'css/front.css', array('wp-members'), DANBI_MEMBERS_VERSION);
	}

    function user_register_group( $user_id ) {
        if (isset($_POST['group_id']) && !empty($_POST['group_id']) && class_exists('Groups_User_Group')) {
            if ( !is_multisite() || is_user_member_of_blog( $user_id ) ) {
                Groups_User_Group::create(
                    array(
                        'user_id'  => $user_id,
                        'group_id' => $_POST['group_id']
                    )
                );
            }
        }
    }

    function activate() {
        load_plugin_textdomain('danbi-members', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        add_role( DBMEM_ROLE_WITHDRAWER, __(DBMEM_ROLE_WITHDRAWER, 'danbi-members' ));
    }

    function deactivate() {
        remove_role( DBMEM_ROLE_WITHDRAWER);
        remove_role( 'withdrawal');  // old role name
    }

    function plugins_loaded() {
        if (defined('WPMEM_VERSION')) {
			$this->wp_members = include_once DBMEM_INCLUDES . 'wp-members/wp-members.php';
		}
        load_plugin_textdomain('danbi-members', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

    function shortcode($atts)
    {
        $content = '';
        // Attributes
        extract( shortcode_atts(array('page'=>'', 'group'=>''), $atts));

        if (in_array($page, array('withdrawal','agreement','find_username'))) {
            $content = call_user_func(array($this, 'shortcode_page_' . $page));
        }

        if (!empty($group))
            do_action('dbmem_set_group', $group);

        return $content;
    }

    function shortcode_page_withdrawal() {
        $nonce = wp_create_nonce( 'danbimembers_withdrawal' );
        $redirect = home_url();
        $admin_ajax = admin_url( 'admin-ajax.php' );

        ob_start();
        ?>
        <div id="wpmem_reg">
            <form id="withdrawal-form" method="post" class="form">
            <fieldset>
                <input type="hidden" name="action" value="danbimembers_withdrawal" />
                <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
                <input type="hidden" id="redirect-url" value="<?php echo $redirect; ?>" />
                <label for="password" class="text"><?php _e('Password'); ?><span class="req">*</span></label>
                <div class="div_text"><input name="password" type="password" id="password" class="textbox"></div>
                <label for="reason" class="textarea"><?php _e('Withdrawal Reason', 'danbi-members'); ?><span class="req">*</span></label>
                <div class="div_textarea"><textarea cols="20" rows="5" name="reason" id="reason" class="textarea"></textarea></div>
                <div class="button_div"><input name="submit" type="submit" value="<?php _e('Withdraw', 'danbi-members'); ?>" class="buttons" id="withdrawal-btn"></div>
                </fiendset>
            </form>
        </div>
        <script type="text/javascript">
        jQuery(function($) {
            $('#withdrawal-btn').click(function() {
                if ($('#password').val() == '') {
                    alert('<?php printf(__('Sorry, %s is a required field.', 'wp-members'), __('Password')); ?>');
                } else if ($('#reason').val() == '') {
                    alert('<?php printf(__('Sorry, %s is a required field.', 'wp-members'), __('Withdrawal Reason', 'danbi-members')); ?>');
                } else {
                    $.post('<?php echo $admin_ajax; ?>', $('#withdrawal-form').serialize(), function(response) {
                        if (response.meta.code == 200) {
                            alert('<?php _e('Withdrawal has been processed successfully.', 'danbi-members'); ?>');
                            location.href = $('#redirect-url').val();
                        } else {
                            alert(response.meta.message);
                        }
                    }, 'json');
                }
                return false;
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    function shortcode_page_agreement()
    {
        // return $this->wp_members->get_agreement_page();
        return apply_filters('dbmem_register_page_agreement', '');
    }

    function shortcode_page_find_username()
    {
        global $wpmem;
        $wpmem->action = 'getusername';
        // return wpmem_do_sc_pages('members-area');
        return do_shortcode('[wpmem_profile]');
    }

    function wpmem_register_fields_arr($wpmem_fields, $toggle) {
        if ($toggle === 'new') {
            $group_fields = get_option('dbmembers_group_fields_' . $this->group);
            if (empty($group_fields))
                return $wpmem_fields;

            $fields = explode(',', str_replace(' ', '', $group_fields));
            print_r($fields);
            print_r($wpmem_fields);
            $new_fields = array();
            foreach( $wpmem_fields as $field ) {
                if (in_array($field[2],$fields))
                    $new_fields[] = $field;
            }
            return $new_fields;
        } else {
            $guser = new Groups_User(get_current_user_id());
            $groups = $guser->groups;
            print_r($groups);
        }
    }

    /*
     * 탈퇴한 회원 로그인 방지
     */
    function authenticate($user, $username, $password) {
        return ($user != null && is_a( $user, 'WP_User' ) && in_array(DBMEM_ROLE_WITHDRAWER, $user->roles)) ? null : $user;
    }

}

new DanbiMembers;

endif; // class_exists check
