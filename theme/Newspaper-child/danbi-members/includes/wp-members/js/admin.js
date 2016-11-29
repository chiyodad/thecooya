jQuery(function($) {
	$('.dbmem-updatesettings').submit(function() {
		var tab = $(this).data('tab');
		$.post(Ajax.url, $(this).serialize(), function(response) {
			location.href = 'options-general.php?page=wpmem-settings&tab=' + tab + '&message=' + response;
			// alert(response);
		});
		return false;
	});
	$('#insert-pages').submit(function() {
		$.post(Ajax.url, $(this).serialize(), function(response) {
			location.href = 'options-general.php?page=wpmem-settings&tab=etc&message=' + response;
		});
		return false;
	});
	$('#recaptcha-enabled').click(function() {
		if ($(this).is(':checked'))
			$('.recaptcha-settigns').slideDown();
		else
			$('.recaptcha-settigns').slideUp();
	});
	$('input[name="dbmembers_seperate_agreement"]').click(function() {
		if ($(this).is(':checked'))
			$('.setting-agreement-page').slideDown();
		else
			$('.setting-agreement-page').slideUp();

	});
	['login','agreement','terms','privacy','welcome','withdrawal'].forEach(function(key) {
		$('#dbmembers_'+key+'_select').change(function() {
			if ($('#dbmembers_'+key+'_select').val() == 'use_custom')
				$('#dbmembers_'+key+'_custom').show();
			else
				$('#dbmembers_'+key+'_custom').hide();
		});
	});

});