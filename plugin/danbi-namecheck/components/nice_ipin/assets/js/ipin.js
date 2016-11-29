jQuery(function($) {
	$.fx.off = true;
    $('body').append('<form id="nice-ipin-form" method="post" action="https://cert.vno.co.kr/ipin.cb" target="popupIPIN2"> <input type="hidden" name="m" value="pubmain"> <input type="hidden" id="ipin-encode-data" name="enc_data" value=""> <input type="hidden" name="param_r1" value=""> <input type="hidden" name="param_r2" value=""> <input type="hidden" name="param_r3" value=""> </form>');
    $('#ipin-encode-data').val(niceIpin.encode);
	$('#nice-ipin-btn').click(function() {
		window.open('', 'popupIPIN2', 'width=450, height=550, top=100, left=100, fullscreen=no, menubar=no, status=no, toolbar=no, titlebar=yes, location=no, scrollbar=no');
		$('#nice-ipin-form').submit();

		return false;
	});
});

function setNiceIpinData(data) {
	var $ = jQuery;
	data.action = 'namecheck_ipin';
	// console.log(data);
	$.post(niceIpin.ajax_url, {
		action: niceIpin.action,
		_wpnonce: niceIpin.nonce,
		reqseq: niceIpin.reqseq,
		encode: data
	}, function(response) {
		if (response.meta.code == '200') {
			completeNamecheck('ipin', niceIpin.reqseq, data);
		} else if (response.meta.code == '302') {
			if (confirm('이미 회원가입하셨습니다. 로그인 페이지로 이동하시겠습니까?')) {
				location.href = response.data.login;
			}
		} else {
			alert(response.meta.message);
		}
	}, 'json');
}
