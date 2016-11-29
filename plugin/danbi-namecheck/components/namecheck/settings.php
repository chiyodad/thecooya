<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Namecheck_Settings extends Abstract_Settings
{
    protected $page = 'namecheck';

    function __construct($id)
    {
        parent::__construct($id);
    }

    function menu()
    {
        $this->add_options_page(array(
            'menu_title'=>'본인인증',
            'page_title'=>'본인인증',
            'capability'=>'manage_options',
            'desc'=>''
        ));
    }

    function init()
    {
        $this->add_section(array(
            'id' => 'nice-checkplus',
            'title' => 'NICE 본인인증',
            'desc' => 'NICE 본인인증 가입 정보를 설정합니다.',
            'fields' => array(
                array(
                    'id' => 'sitecode',
                    'type' => 'text',
                    'title' => '사이트 코드',
                    'subtitle' => '',
                    'desc' => 'NICE 본인인증 사이트 코드를 입력합니다.',
                    'default' => ''
                ),
                array(
                    'id' => 'sitepass',
                    'type' => 'text',
                    'title' => '사이트 비밀번호',
                    'subtitle' => '',
                    'desc' => 'NICE 본인인증 사이트 비밀번호를 입력합니다.',
                    'default' => ''
                )
            )
        ));
        $this->add_section(array(
            'id' => 'nice-ipin',
            'title' => 'NICE 아이핀',
            'desc' => 'NICE 아이핀 가입 정보를 설정합니다.',
            'fields' => array(
                array(
                    'id' => 'sitecode',
                    'type' => 'text',
                    'title' => '사이트 코드',
                    'subtitle' => '',
                    'desc' => 'NICE 아이핀 사이트 코드를 입력합니다.',
                    'default' => ''
                ),
                array(
                    'id' => 'sitepass',
                    'type' => 'text',
                    'title' => '사이트 비밀번호',
                    'subtitle' => '',
                    'desc' => 'NICE 아이핀 사이트 비밀번호를 입력합니다.',
                    'default' => ''
                )
            )
        ));
    }
}
