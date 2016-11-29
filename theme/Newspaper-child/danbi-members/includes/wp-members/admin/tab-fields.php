<div class="metabox-holder">
	
	<div id="post-body">
		<div id="post-body-content">
			<div class="postbox">
                <h3><?php _e('Need help?','danbi-members'); ?></h3>
				<div class="inside">
					<ul style="list-style-type: square; margin-left:20px;">
						<li><?php _e('You can change the order of the fields by drag-and-drop.','danbi-members'); ?></li>
						<li><?php _e('Use a nickname or a display name instead of the first name and last name in Korea.','danbi-members'); ?></li>
                        <li>비밀번호 확인 필드는 옵션 이름이 confirm_password 이고, 필드 타입이 password 입니다.</li>
                        <li><?php _e('Zip (zip), Address 1 (addr1), Address 2 (addr2) are required to use the zip code search. You can change the labels of those fields to Address by clicking the Edit button.','danbi-members'); ?></li>
					</ul>
					<h4><?php _e('New Field','danbi-members'); ?></h4>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Name','danbi-members'); ?>" wpm-option="display_name" wpm-type="text" wpm-display="y" wpm-required="y"><?php _e('Display Name','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Nickname','danbi-members'); ?>" wpm-option="nickname" wpm-type="text" wpm-display="y" wpm-required="y"><?php _e('Nickname','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Password','danbi-members'); ?>" wpm-option="password" wpm-type="password" wpm-display="y" wpm-required="y"><?php _e('Password','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Mobile','danbi-members'); ?>" wpm-option="mobile" wpm-type="text" wpm-display="y" wpm-required="n"><?php _e('Mobile Number','danbi-members'); ?></button>
					<h4><?php _e('Checkout(Billing) Fields of WooCommerce','danbi-members'); ?></h4>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Name','danbi-members'); ?>" wpm-option="billing_first_name" wpm-type="text" wpm-display="y" wpm-required="n"><?php _e('Name','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Postcode','danbi-members'); ?>" wpm-option="billing_postcode" wpm-type="text" wpm-display="y" wpm-required="n"><?php _e('Postcode','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Address','danbi-members'); ?>" wpm-option="billing_address_1" wpm-type="text" wpm-display="y" wpm-required="n"><?php _e('Address','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Address Detail','danbi-members'); ?>" wpm-option="billing_address_2" wpm-type="text" wpm-display="y" wpm-required="n"><?php _e('Address Detail','danbi-members'); ?></button>
					<button class="button-secondary add-field-btn" wpm-name="<?php _e('Phone','danbi-members'); ?>" wpm-option="billing_phone" wpm-type="text" wpm-display="y" wpm-required="n"><?php _e('Phone Number','danbi-members'); ?></button>
				</div>
			</div>
		</div><!-- #post-body-content -->
	</div><!-- #post-body -->

</div><!-- .metabox-holder -->

<script type="text/javascript">
jQuery(function($) {
	$('.add-field-btn').click(function() {
		$('input[name="add_name"]').val($(this).attr('wpm-name'));
		$('input[name="add_option"]').val($(this).attr('wpm-option'));
		$('select[name="add_type"] option[value="' + $(this).attr('wpm-type') + '"]').attr('selected','selected');
		$('input[name="add_display"]').attr('checked', $(this).attr('wpm-display') == 'y');
		$('input[name="add_required"]').attr('checked', $(this).attr('wpm-required') == 'y');
		$('textarea[name="add_dropdown_value"]').val($(this).attr('wpm-options'));
		$('#addfieldform').submit();
	});
});
</script>