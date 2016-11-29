jQuery(function($) {
    $('#wpmem_login form').submit(function() {
        var msg = '';
        var pass1 = $('#pass1').val(), pass2 = $('#pass2').val();
        if (pass1 == '' || pass2 == '') {
            msg = '비밀번호를 입력하십시오.';
        } else if (pass1 != pass2) {
            msg = '비밀번호가 일치하지 않습니다.';
        } else {
            $.ajax({
                method: 'POST',
                url: DanbiMembers.ajax_url,
                async: false,
                data: $(this).serialize(),
                success: function(data) {
                    data = $.trim(data);
                    if (data != '')
                        msg = data;
                }
            });
        }
        if (msg != '') {
            alert(msg);
            return false;
        }
        return true;
    });
});
