<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Dbmembers_Controller extends Abstract_Controller
{
    protected $id = 'dbmem';

    protected $settings;

    private $decode = null;

    function __construct()
    {
        parent::__construct(__FILE__);
        $this->add_action('settings_etc_before_register');
        $this->add_action('settings_etc_after_register');
        $this->add_filter('settings_namecheck', 10, 2);
        $this->add_filter('register_form_rows', 999, 2);
        $this->add_wp_action('show_user_profile', 10, 1);
        $this->add_wp_action('edit_user_profile', 10, 1);
    }

    function settings_etc_before_register()
    {
        ?>
        <h3><?php _e('Register'); ?> - 본인인증</h3>
        <ul>
            <?php
            \DM_WpMembers_Admin::checkbox('nice_checkplus', '핸드폰 인증', 'NICE 본인인증의 핸드폰 인증을 사용합니다.');
            \DM_WpMembers_Admin::checkbox('nice_ipin', '아이핀 인증', 'NICE 아이핀 인증을 사용합니다.');
            \DM_WpMembers_Admin::checkbox('namecheck_save', '사용자 정보 저장', '본인인증 후 사용자의 이름, 생년월일, 성별, 국적을 저장합니다. 저장 시에는 개인정보 보호정책에 관련 내용을 추가하십시오.');
            ?>
        </ul>
        <?php
    }

    function settings_etc_after_register()
    {
        ?>
        <h3><?php _e('Profile'); ?> - 본인인증</h3>
        <ul>
            <?php
            \DM_WpMembers_Admin::checkbox('profile_nice_checkplus', '핸드폰 인증', 'NICE 본인인증의 핸드폰 인증을 사용합니다.');
            \DM_WpMembers_Admin::checkbox('profile_nice_ipin', '아이핀 인증', 'NICE 아이핀 인증을 사용합니다.');
            \DM_WpMembers_Admin::checkbox('profile_namecheck_save', '사용자 정보 저장', '본인인증 후 사용자의 이름, 생년월일, 성별, 국적을 저장합니다. 저장 시에는 개인정보 보호정책에 관련 내용을 추가하십시오.');
            ?>
        </ul>
        <?php
    }

    function settings_namecheck($settings = false, $toggle = 'new')
    {
        if ($toggle == 'new') {
            $checkplus = get_option('dbmembers_nice_checkplus', '0');
            $ipin = get_option('dbmembers_nice_ipin', '0');
        } else {
            $checkplus = get_option('dbmembers_profile_nice_checkplus', '0');
            $ipin = get_option('dbmembers_profile_nice_ipin', '0');
        }
        if ($checkplus === '1' || $ipin === '1') {
            $settings = array();
            if ($checkplus === '1')
                $settings['checkplus'] = '1';
            if ($ipin === '1')
                $settings['ipin'] = '1';
        }
        return $settings;
    }

    function register_form_rows($rows, $toggle = 'new')
    {
        global $wpmem;

		if ($toggle === 'new') {
            $namecheck = apply_filters('dbmem_settings_namecheck', FALSE, $toggle);
            if ($namecheck) {
                $class = (!isset($_REQUEST['namecheck_component']) || empty($_REQUEST['namecheck_component']) ||
                        !isset($_REQUEST['namecheck_reqseq']) || empty($_REQUEST['namecheck_reqseq']) ||
                        !isset($_REQUEST['namecheck_encode']) || empty($_REQUEST['namecheck_encode']) ) ?
                        '' : 'namecheck-success';
                $context = array('class'=>$class, 'namecheck'=>$namecheck);
                $content = $this->view_contents('register_namecheck', $context);
                $rows = array_merge(array(\DM_WpMembers::make_row($content)), $rows);
            }
        } else {
            $namecheck = apply_filters('dbmem_settings_namecheck', FALSE, $toggle);
            if ($namecheck) {
                $class = (!isset($_REQUEST['namecheck_component']) || empty($_REQUEST['namecheck_component']) ||
                        !isset($_REQUEST['namecheck_reqseq']) || empty($_REQUEST['namecheck_reqseq']) ||
                        !isset($_REQUEST['namecheck_encode']) || empty($_REQUEST['namecheck_encode']) ) ?
                        '' : 'namecheck-success';
                $realname = get_user_meta(get_current_user_id(), 'namecheck_utf8_name', true);
                $context = array(
                    'class' => $class,
                    'namecheck' => $namecheck,
                    'register_heading' => apply_filters( 'wpmem_register_heading', $wpmem->get_text( 'register_heading' ), $toggle ),
                    'display' => get_option('dbmembers_profile_namecheck_display'),
                    'user' => wp_get_current_user(),
                    'namechecked' => !empty($realname)
                );
                $content = $this->view_contents('profile_namecheck', $context);
                $rows = array_merge(array(\DM_WpMembers::make_row($content)), $rows);
            }
        }
        return $rows;
    }

    function edit_user_profile($user)
    {
        $context = array('user'=>$user);
        $this->view('admin/profile', $context);
    }

    function show_user_profile($user)
    {
        $this->edit_user_profile($user);
    }
}

return new Dbmembers_Controller;
