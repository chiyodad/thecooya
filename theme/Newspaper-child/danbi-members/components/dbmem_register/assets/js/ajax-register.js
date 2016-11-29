jQuery(function($) {
    $('#wpmem_reg .register-form').ajaxForm({
        url: DanbiMembers.ajax_url,
        dataType: 'json',
        beforeSerialize: function($form, options) {
            $form.find('input[name="a"]').remove();
        },
        success: function(response) {
            if (response.meta.code === 200) {
                if (typeof response.meta.message !== 'undefined')
                    alert(response.meta.message);
                location.href = response.data;
            } else {
                alert(response.meta.message.replace(/<(?:.|\n)*?>/gm, ''));
            }
            return false;
        }
    });
});
