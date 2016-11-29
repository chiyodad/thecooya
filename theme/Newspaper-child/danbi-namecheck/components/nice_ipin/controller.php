<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nice_Ipin_Controller extends Abstract_Controller
{
    protected $id = 'nice_ipin';

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
        $option = get_option('namecheck_nice-ipin', array());
        if (!isset($option['sitecode']) || empty($option['sitecode']) ||
                !isset($option['sitepass']) || empty($option['sitepass'])) {
            $content = '<span class="error-msg">본인인증 설정 오류</span>';
        } else {
            $sitecode = $option['sitecode'];
            $sitepass = $option['sitepass'];

            $reqseq = $this->exec("SEQ $sitecode");

            $returnurl = $this->get_url('success.php');	// 성공시 이동될 URL
            $enc_data = $this->exec("REQ $sitecode $sitepass $reqseq $returnurl");

            $this->script('ipin');
            $this->localize('ipin', 'niceIpin', array(
                'reqseq' => $reqseq,
                'encode' => $enc_data,
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => $this->create_nonce('check'),
                'action' => $this->get_name('check')
            ));
            $content = '<a id="nice-ipin-btn" class="d_btn" href="#" style="height: 42px;">' . $content . '</a>';
        }
        $content = '<li class="namecheck-btn"><h4><span class="dashicons dashicons-lock"></span></h4>' . $content . '</li>';

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
                    'Windows/IPINClient.exe' :
                    PHP_OS === 'Unix' ?
    					'Unix/IPINClient' :
                        PHP_OS === 'FreeBSD' ?
        					'FreeBSD/' . (PHP_INT_SIZE * 8) . '/IPINClient' :
        					'Linux/' . (PHP_INT_SIZE * 8) . '/IPINClient')
            );

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
        if(empty($enc_data) || preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match))
            throw new \Exception('입력 값 확인이 필요합니다');

        $option = get_option('namecheck_nice-ipin', array());
        if (!isset($option['sitecode']) || empty($option['sitecode']) ||
                !isset($option['sitepass']) || empty($option['sitepass']))
            throw new \Exception('본인인증 설정이 되어 있지 않습니다.');

        $sitecode = $option['sitecode'];
        $sitepass = $option['sitepass'];

        $plaindata = $this->exec("RES $sitecode $sitepass $enc_data");
        if ($plaindata == -9)
            throw new \Exception('입력값 오류 : 복호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.');
        else if ($plaindata == -12)
            throw new \Exception('NICE신용평가정보에서 발급한 개발정보가 정확한지 확인해 보세요.');

        $dec_data = $this->parse($plaindata);

        if ($reqseq !== $dec_data['REQ_SEQ'])
            throw new \Exception('요청번호가 일치하지 않습니다.');

        return $dec_data;
    }

    function parse($plain) {
        $parsed = explode('^', $plain);
        if (count($parsed) < 5)
            throw new \Exception('리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요.');

        if ($parsed[0] != 1)
            throw new \Exception('리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요. [' . $parsed[0] . ']');

        $data = array(
            'VNO' => $parsed[1],
            'NAME' => $parsed[2],
            'DI' => $parsed[3],  // 중복가입 확인값 (64 bytes)
            'AGEINFO' => $parsed[4],
            'GENDER' => $parsed[5],
            'BIRTHDATE' => $parsed[6],
            'NATIONALINFO' => $parsed[7],
            'REQ_SEQ' => $parsed[8],
            'UTF8_NAME' => iconv("EUC-KR", "UTF-8", $parsed[2])
        );

        return $data;
    }


}

return new Nice_Ipin_Controller;
