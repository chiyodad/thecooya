<?php
global $wpmem;

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

$unique_fields = get_option('dbmembers_check_unique', array());
?>

<div id="dbmem-tab-etc" class="metabox-holder">

	<div id="post-body">
		<div id="post-body-content">
			<?php if (!get_option('dbmembers_dismiss_creating_pages')): ?>
				<div class="postbox">
					<div class="inside" style="padding-bottom:0px;">
						<form id="insert-pages" method="post" class="updatesettings-etc">
							<span style="font-size:15px;"><?php _e('You can create pages of Log in, Register, Welcome, Profile, Withdrawal, Reset Password, Retrieve Username, Terms of Service and Privacy Policy?', 'danbi-members'); ?></span>
							<input type="hidden" id="dbmem-action" name="action" value="dbmembers_create_pages" />
							<?php wp_nonce_field( 'dbmembers_update_settings' ); ?>
							<input type="submit" class="button-default" value="<?php _e('Add Pages','danbi-members'); ?>" />
							<input type="button" id="dbmem-dismiss-btn" class="button-default" value="<?php _e('Dismiss'); ?>" />
						</form>
						<script type="text/javascript">
						jQuery(function($) {
							$('#dbmem-dismiss-btn').click(function() {
								$('#dbmem-action').val('dbmembers_dismiss_creating_pages');
								$('#insert-pages').submit();
								return false;
							});
						});
						</script>
					</div><!-- .inside -->
				</div>
			<?php endif; ?>
			<div class="postbox">
				<h3>추가 관리</h3>
				<form name="updatesettings" id="updatesettings" method="post" class="dbmem-updatesettings" data-tab="etc">
					<input type="hidden" name="action" value="dbmembers_update_settings_etc" />
					<?php wp_nonce_field( 'dbmembers_update_settings_etc' ); ?>
					<div class="inside">
						<h3><?php _e('Log in'); ?></h3>
						<ul>
							<?php
							DM_WpMembers_Admin::checkbox('login_redirect', __('Redirect to Home','danbi-members'), __('Redirects to Home after login','danbi-members'));
							?>
                        </ul>
						<?php do_action('dbmem_settings_etc_before_register'); ?>

						<h3><?php _e('Register'); ?> - 약관동의</h3>
						<ul>
							<?php
                            DM_WpMembers_Admin::checkbox('seperate_agreement', __('약관동의 분리', 'danbi-members'), __('회원가입 시 약관동의와 회원정보 입력을 다른 페이지로 분리합니다.', 'danbi-members'));
							//DM_WpMembers_Admin::checkbox('confirm_mobile', '휴대전화번호 인증', '회원가입 시 휴대전화번호 인증을 위해 SMS로 인증번호를 발송하고 확인합니다. 이 기능을 사용하기 위해서는 <a href="http://danbistore.com/item/danbi-sms/" target="_blank">단비SMS</a> 플러그인이 필요합니다.');
							echo '<li class="setting-agreement-page"';
							if (get_option('dbmembers_seperate_agreement') !== '1')
								echo ' style="display:none;"';
							echo '>';
							DM_WpMembers_Admin::select_page('agreement','- 약관동의','페이지 본문에 <code>[danbi-members page="agreement"]</code> 추가', false);
							// DM_WpMembers_Admin::select_page('userinfo','- 회원정보','페이지 본문에 <code>[wpmem_form register /]</code> 추가',false);
							echo '</li>';

							DM_WpMembers_Admin::select_page('terms', __('Terms of Service', 'wp-members'));
                            DM_WpMembers_Admin::select_page('privacy', __('Privacy Policy', 'danbi-members'));
                            DM_WpMembers_Admin::select_page('3rd_party', '개인정보 제3자 제공');
							?>
                        </ul>
						<h3><?php _e('Register'); ?> - <?php _e('Fields','wp-members'); ?></h3>
						<ul>
							<?php
                            DM_WpMembers_Admin::checkbox('username_email', __('Username') . ' 대신 이메일 사용', __('Username') . ' 대신 이메일을 사용하고, 이메일은 별도로 입력받지 않습니다.');
                            DM_WpMembers_Admin::checkbox('unique_username', __('Username') . ' ' . __('중복 체크', 'danbi-members'), __('Username') . ' ' . __('중복 체크를 합니다.', 'danbi-members'));
							?>
							<li>
								<div style="float:left;"><label>필드 중복 체크</label></div>
								<div class="check-unique" style="margin-left:166px;">
									<span class="description">아래 필수입력 필드 중 추가로 중복 체크할 필드를 선택하십시오.</span><br>
									<p style="margin-bottom:0;">
										<?php
										foreach ($wpmem->fields as $field) {
											if ($field[2] === 'user_email' || ($field[3] === 'text' && $field[4] === 'y' && $field[5] === 'y')) {
												printf('<label><input type="checkbox" name="dbmembers_check_unique[]" value="%s" %s> %s</label>',
													$field[2],
													in_array($field[2],$unique_fields) ? 'checked' : '',
													__($field[1],'wp-members')
												);
											}
										}
										?>
									</p>
								</div>
								<div style="clear:both;"></div>
							</li>
							<?php
                            DM_WpMembers_Admin::input_text('password_min_length', __('비밀번호 제한', 'danbi-members'), '자 이상', 'small');
                            DM_WpMembers_Admin::checkbox('password_complex', '', __('영문, 숫자, 특수문자 포함', 'danbi-members'));
                            DM_WpMembers_Admin::checkbox('postcode_daum', __('Search postcode', 'danbi-members'), __('Uses <a href="http://postcode.map.daum.net/guide" target="_blank">Daum Postcode Service</a> in the register form', 'danbi-members'));
							?>
                        </ul>
						<h3><?php _e('Register'); ?> - 완료</h3>
						<ul>
							<?php
							DM_WpMembers_Admin::checkbox('ajax_register', __('AJAX 사용','danbi-members'), __('페이지 이동 없이 AJAX 방식으로 회원가입을 처리하고, 오류 메시지는 alert 로 표시합니다.','danbi-members'));
							DM_WpMembers_Admin::select_page('welcome', __('Welcome', 'danbi-members'));
							DM_WpMembers_Admin::checkbox('force_login', __('Logged in after register','danbi-members'), __('Changes the user status to be logged in after register','danbi-members'));
                            DM_WpMembers_Admin::checkbox('email_html', __('Send HTML email', 'danbi-members'), __('Sends a welcome email of the HTML format to the new user.', 'danbi-members'));
							?>
                        </ul>
						<h3><?php _e('Withdrawal', 'danbi-members') ?></h3>
						<ul>
							<?php
                            DM_WpMembers_Admin::select_page('withdrawal', __('Withdrawal', 'danbi-members'));
                            DM_WpMembers_Admin::checkbox('withdrawal_delete_user', __('회원정보 삭제', 'danbi-members'), __('회원탈퇴 시 회원정보를 삭제합니다.', 'danbi-members'));
                            DM_WpMembers_Admin::checkbox('notify_on_withdrawal', __('Notify admin', 'danbi-members'), __('Notifies the administrator of the user\'s withdrawal', 'danbi-members'));
							?>
                        </ul>
						<h3><?php _e('기타 설정'); ?></h3>
						<ul>
							<?php
                            DM_WpMembers_Admin::select_page('password', __('비밀번호 변경', 'danbi-members'));
                            DM_WpMembers_Admin::select_page('find_username', __('Username') . ' ' . __('Find', 'danbi-members'));
                            DM_WpMembers_Admin::checkbox('show_clear_form', __( 'Reset Form', 'wp-members' ) . ' 버튼 표시', '회원가입 폼에서 ' . __( 'Reset Form', 'wp-members' ) . ' 버튼을 표시합니다.');
							?>
                        </ul>
						<br /></br />
						<input type="submit" class="button-primary" value="<?php _e( 'Update Settings', 'wp-members' ); ?> &raquo;" />
                    </div>
				</form>
			</div>
			<?php if (get_option('dbmembers_dismiss_creating_pages') === '1'): ?>
				<div class="postbox">
					<div class="inside" style="padding-bottom:0px;">
						<form id="insert-pages" method="post" class="updatesettings-etc">
							<span style="font-size:15px;"><?php _e('You can create pages of Log in, Register, Welcome, Profile, Withdrawal, Reset Password, Retrieve Username, Terms of Service and Privacy Policy?', 'danbi-members'); ?></span>
							<input type="hidden" id="dbmem-action" name="action" value="dbmembers_create_pages" />
							<?php wp_nonce_field( 'dbmembers_update_settings' ); ?>
							<input type="submit" class="button-default" value="<?php _e('Add Pages','danbi-members'); ?>" />
						</form>
					</div><!-- .inside -->
				</div>
			<?php endif; ?>
		</div><!-- #post-body-content -->
	</div><!-- #post-body -->
</div><!-- .metabox-holder -->
