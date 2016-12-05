<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once 'abstract.php';

if ( ! class_exists( 'Nice_Checkplus_Front' ) ) :

class Nice_Checkplus_Front extends Abstract_Nice_Checkplus {

	function __construct() {
        parent::__construct();
        add_action('namecheck', array($this, 'namecheck'));
        add_filter('wpmem_register_form_rows', array($this, 'wpmem_register_form_rows'), 5, 2);
        add_filter('wpmem_register_hidden_fields', array($this, 'wpmem_register_hidden_fields'), 10, 2);
    }

	function enqueue_scripts() {
	}

	function namecheck() {
		if ( $this->plugin->has_option('nice_ckeckplus_code') && $this->plugin->has_option('nice_ckeckplus_password')) {
			$this->print_checkplus_form('nice_ckeckplus_use','mobile', 'M', '휴대폰 인증');
			$this->print_checkplus_form('nice_ckeckplus_use','card', 'C', '신용카드 인증');
			$this->print_checkplus_form('nice_ckeckplus_use','cert', 'X', '공인인증서 인증');
		}

		return $rows;
	}

	/*
	 *
	 * @param $authtype 없으면 기본 선택화면, X: 공인인증서, M: 핸드폰, C: 카드
	 */
	function print_checkplus_form($name, $subname, $authtype = '', $title = '') {
		if ($this->plugin->get_option($name, $subname) == 'true') {
		    $sitecode = $this->plugin->get_option('nice_ckeckplus_code');				// NICE로부터 부여받은 사이트 코드
		    $sitepasswd = $this->plugin->get_option('nice_ckeckplus_password');			// NICE로부터 부여받은 사이트 패스워드
		    // echo $cb_encode_path;
			$popgubun 	= 'Y';		//Y : 취소버튼 있음 / N : 취소버튼 없음
			$customize 	= '';			//없으면 기본 웹페이지 / Mobile : 모바일페이지

		    $reqseq = $this->exec("SEQ $sitecode");

		    // CheckPlus(본인인증) 처리 후, 결과 데이타를 리턴 받기위해 다음예제와 같이 http부터 입력합니다.
		    $returnurl = add_query_arg(array('action'=>'namecheck_nice_checkplus_success'), admin_url('admin-ajax.php') );	// 성공시 이동될 URL
		    $errorurl = add_query_arg(array('action'=>'namecheck_nice_checkplus_fail'), admin_url('admin-ajax.php') );	// 실패시 이동될 URL
		    // reqseq값은 성공페이지로 갈 경우 검증을 위하여 세션에 담아둔다.

		    // 입력될 plain 데이타를 만든다.
		    $plaindata =  "7:REQ_SEQ" . strlen($reqseq) . ":" . $reqseq .
					    			  "8:SITECODE" . strlen($sitecode) . ":" . $sitecode .
					    			  "9:AUTH_TYPE" . strlen($authtype) . ":". $authtype .
					    			  "7:RTN_URL" . strlen($returnurl) . ":" . $returnurl .
					    			  "7:ERR_URL" . strlen($errorurl) . ":" . $errorurl .
					    			  "11:POPUP_GUBUN" . strlen($popgubun) . ":" . $popgubun .
					    			  "9:CUSTOMIZE" . strlen($customize) . ":" . $customize ;

		    $enc_data = $this->exec("ENC $sitecode $sitepasswd $plaindata");

		    if( $enc_data == -1 )
		    {
		        $returnMsg = "암/복호화 시스템 오류입니다.";
		        $enc_data = "";
		    }
		    else if( $enc_data== -2 )
		    {
		        $returnMsg = "암호화 처리 오류입니다.";
		        $enc_data = "";
		    }
		    else if( $enc_data== -3 )
		    {
		        $returnMsg = "암호화 데이터 오류 입니다.";
		        $enc_data = "";
		    }
		    else if( $enc_data== -9 )
		    {
		        $returnMsg = "입력값 오류 입니다.";
		        $enc_data = "";
		    }
			echo <<<EOT
				<li>
					<a class="namecheck-item-checkplus" href="#" form="{$name}_{$subname}_form">$title</a>
					<form id="{$name}_{$subname}_form" method="post" action="https://nice.checkplus.co.kr/CheckPlusSafeModel/checkplus.cb" target="popupChk">
						<input type="hidden" name="m" value="checkplusSerivce">						<!-- 필수 데이타로, 누락하시면 안됩니다. -->
						<input type="hidden" name="EncodeData" value="$enc_data">		<!-- 위에서 업체정보를 암호화 한 데이타입니다. -->

					    <!-- 업체에서 응답받기 원하는 데이타를 설정하기 위해 사용할 수 있으며, 인증결과 응답시 해당 값을 그대로 송신합니다.
					    	 해당 파라미터는 추가하실 수 없습니다. -->
						<input type="hidden" name="param_r1" value="">
						<input type="hidden" name="param_r2" value="">
						<input type="hidden" name="param_r3" value="">
					</form>
				</li>
EOT;
		}
	}

	function wpmem_register_form_rows($rows, $toggle) {
		if ($toggle == 'new') {
			$new_rows = array();

			if ($this->plugin->get_option('nice_ckeckplus_fields','realname')) {
				$new_rows[] = $this->display_register_input('name','실명');
			}
			foreach( $rows as $row ) {
				$new_rows[] = $row;
			}

			if ($this->plugin->get_option('nice_ckeckplus_fields','birthdate')) {
				$adult_date = date("Ymd", strtotime("-19 years"));
				$birth_date = $this->plugin->get_option('nice_ckeckplus_fields','birthdate');
				$yn_adult = '미성년';
				if((int)$birth_date <= (int)$adult_date) {
				    $yn_adult = "성인";
				}
				$new_rows[] = $this->display_register_input($yn_adult,'성인여부');
			}

			$rows = $new_rows;
		}

		return $rows;
	}

	function display_register_input($field, $label) {
		return array('field' => <<<EOD
	<label class="text">$label</label>
	<div class="div_text" style="width:100%;">
		<input type="text" id="nice_checkplus_field_$field" readonly />
	</div>
EOD
		);
	}

    function wpmem_register_hidden_fields($hidden, $toggle) {
        if ($toggle == 'new')
            $hidden .= '<input type="hidden" name="activation_key" id="nice_checkplus_field_activation_key" value="" />';
        return $hidden;
    }
}

endif; // class_exists check

return new Nice_Checkplus_Front;
