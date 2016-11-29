jQuery(function($) {
	$('.update-settings').submit(function() {
		$.post(ajaxurl, $(this).serialize(), function(response) {
			location.href = 'options-general.php?page=' + Namecheck.page + '&tab=' + Namecheck.tab + '&message=' + response;
		});
		return false;
	});
});