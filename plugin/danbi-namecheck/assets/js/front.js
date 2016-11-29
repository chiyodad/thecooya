jQuery(function($) {
	$.fx.off = true;
	$('.namecheck-item-checkplus').click(function() {
		window.open('', 'popupChk', 'width=500, height=550, top=100, left=100, fullscreen=no, menubar=no, status=no, toolbar=no, titlebar=yes, location=no, scrollbar=no');
		$('#'+$(this).data('form')).submit();
		//document.checkplus_form.submit();

		return false;
	});
    $('#wpmem_reg form').submit(function(event) {
        if (jQuery('#nice_checkplus_field_activation_key').val() == '') {
            alert('본인인증을 진행해주십시오.');
            event.preventDefault();
            $.namecheckRequired = true;
        } else {
            $.namecheckRequired = false;
        }
        return true;
    });
});

