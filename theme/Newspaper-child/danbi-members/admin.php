<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'DanbiMembersAdmin' ) ) :

class DanbiMembersAdmin {

	function __construct() {
		add_filter('user_search_columns', array($this, 'user_search_columns') , 10, 3);
		add_filter('manage_users_custom_column', array($this, 'manage_users_custom_column'), 10, 3);
		add_filter('manage_users_columns', array($this, 'manage_users_columns'));
        add_action('wp_ajax_danbimembers_withdrawal', array($this, 'withdraw'));
        add_action('edit_user_profile', array($this, 'edit_user_profile'), 20);
    }

	function init() {
	}

	function enqueue_scripts() {
	}

	function user_search_columns($search_columns, $search, $wp_user_query){
		if(!in_array('display_name', $search_columns)){
			$search_columns[] = 'display_name';
		}
		return $search_columns;
	}

	function manage_users_custom_column ( $now, $column_name, $uid) {
	  if ( $column_name === 'display_name' ) {
	    $user = new WP_User($uid);
	    $now = $user->display_name;
	  }
	  return $now;
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

    function ajax_failure($message, $code = 0) {
        $this->ajax_result($code,$message);
    }

    function ajax_success($data=null) {
        $this->ajax_result(200,null,$data);
    }

    function ajax_result($code, $message, $data=null) {
        $meta = array('code'=>$code);
        if ($message != null)
            $meta['message'] = $message;

        $result = array('meta'=>$meta);
        if ($data != null)
            $result['data'] = $data;

        echo json_encode($result);
        die();
    }

    function withdraw()
    {
        if (!is_user_logged_in())    
            $this->ajax_failure(__('You have access to the wrong path.', 'danbi-members') );

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'danbimembers_withdrawal' ) )
            $this->ajax_failure(__('You have access to the wrong path.', 'danbi-members') );

        $user = wp_get_current_user();
        $old_pass = wp_slash( $_POST['password'] );

        if (!wp_check_password($old_pass, $user->user_pass, $user->ID))
            $this->ajax_failure(__('Passwords do not match.', 'danbi-members'));

        $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

        if (get_option('dbmembers_notify_on_withdrawal') === '1')
            $this->notify_admin_on_withdrawal($user, $reason);

        if (get_option('dbmembers_withdrawal_delete_user') === '1') {
            require_once(ABSPATH.'wp-admin/includes/user.php' );
            wp_delete_user($user->ID);
        } else {
            update_user_meta( $user->ID, 'withdrawal_reason', $reason);
            $user->set_role(DBMEM_ROLE_WITHDRAWER);
        }

        wp_clear_auth_cookie();

        $this->ajax_success();
    }

    function notify_admin_on_withdrawal( $user, $reason ) {
        $blogname = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );

        $act_link = admin_url('user-edit.php?user_id=' . $user->ID);

        $subj = "[$blogname] " . __('withdrawal', 'danbi-members');
        $body = __('Username') . ": {$user->user_login}\r\n" . __('Reason', 'danbi-members') . ":\r\n$reason\r\n\r\n" . __('User Profile') . ": $act_link";

        /* Apply filters (if set) for the sending email address */
        add_filter( 'wp_mail_from', 'wpmem_mail_from' );
        add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );

        /**
         * Filters the address the admin notification is sent to.
         *
         * @since 2.7.5
         *
         * @param string The email address of the admin to send to.
         */
        $admin_email = apply_filters( 'wpmem_notify_addr', get_option( 'admin_email' ) );
        /**
         * Filters the email headers.
         *
         * @since 2.7.4
         *
         * @param mixed The email headers (default = null).
         */
        $headers = apply_filters( 'wpmem_email_headers', '' );

        /* Send the message */
        wp_mail( $admin_email, stripslashes( $subj ), stripslashes( $body ), $headers );
    }

    function edit_user_profile($profileuser) {
        if (!empty($profileuser->withdrawal_reason)): ?>
            <h3><?php _e('DanbiMembers Extra Fields', 'danbi-members'); ?></h3>
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label><?php _e('Withdrawal Reason', 'danbi-members'); ?></label></th>
                    <td>
                        <textarea cols="20" rows="5" class="textarea" readonly><?php echo $profileuser->withdrawal_reason; ?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        endif;
    }
}

new DanbiMembersAdmin;

endif; // class_exists check