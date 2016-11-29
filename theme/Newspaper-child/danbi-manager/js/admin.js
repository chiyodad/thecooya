jQuery(function($) {
	var action = '';
	$('.edd_license_action').click(function() {
		$(this).next().css('visibility','visible');
		action = $(this).data('action');
		$.post(ajaxurl, {
			action: 'danbi_manager_' + action + '_license',
			_wpnonce: $('input[name="_wpnonce"]').val(),
			_wp_http_referer: $('input[name="_wp_http_referer"]').val(),
			key: $(this).prev().val(),
			item_id: $(this).data('id'),
			item_name : $(this).data('name')
		}, function(response) {
			if (response == 'valid') {
				document.location.href = '?page=danbi_manager&tab=' + $.url().param('tab') + '&' + action + '=1';
			} else if (response == 'invalid') {
				document.location.href = '?page=danbi_manager&tab=' + $.url().param('tab');
			} else {
				alert(response);
				// location.reload();
			}
		});
		return false;
	});
});