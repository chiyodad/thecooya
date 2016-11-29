jQuery(function($) {
	$.fx.off = true;
    $('body').append('<form id="nice-checkplus-form" method="post" action="https://nice.checkplus.co.kr/CheckPlusSafeModel/checkplus.cb" target="popupChk"> <input type="hidden" name="m" value="checkplusSerivce"> <input type="hidden" id="checkplus-encode-data" name="EncodeData" value=""> <input type="hidden" name="param_r1" value=""> <input type="hidden" name="param_r2" value=""> <input type="hidden" name="param_r3" value=""> </form>');
    $('#checkplus-encode-data').val(niceCheckplus.encode);
	$('#nice-checkplus-btn').click(function() {
		window.open('', 'popupChk', 'width=500, height=550, top=100, left=100, fullscreen=no, menubar=no, status=no, toolbar=no, titlebar=yes, location=no, scrollbar=no');
		$('#nice-checkplus-form').submit();
		// document.checkplus_form.submit();

		return false;
	});
});

function setNiceCheckplusData(data) {
	var $ = jQuery;
	data.action = 'namecheck_checkplus';
	// console.log(data);
	$.post(niceCheckplus.ajax_url, {
		action: niceCheckplus.action,
		_wpnonce: niceCheckplus.nonce,
		reqseq: niceCheckplus.reqseq,
		encode: data
	}, function(response) {
		if (response.meta.code == '200') {
			completeNamecheck('checkplus', niceCheckplus.reqseq, data);
		} else if (response.meta.code == '302') {
			if (confirm('이미 회원가입하셨습니다. 로그인 페이지로 이동하시겠습니까?')) {
				location.href = response.data.login;
			}
		} else {
			alert(response.meta.message);
		}
		if (response.meta.code == '200') {
			//location.reload();
			//jQuery('.namecheck-area').html(response.meta.message);
		} else {
			alert(response.meta.message);
		}
	}, 'json');
}
