jQuery(function($) {
    $('#wpmem_reg form.agreement-form').attr('action', $('input[name="redirect_to"]', '#wpmem_reg form.agreement-form').val());
    $('input[name="a"]', '#wpmem_reg form.agreement-form').remove();

    $('input.uncheck-agreement', '#wpmem_reg form').each(function() {
        alert('약관에 동의해주십시오.');
        location.href = DanbiMembers.agreement_page;
        return false;
    });

    $('.check-unique-btn').each(function() {
        var self = this;
        var label = $(self).data('label');
        var val = $(self).val();
        var $input = $(self).prev();
        $input.keyup(function(event) {
            $(self).data('checked',false);
            $(self).val(val).prop('disabled',false).data('checked', false);
        });
        $(self)
            .data('checked', false)
            .data('val', $(self).val())
            .click(function() {
                var self = this;
                if ($.trim($input.val()) === '') {
                    alert(label + ' 입력하십시오.');
                    return false;
                }
                $.post(DanbiMembers.ajax_url, {
                    _wpnonce: $(self).data('nonce'),
                    action: 'dbmem_register_check_unique_field',
                    field: $(self).data('field'),
                    field_value: $input.val()
                }, function(response) {
                    if (response.meta.code === 200) {
                        $(self).val('사용 가능').prop('disabled','disabled').data('checked', true);
                    } else {
                        alert(response.meta.message);
                    }
                }, 'json');
            });
    });
    $('#wpmem_reg form').each(function() {
        // $('.agree-div').show();
        /*
        $.each(['zip','addr1','billing_postcode','billing_address_1'], function(index, element) {
            $('input[name="'+element+'"]').prop('readonly', true);
        });
        $('<input type="button" value="' + DanbiMembers.LABEL_SEARCH_POSTCODE + '" class="d_btn" onclick="openDaumPostcode(\'zip\',\'addr1\',\'addr2\');">').insertAfter('input[name="zip"]');
        $('<input type="button" value="' + DanbiMembers.LABEL_SEARCH_POSTCODE + '" class="d_btn" onclick="openDaumPostcode(\'billing_postcode\',\'billing_address_1\',\'billing_address_2\');">').insertAfter('input[name="billing_postcode"]');
        */
	/*
        $('input.d_btn').each(function() {
            var $prev = $(this).prev();
            var height = parseInt($prev.css('height')) + parseInt($prev.css('padding-top')) + parseInt($prev.css('padding-bottom')) +
                parseInt($prev.css('border-top-width')) + parseInt($prev.css('border-bottom-width'));
            // $(this).css({'height': height + 'px', 'margin-left':'5px'});
        });
	*/
    }).submit(function() {
        if ($.namecheckRequired !== undefined && $.namecheckRequired)
            return false;
//        $('#g-recaptcha-response').each(function() {
//            $('#recaptcha_response_field').val($(this).val());
//        });

        var result = true;
        $('.agreement:checkbox:not(:checked):first').each(function() {
            alert($(this).data('agree-alert'));
            $(this).focus();
            result = false;
        });
        if (!result)
            return false;

        $('.check-unique-btn').each(function() {
            if (!$(this).data('checked')) {
                alert($(this).data('label') + ' 중복 확인을 하십시오');
                result = false;
                return false;
            }
        });
        if (!result)
            return false;

        if ($('#confirm_password','#wpmem_reg').length > 0 &&
                $('#confirm_password','#wpmem_reg').val() != $('#password','#wpmem_reg').val()) {
            alert(DanbiMembers.MESSAGE_PASSWORD_MISMATCH);
            $('#confirm_password','#wpmem_reg').focus();
            return false;
        }

        return true;
    });
    // $('#send-mobile-confirm').click(function() {
    //     $(this).text('인증번호 재전송');
    //     $('#mobile-confirm-msg').show();
    //     $(this).prev().focus();
    //     return false;
    // });

});
