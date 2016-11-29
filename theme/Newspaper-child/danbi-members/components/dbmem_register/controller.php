<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Dbmem_Register_Controller extends Abstract_Controller
{
    public static $DUMMY_ROW;

    protected $component_id = 'dbmem_register';

    protected $option_prefix = 'dbmembers_';

    private $agreement_page = false;

    private $group_id = null;

    function __construct()
    {
        parent::__construct(__FILE__);

        self::$DUMMY_ROW = array('type' => '', 'row_before' => '', 'field_before' => '', 'field_after' => '', 'row_after' => '', 'label' => '');

        $this->wp_add_filter('register_url');
        // $this->wp_add_filter('send_password_change_email', 10, 3);

        $this->add_filter('validate_form');
        $this->add_filter('page_agreement');
        $this->add_filter('fields_arr', 999, 2);
        $this->add_filter('hidden_fields', 10, 2);
        $this->add_filter('form_args', 10, 2);
        $this->add_filter('form_before', 10, 2);
        $this->add_filter('form_rows', 20, 2);
        $this->add_filter('form_rows', 30, 2, 'form_rows_agreement');
        $this->add_filter('form_rows', 10, 2, 'form_rows_username_email');

        $this->add_ajax_nopriv('check_unique_field');
        $this->add_ajax('check_unique_field');

        $this->add_ajax_nopriv('ajax_register');
        $this->add_ajax('ajax_update');
        $this->add_ajax('password_check');

		$this->add_action('pre_data', 10, 1, 'validate_agreement');
		$this->add_action('pre_data', 20, 1, 'validate_username_email');
		$this->add_action('pre_data', 80, 1, 'validate_password');
    }

    function validate_form($fields)
    {
        if ($this->get_option('username_email') === '1')
            $fields['user_email'] = $fields['username'];
        return $fields;
    }

    function send_password_change_email($email, $user, $userdata)
    {
        return '';
    }

    function fields_arr($fields, $toggle)
    {
        if ($this->get_option('username_email') === '1') {
            $new_fields = array();
            foreach ( $fields as $field ) {
                if ($field[2] !== 'user_email' && $field[2] !== 'confirm_email')
                    $new_fields[] = $field;
            }
            $fields = $new_fields;
        }

        return $fields;
    }

	function register_url( $register_url )
    {
        if ($this->get_option('seperate_agreement') === '1') {
            $page = $this->get_option('url_agreement', '');
            if (is_numeric($page))
    			$page = get_page_link($page);
            if (!empty($page))
                return $page;
        }
        return (WPMEM_REGURL != null && !defined(WPMEM_REGURL)) ? WPMEM_REGURL : $register_url;
	}

    function page_agreement($content = '')
    {
        global $wpmem;

        define(WPMEM_CAPTCHA, 0);

        $this->agreement_page = true;

        $this->remove_filter('form_rows');
        $this->remove_filter('hidden_fields');

        $this->add_filter('form_args', 20, 2, 'page_agreement_form_args');
        $this->add_filter('form_rows', 10, 2, 'page_agreement_clear_rows');
        $this->add_filter('fields_arr', 10, 2, 'page_agreement_clear_fields');
        // $this->add_filter('hidden_fields', 10, 2, 'agreement_page_hidden_fields');

        // $this->script('agreement');

        // $_REQUEST['redirect_to'] = get_permalink($wpmem->user_pages['register']);
        $_REQUEST['redirect_to'] = $wpmem->user_pages['register'];
        $content = do_shortcode('[wpmem_form register]');
        // $content = do_shortcode('[wp-members page="register"]');
        return $content;
    }

    function page_agreement_form_args($args = array(), $toggle)
    {
        $args['submit_register'] = __('Next');
        $args['form_class'] = 'agreement-form';
    	return $args;
    }

    function page_agreement_clear_fields($fields, $toggle)
    {
    	return array();
    }

    function page_agreement_clear_rows($rows, $toggle)
    {
    	return array();
    }

    function agreement_page_form($form, $toggle, $rows, $hidden )
    {
    	return preg_replace('/action="[^"]+"/i', 'action="' . get_permalink(get_option('wpmembers_regurl','')) . '"', $form);
    }

	function is_seperate_agreement() {
		return get_option('dbmembers_seperate_agreement') === '1';
	}

	function form_args($args = array(), $toggle = '') {
		$args = array(
			'heading_before'   => '',
			'heading_after'    => '',
            'form_class'       => 'register-form'
		);
		if (get_option('dbmembers_show_clear_form') === '1') {
            $args['show_clear_form'] = true;
        } else {
        	$args['show_clear_form'] = false;
        }

		return $args;
	}
/*
        	if (!isset($_REQUEST['check_dbmembers_url_terms']) && !isset($_REQUEST['check_dbmembers_url_privacy'])) {
        		$hidden = '<script type="text/javascript">alert("약관 동의를 먼저 해주십시오.");location.href="' . wp_registration_url() . '";</script>';
	        } else {
*/
	function form_rows($rows, $toggle)
    {
        $new_rows = array();
		$addr_fields = array('zip','addr1','billing_postcode','billing_address_1');

		$postcode_daum = get_option( 'dbmembers_postcode_daum', '0' );
		$unique_username = get_option( 'dbmembers_unique_username', '0' );
		$unique_fields = get_option('dbmembers_check_unique', array());

		if ($toggle == 'new' && !$this->is_seperate_agreement()) {
            $new_rows[] = $this->make_row('<legend class="user-info-heading">' . __('User Information', 'danbi-members') . '</legend>');
        }

 		foreach( $rows as $row ) {
            if ($toggle === 'new' && $unique_username === '1' && $row['meta'] === 'username') {
                $label_text = isset($row['label_text']) ? $row['label_text'] : '';
				$row['field'] = preg_replace('/class="([^"]*)"/i', 'class="$1 unique-field"', $row['field']) .
                    sprintf('<input type="button" id="check-username-btn" value="%s" class="d_btn adjust-height-btn check-unique-btn" data-nonce="%s" data-label="%s" data-field="user_login">',
    					__('중복 확인', 'danbi-members'),
    					$this->create_nonce('check_unique_field'),
    					$label_text
    				);
            } else if (in_array($row['meta'], $unique_fields)) {
                $label_text = isset($row['label_text']) ? $row['label_text'] : '';
				$row['field'] = preg_replace('/class="([^"]*)"/i', 'class="$1 unique-field"', $row['field']) .
	                sprintf('<input type="button" value="%s" class="d_btn adjust-height-btn check-unique-btn" data-nonce="%s" data-label="%s" data-field="%s">',
						__('중복 확인', 'danbi-members'),
                        $this->create_nonce('check_unique_field'),
						$label_text,
						$row['meta']
					);
            } else if ($postcode_daum === '1') {
				if (in_array($row['meta'], $addr_fields))
					$row['field'] = str_replace('>', 'readonly >', $row['field']);
				if ($row['meta'] == 'zip')
					$row['field'] .= '<input type="button" value="' . __('Search postcode', 'danbi-members') . '" class="d_btn adjust-height-btn" onclick="openDaumPostcode(\'zip\',\'addr1\',\'addr2\');">';
				if ($row['meta'] == 'billing_postcode')
					$row['field'] .= '<input type="button" value="' . __('Search postcode', 'danbi-members') . '" class="d_btn adjust-height-btn" onclick="openDaumPostcode(\'billing_postcode\',\'billing_address_1\',\'billing_address_2\');">';
			}
			$new_rows[] = $row;
			/*
			if ($confirm_mb == '1' && $row['meta'] == 'mobile') {
				$nonce_key = substr(sha1(rand()), 0, 10);
				$nonce_secret = wp_create_nonce( 'confirm_mobile' . $nonce_key);
				$field = <<<EOD
<label class="text">?´ë??„í™”ë²ˆí˜¸ ?¸ì¦</label><font class="req" style1="float:left;">*</font>
<div class="div_text" style="width:100%;">
	<input type="text" id="mobile_confirm" name="mobile_confirm" /> <button id="send-mobile-confirm" style="background:grey;">?¸ì¦ë²ˆí˜¸ ?„ì†¡</button> <br/>
	<span id="mobile-confirm-msg" style="display:none;">?´ë??„í™”ë¡??„ì†¡???¸ì¦ë²ˆí˜¸ë¥??…ë ¥??ì£¼ì‹­?œì˜¤.</span>
</div>
<input type="hidden" name="_wpnonce_key" value="$nonce_key" />
<input type="hidden" name="_wpnonce_secret" value="$nonce_secret" />
EOD;
				$new_rows[] = array('field' => $field);
			}
			*/
		}

		// array_splice( $rows, 0, 0, $new_rows );

		// print_r($rows); die;

		return $new_rows;
    }

    function form_rows_username_email($rows, $toggle = 'new')
    {
        if ($toggle !== 'new' || $this->get_option('username_email') !== '1')
            return $rows;

        $new_rows = array();
 		foreach( $rows as $row ) {
            if ($row['meta'] !== 'user_email' && $row['meta'] !== 'confirm_email') {
                $new_rows[] = $row;
			}
        }

        return $new_rows;
    }

    function form_rows_agreement($rows, $toggle = 'new')
    {
		if (($toggle == 'new' && !$this->is_seperate_agreement()) || $this->agreement_page) {
            $new_rows = array();
			$terms_row = $this->agreement('dbmembers_url_terms');
			$privacy_row = $this->agreement('dbmembers_url_privacy');
			$party_row = $this->agreement('dbmembers_url_3rd_party');
			if (!empty($terms_row) || !empty($privacy_row) || !empty($party_row)) {
                if (!$this->is_seperate_agreement())
				    $new_rows[] = $this->make_row('<legend>' . __('Agreement', 'danbi-members') . '</legend>');
				if (!empty($terms_row))
					$new_rows[] = $this->make_row($terms_row);
				if (!empty($privacy_row))
					$new_rows[] = $this->make_row($privacy_row);
				if (!empty($party_row))
					$new_rows[] = $this->make_row($party_row);
				$new_rows[] = $this->make_row('</fieldset><fieldset>');
			}
            $rows = array_merge($new_rows, $rows);
        }

        return $rows;
    }

	function agreement($option_name) {

		$postid = get_option($option_name);
		if (!$postid)
			return;

		if (!is_numeric($postid))
			$postid = url_to_postid($postid);

		if ($postid == 0)
			return;

		$post = get_post($postid);
        $content = wpautop(wptexturize($post->post_content));
        $agree_to = sprintf( __('I agree to %s.', 'danbi-members'), $post->post_title);
        $agree_alert = sprintf( __('Please agree to %s.', 'danbi-members'), $post->post_title);

        $context = array(
            'post' => $post,
            'option_name' => $option_name
        );
        return $this->view_contents('agreement', $context);
/*
		return <<<EOD
		<div class="agreement-box" style="margin-bottom:14px; clear:both;">
			<label class="text"><b>$post->post_title</b></label>
			<div class="div_textarea" style="height:150px; overflow-y:auto; border:1px solid darkgrey; padding:5px;">$content</div>
			<label style="display: inline-block;">
				<input type="checkbox" id="agree-to-$postid" class="agreement" name="check_$option_name" value="true"
					style="vertical-align:middle; margin-right:5px;" data-title="$post->post_title" data-agree-alert="$agree_alert" />
				<span style="vertical-align: middle">$agree_to</span>
				<span class="req">*</span>
			</label>
		</div>
EOD;
*/
	}

	function make_row($text)
	{
		return array_merge(array('field'=>$text), self::$DUMMY_ROW);
	}

    function hidden_fields($hidden, $toggle)
    {
    	if ($this->group_id !== NULL) {
            $hidden .= '<input type="hidden" name="group_id" value="' . $this->group_id . '" />';
        }

        if ($toggle === 'new' && $this->is_seperate_agreement()) {
            foreach (array('terms','privacy','3rd_party') as $item) {
                $agree = $this->agreement('dbmembers_url_' . $item);
                $key = 'check_dbmembers_url_' . $item;
            	if (!empty($agree)) {
                    if (isset($_REQUEST[$key])) {
        	    		$hidden .= sprintf('<input type="hidden" name="%s" value="%s">', $key, $_REQUEST['check_dbmembers_url_terms']);
                    } else {
        	    		$hidden .= '<input type="hidden" class="uncheck-agreement" value="true">';
                        return $hidden;
                    }
                }
            }
        }

        if ($this->get_option('username_email') === '1') {
            if ($toggle === 'new') {
                $hidden .= '<input type="hidden" name="user_email">';
            } else {
                $current_user = wp_get_current_user();
                $hidden .= '<input type="hidden" name="user_email" value="' . $current_user->user_email . '">';
            }
        }

        if (get_option('dbmembers_ajax_register') !== '1')
            return $hidden;

        $action = ($toggle === 'new') ? 'ajax_register' : 'ajax_update';

        ob_start();
        $this->nonce_action_field($action);
        $contents = ob_get_contents();
        ob_end_clean();

        return $hidden . $contents;
    }

	function form_before($content, $toggle = '')
    {
        $local = array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        );

        $this->script('register');

        if ($toggle === 'new') {
            $local['MESSAGE_PASSWORD_MISMATCH'] = __('The passwords do not match.', 'danbi-members');
            if ($this->is_seperate_agreement()) {
                $local['agreement_page'] = get_page_link($this->get_option('url_agreement'));
            }
        }
        if ($this->get_option('ajax_register') === '1') {
            $this->p_script('jquery.form.min');
            $this->script('ajax-register', array('jquery', 'jquery.form.min'));
        }

        $this->localize('register', 'DanbiMembers', $local);

        if ($this->get_option('postcode_daum') === '1') {
            $this->script('postcode');
        }

		return $content;
	}

    function check_unique_field()
    {
        $field = $_REQUEST['field'];
        $value = $_REQUEST['field_value'];
        $user = false;

        switch ($field) {
            case 'user_login':
                $user = get_user_by( 'login', $value);
                if ($user === FALSE && $this->get_option('username_email') === '1')
                    $user = get_user_by('email', $value);
                break;
            case 'user_email':
                $user = get_user_by('email', $value);
                break;
            default:
                $users = get_users(array('meta_key'=>$field, 'meta_value'=>$value, 'number'=>1));
                $user = empty($users) ? FALSE : $users[0];
                break;
        }

        if (is_user_logged_in()) {
            if (get_current_user_id() == $user->ID)
                $user = FALSE;
        }

        if ($user === FALSE) {
            if (($field === 'user_login' && $this->get_option('username_email') === '1') || $field === 'user_email') {
                if (is_email($value)) {
                    $this->ajax_success();
                } else {
                    global $wpmem;
                    $this->ajax_failure($wpmem->get_text('reg_valid_email'));
                }
            }
            $this->ajax_success();
        } else {
            $this->ajax_failure('이미 사용 중입니다.');
        }
    }

    function ajax_register()
    {
        global $wpmem, $wpmem_themsg;

		$regchk = $wpmem->get_regchk('register');
		if ($regchk === 'success') {
            $welcome = get_option( 'dbmembers_url_welcome' );
            if (empty($welcome))
                $this->ajax_success('회원가입이 완료되었습니다.', esc_url(home_url( '/' )));
            else
                $this->ajax_success('', esc_url(get_permalink($welcome)));
        } else {
			include_once WPMEM_PATH . 'inc/dialogs.php';
			$msg = wpmem_inc_regmessage( $regchk, $wpmem_themsg);
            $this->ajax_failure($msg);
		}
    }

    function ajax_update()
    {
        global $wpmem, $wpmem_themsg;

		$regchk = $wpmem->get_regchk('update');
		if ($regchk === 'editsuccess') {
            $this->ajax_success('회원정보가 수정되었습니다.', $_REQUEST['_wp_http_referer']);
        } else {
			include_once WPMEM_PATH . 'inc/dialogs.php';
			$msg = wpmem_inc_regmessage( $regchk, $wpmem_themsg);
            $this->ajax_failure($msg);
		}
    }

	function wpmem_register_captcha_row($form, $toggle) {
		return '<label class="text">' . __('CAPTCHA', 'danbi-members') . '</label>' . $form;
	}

	function validate_agreement($fields) {
		if (!$this->check_agreement('dbmembers_url_terms'))
			return;

		if (!$this->check_agreement('dbmembers_url_privacy'))
			return;

		if (!$this->check_agreement('dbmembers_url_3rd_party'))
			return;
	}

	function check_agreement($option_name) {
		global $wpmem_themsg;
		$url = get_option($option_name);
		if (!$url)
			return true;

		$postid = url_to_postid($url);
		if ($postid == 0)
			return true;

		if (isset($_POST['check_' . $option_name]) && $_POST['check_' . $option_name] == 'true')
			return true;

		$post = get_post($postid);
        $wpmem_themsg = sprintf( __('Please agree to %s.', 'danbi-members'), $post->post_title);
        return false;
	}

	function validate_username_email($fields = array()) {
        global $wpmem_themsg;

        if ($this->get_option('username_email') === '1' && !is_email($fields['username'])) {
            global $wpmem;
            $wpmem_themsg = $wpmem->get_text('reg_valid_email');
        }
	}

	function validate_password($fields = array()) {
        global $wpmem_themsg;
        if (isset($fields['password'])) {
            $msg = $this->check_password($fields['password']);
            if ($msg !== FALSE)
                $wpmem_themsg = $msg;
        }
	}

    function password_check() {
        $msg = FALSE;

        if (!isset($_POST['pass1']) || empty($_POST['pass1']) || !isset($_POST['pass2']) || empty($_POST['pass2'])) {
            $msg = '비밀번호를 입력하십시오.';
        } else {
            $pass1 = $_POST['pass1'];
            $pass2 = $_POST['pass2'];
            if ($pass1 !== $pass2) {
                $msg = '비밀번호가 일치하지 않습니다.';
            } else {
                $msg = $this->check_password($pass1);
            }
        }

        if ($msg)
            echo $msg;

        die();
    }

	function check_password($password) {
        $msg = FALSE;

        $min_length = intval(get_option('dbmembers_password_min_length', '0'));
        $complex = get_option('dbmembers_password_complex', '0');

        if ($min_length > 0 && $complex === '1') {
            if (!preg_match_all('$\S*(?=\S{' . $min_length . ',})(?=\S*[a-zA-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)) {
                $msg = '비밀번호는 영문, 숫자, 특수문자 포함 ' . $min_length . '자 이상입니다.';
            }
        } else if (strlen($password) < $min_length) {
            $msg = '비밀번호는 ' . $min_length . '자 이상입니다.';
        } else if ($complex === '1' && !preg_match_all('$\S*(?=\S*[a-zA-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)) {
            $msg = '비밀번호에는 영문, 숫자, 특수문자가 포함되어야 합니다.';
        }

        return $msg;
	}
}

return new Dbmem_Register_Controller;
