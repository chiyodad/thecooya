<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once 'abstract.php';

if ( ! class_exists( 'Nice_Checkplus_Admin' ) ) :

class Nice_Checkplus_Admin extends Abstract_Nice_Checkplus {

	function __construct() {
        parent::__construct();
        add_action('namecheck_settings', array($this, 'namecheck_settings'), 10, 1);
		add_action('wp_ajax_namecheck_nice_checkplus_success', array($this, 'success') );
		add_action('wp_ajax_namecheck_nice_checkplus_fail', array($this, 'fail') );
		add_action('wp_ajax_nopriv_namecheck_nice_checkplus_success', array($this, 'success') );
		add_action('wp_ajax_nopriv_namecheck_nice_checkplus_fail', array($this, 'fail') );
	}

	function namecheck_settings($component) {
		echo '<h4>나이스 네임체크 본인인증</h4> <ul>';
		$component->input_text($this->MODULE . '_code', '사이트 코드', '나이스 네임체크 본인인증 사이트 코드입니다.');
		$component->input_text($this->MODULE . '_password', '사이트 비밀번호', '나이스 네임체크 본인인증 비밀번호입니다.');
		$component->input_checkbox($this->MODULE . '_use', '인증 방식', '', array('mobile'=>'핸드폰', 'card'=>'신용카드', 'cert'=>'공인인증서'));
		$component->input_checkbox($this->MODULE . '_fields', '인증 데이터 저장', '', $this->plugin->fields_meta);
		echo '</ul>';
	}

    function get_result() {
        $sitecode = $this->plugin->get_option('nice_ckeckplus_code');				// NICE로부터 부여받은 사이트 코드
        $sitepasswd = $this->plugin->get_option('nice_ckeckplus_password');			// NICE로부터 부여받은 사이트 패스워드
        $enc_data = $_POST["EncodeData"];		// 암호화된 결과 데이타
        $sReserved1 = $_POST['param_r1'];
        $sReserved2 = $_POST['param_r2'];
        $sReserved3 = $_POST['param_r3'];

        //////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
        if(preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match))
            return array('code'=>'-1', 'message'=>'입력 값 확인 필요');
        if(base64_encode(base64_decode($enc_data))!= $enc_data)
            return array('code'=>'-1', 'message'=>'입력 값 확인 필요');
        if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved1, $match))
            return array('code'=>'-1', 'message'=>'문자열 점검:' . $match[0]);
        if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved2, $match))
            return array('code'=>'-1', 'message'=>'문자열 점검:' . $match[0]);
        if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved3, $match))
            return array('code'=>'-1', 'message'=>'문자열 점검:' . $match[0]);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////

        if ($enc_data != "") {
            $plaindata = $this->exec("DEC $sitecode $sitepasswd $enc_data");		// 암호화된 결과 데이터의 복호화
            if ($plaindata == -1){
                $returnMsg  = "암/복호화 시스템 오류";
            }else if ($plaindata == -4){
                $returnMsg  = "복호화 처리 오류";
            }else if ($plaindata == -5){
                $returnMsg  = "HASH값 불일치 - 복호화 데이터는 리턴됨";
            }else if ($plaindata == -6){
                $returnMsg  = "복호화 데이터 오류";
            }else if ($plaindata == -9){
                $returnMsg  = "입력값 오류";
            }else if ($plaindata == -12){
                $returnMsg  = "사이트 비밀번호 오류";
            }else{
                // 복호화가 정상적일 경우 데이터를 파싱합니다.
                $ciphertime = $this->exec("CTS $sitecode $sitepasswd $enc_data");	// 암호화된 결과 데이터 검증 (복호화한 시간획득)
                $data = $this->parse($plaindata);
                $data['code'] = '200';
                return $data;
            }
            return array('code'=>'-1', 'message'=>$returnMsg);
        }
        return array('code'=>'-1', 'message'=>'결과 데이터 없음');
    }

	function success() {
		global $wpdb;

        $data = $this->get_result();

        if ($data['code'] == '-1') {
            include 'fail.php';
        } else {
            $usermeta = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE meta_key = '_namecheck_ci' and meta_value = %s LIMIT 1", $data['CI']) );
            if ($usermeta != null) {  // 회원 존재
                include 'user_exist.php';
            } else {
                $wpdb->insert( $wpdb->signups, array(
                    'domain' => '',
                    'path' => '',
                    'title' => 'nice_checkplus',
                    'user_login' => '',
                    'user_email' => '',
                    'registered' => current_time('mysql', true),
                    'activation_key' => $data['RES_SEQ'],
                    'meta' => serialize(array('ci'=>$data['CI']))
                ) );
                include 'success.php';
            }
        }
        die();
    }

    function get_value($str , $name)
    {
        $pos1 = 0;  //length의 시작 위치
        $pos2 = 0;  //:의 위치

        while( $pos1 <= strlen($str) ) {
            $pos2 = strpos( $str , ":" , $pos1);
            $len = substr($str , $pos1 , $pos2 - $pos1);
            $key = substr($str , $pos2 + 1 , $len);
            $pos1 = $pos2 + $len + 1;
            if( $key == $name ) {
                $pos2 = strpos( $str , ":" , $pos1);
                $len = substr($str , $pos1 , $pos2 - $pos1);
                $value = substr($str , $pos2 + 1 , $len);
                return $value;
            } else {
                // 다르면 스킵한다.
                $pos2 = strpos( $str , ":" , $pos1);
                $len = substr($str , $pos1 , $pos2 - $pos1);
                $pos1 = $pos2 + $len + 1;
            }            
        }
    }

    function parse($plain) {
    	$parsed = split(':', $plain);
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
    	return $data;
    }

	function fail() {
        $data = $this->get_result();
        include 'fail.php';
	}
}

endif; // class_exists check

return new Nice_Checkplus_Admin;