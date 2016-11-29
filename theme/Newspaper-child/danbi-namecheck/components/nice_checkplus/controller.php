<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nice_Checkplus_Controller extends Abstract_Controller
{
    protected $id = 'nice_checkplus';

    private $client = null;

    function __construct()
    {
        parent::__construct(__FILE__);
        $this->add_filter('button');
        $this->add_filter('decode', 10, 2);
        $this->add_ajax('check', TRUE);
    }

	function button($content = '')
    {
        $option = get_option('namecheck_nice-checkplus', array());
        if (!isset($option['sitecode']) || empty($option['sitecode']) ||
                !isset($option['sitepass']) || empty($option['sitepass'])) {
            $content = '<span class="error-msg">본인인증 설정 오류</span>';
        } else {
            $sitecode = $option['sitecode'];
            $sitepass = $option['sitepass'];
            $popgubun 	= 'Y';		//Y : 취소버튼 있음 / N : 취소버튼 없음
            $customize 	= '';			// 없으면 기본 웹페이지 / Mobile : 모바일페이지
            $authtype = '';  // 인증수단 - M : 휴대폰 / C : 신용카드 / X : 공인인증서

            $reqseq = $this->exec("SEQ $sitecode");
            if (empty($reqseq)) {
                $content = '<span class="error-msg">본인인증모듈 실행권한 오류</span>';
            } else {
                // CheckPlus(본인인증) 처리 후, 결과 데이타를 리턴 받기위해 다음예제와 같이 http부터 입력합니다.
                $returnurl = $this->get_url('success.php');	// 성공시 이동될 URL
                $errorurl = $this->get_url('error.php');	// 실패시 이동될 URL
                // reqseq값은 성공페이지로 갈 경우 검증을 위하여 세션에 담아둔다.

                // 입력될 plain 데이타를 만든다.
                $plaindata =  "7:REQ_SEQ" . strlen($reqseq) . ":" . $reqseq .
                "8:SITECODE" . strlen($sitecode) . ":" . $sitecode .
                "9:AUTH_TYPE" . strlen($authtype) . ":". $authtype .
                "7:RTN_URL" . strlen($returnurl) . ":" . $returnurl .
                "7:ERR_URL" . strlen($errorurl) . ":" . $errorurl .
                "11:POPUP_GUBUN" . strlen($popgubun) . ":" . $popgubun .
                "9:CUSTOMIZE" . strlen($customize) . ":" . $customize ;

                // echo 'plaindata: '; print_r($plaindata);
                $enc_data = $this->exec("ENC $sitecode $sitepass $plaindata");

                $this->script('checkplus');
                $this->localize('checkplus', 'niceCheckplus', array(
                    'reqseq' => $reqseq,
                    'encode' => $enc_data,
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => $this->create_nonce('check'),
                    'action' => $this->get_name('check')
                ));
                $content = '<a id="nice-checkplus-btn" class="d_btn" href="#" style="height: 42px;">' . $content . '</a>';
            }

        }
        $content = '<li class="namecheck-btn"><h4><span class="dashicons dashicons-smartphone"></span></h4>' . $content . '</li>';

        return $content;
	}

    function exec($cmd)
    {
		return exec($this->get_client() . ' ' . $cmd);
	}

    function get_client()
    {
		if ($this->client == null)
			$this->client =  $this->get_path('lib/' .
				(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ?
                    'Window/CPClient.exe' :
					'Linux/' . (PHP_INT_SIZE * 8) . '/CPClient')
            );
        // print_r($this->client);
		return $this->client;
	}

    function check()
    {
        $encode = $_REQUEST['encode'];
        $reqseq = $_REQUEST['reqseq'];
        try {
            $data = $this->decode($encode, $reqseq);
        } catch(\Exception $e) {
            $this->ajax_failure($e->getMessage());
        }

        if (isset($data['DI'])) {
            $users = get_users(array('fields'=>'ID', 'meta_key'=>'namecheck_di', 'meta_value'=>$data['DI']));
            if (empty($users))
                $this->ajax_success();
            else {
                define('DBMEMBERS_LOGIN',TRUE);
                $this->ajax_result(302, '이미 회원가입 하셨습니다.', array('login'=>wp_login_url( home_url() )));
            }
        } else {
            $this->ajax_success();
        }
    }

    function decode($enc_data, $reqseq)
    {
        if(empty($enc_data) || preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match) || base64_encode(base64_decode($enc_data)) != $enc_data)
            throw new \Exception('입력 값 확인이 필요합니다');

        $option = get_option('namecheck_nice-checkplus', array());
        if (!isset($option['sitecode']) || empty($option['sitecode']) ||
                !isset($option['sitepass']) || empty($option['sitepass']))
            throw new \Exception('본인인증 설정이 되어 있지 않습니다.');

        $sitecode = $option['sitecode'];
        $sitepass = $option['sitepass'];

        $plaindata = $this->exec("DEC $sitecode $sitepass $enc_data");
        if ($plaindata == -1)
            throw new \Exception('암/복호화 오류입니다.');
        if ($plaindata == -4)
            throw new \Exception('복호화 처리 오류입니다.');
        if ($plaindata == -5)
            throw new \Exception('해쉬값 불일치 오류입니다.');
        if ($plaindata == -6)
            throw new \Exception('복호화 처리 오류입니다.');
        if ($plaindata == -9)
            throw new \Exception('입력값 오류입니다.');
        if ($plaindata == -12)
            throw new \Exception('사이트 비밀번호 오류입니다.');

        $dec_data = $this->parse($plaindata);

        if ($reqseq !== $dec_data['REQ_SEQ'])
            throw new \Exception('요청번호가 일치하지 않습니다.');

        return $dec_data;
    }

    function parse($plain) {
        $parsed = explode(':', $plain);
        $data = array();
        $key = null;
        for ($i = 1; $i < sizeof($parsed); $i++) {
            $token = substr($parsed[$i], 0, (int)$parsed[$i-1]);
            $parsed[$i] = substr($parsed[$i], (int)$parsed[$i-1]);
            if ($i % 2 == 1)
                $key = $token;
            else
                $data[$key] = $token;
        }
        $data['UTF8_NAME'] = urldecode($data['UTF8_NAME']);
        return $data;
    }
}

return new Nice_Checkplus_Controller;
