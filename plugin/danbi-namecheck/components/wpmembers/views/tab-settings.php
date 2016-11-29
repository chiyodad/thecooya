<?php
if (isset($_GET['message']) && $_GET['message'] != '0'): ?>
	<div id="message" class="updated fade below-h2"><p><strong>
	<?php
	switch ($_GET['message']) {
		case '1':
			echo __('Options saved.');
			break;
		case '2':
			echo __('Pages of Log in, Register, Welcome, Profile, Withdrawal, Reset Password, Retrieve Username, Terms of Service and Privacy policy created.', 'danbi-members');
			break;
		default:
			break;
	}
	?>
		</strong></p></div>
<?php
endif;
?>

<div id="dbmem-tab-etc" class="metabox-holder">
	<div id="post-body">
		<div id="post-body-content">
			<div class="postbox">
				<h3>본인인증 관리</h3>
				<form name="updatesettings" id="updatesettings" method="post" class="dbmem-updatesettings" data-tab="namecheck">
					<input type="hidden" name="action" value="wpmem_update_settings_namecheck" />
					<?php wp_nonce_field( 'wpmem_update_settings_namecheck' ); ?>
					<div class="inside">
                        <h3><?php _e('Register'); ?> - 본인인증</h3>
                        <ul>
                            <?php
                            \DM_WpMembers_Admin::checkbox('nice_checkplus', '핸드폰 인증', 'NICE 본인인증의 핸드폰 인증을 사용합니다.');
                            \DM_WpMembers_Admin::checkbox('nice_ipin', '아이핀 인증', 'NICE 아이핀 인증을 사용합니다.');
                            \DM_WpMembers_Admin::checkbox('namecheck_save', '사용자 정보 저장', '본인인증 후 사용자의 이름, 생년월일, 성별, 국적을 저장합니다. 저장 시에는 개인정보 보호정책에 관련 내용을 추가하십시오.');
                            ?>
                        </ul>
                        <h3><?php _e('Profile'); ?> - 본인인증</h3>
                        <ul>
                            <?php
                            \DM_WpMembers_Admin::checkbox('profile_nice_checkplus', '핸드폰 인증', 'NICE 본인인증의 핸드폰 인증을 사용합니다.');
                            \DM_WpMembers_Admin::checkbox('profile_nice_ipin', '아이핀 인증', 'NICE 아이핀 인증을 사용합니다.');
                            \DM_WpMembers_Admin::checkbox('profile_namecheck_save', '사용자 정보 저장', '본인인증 후 사용자의 이름, 생년월일, 성별, 국적을 저장합니다. 저장 시에는 개인정보 보호정책에 관련 내용을 추가하십시오.');
                            \DM_WpMembers_Admin::checkbox('profile_namecheck_display', '본인인증 정보 표시', '사용자의 이름, 생년월일, 성별, 국적을 표시합니다.');
                            ?>
                        </ul>
						<br /></br />
						<input type="submit" class="button-primary" value="<?php _e( 'Update Settings', 'wp-members' ); ?> &raquo;" />
                    </div>
				</form>
			</div>
		</div><!-- #post-body-content -->
	</div><!-- #post-body -->
</div><!-- .metabox-holder -->
