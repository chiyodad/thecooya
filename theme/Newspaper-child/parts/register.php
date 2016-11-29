<?php
//check if admin allow registration
$users_can_register = get_option('users_can_register');

$users_can_register_form = '';

$social_login_widget = '
<div class="wp-social-login-widget">
<div class="wp-social-login-connect-with">소셜 로그인</div>
<div class="wp-social-login-provider-list">
		<a rel="nofollow" href="http://thecooya.kr/login/?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Facebook&amp;redirect_to=http%3A%2F%2Fthecooya.kr%2F" title="Connect with Facebook" class="wp-social-login-provider wp-social-login-provider-facebook" data-provider="Facebook">
			<img alt="Facebook" title="Connect with Facebook" src="http://thecooya.kr/wp-content/plugins/wordpress-social-login/assets/img/32x32/wpzoom/facebook.png" scale="0">
		</a>
		<a rel="nofollow" href="http://thecooya.kr/login/?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Twitter&amp;redirect_to=http%3A%2F%2Fthecooya.kr%2F" title="Connect with Twitter" class="wp-social-login-provider wp-social-login-provider-twitter" data-provider="Twitter">
			<img alt="Twitter" title="Connect with Twitter" src="http://thecooya.kr/wp-content/plugins/wordpress-social-login/assets/img/32x32/wpzoom/twitter.png" scale="0">
		</a>
		<a rel="nofollow" href="http://thecooya.kr/login/?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Naver&amp;redirect_to=http%3A%2F%2Fthecooya.kr%2F" title="Connect with Naver" class="wp-social-login-provider wp-social-login-provider-naver" data-provider="Naver">
			<img alt="Naver" title="Connect with Naver" src="http://thecooya.kr/wp-content/plugins/wsl-login-extends-naver/assets/img/32x32/wpzoom/Naver.png" scale="0">
		</a>
</div>
<div class="wp-social-login-widget-clearing"></div>
</div>';

if ($users_can_register == 1) {

	//add the Register tab to the modal window if `Anyone can register` chec
	//$users_can_register_tab = ' / <a id="register-link">' . __td('REGISTER', TD_THEME_NAME) . '</a>';
  $users_can_register_form = '
            <div id="td-register-mob" class="td-login-animation td-login-hide-mob">
            	<!-- close button -->
	            <div class="td-register-close">
	                <a href="#" class="td-back-button"><i class="td-icon-read-down"></i></a>
	                <div class="td-login-title">' . __td('Sign up', TD_THEME_NAME) . '</div>
	                <!-- close button -->
		            <div class="td-mobile-close">
		                <a href="#"><i class="td-icon-close-mobile"></i></a>
		            </div>
	            </div>
            	<div class="td-login-panel-title"><span>' . __td('Welcome!', TD_THEME_NAME) . '</span>' . __td('Register for an account', TD_THEME_NAME) .'</div>
                <div class="td-login-form-wrap">
	                <div class="td_display_err"></div>'
									.$social_login_widget.
									'<div class="td-login-inputs"><input class="td-login-input" type="text" name="register_email" id="register_email-mob" value="" required><label>' . __td('your email', TD_THEME_NAME) .'</label></div>
	                <div class="td-login-inputs"><input class="td-login-input" type="text" name="register_user" id="register_user-mob" value="" required><label>' . __td('your username', TD_THEME_NAME) .'</label></div>
	                <input type="button" name="register_button" id="register_button-mob" class="td-login-button" value="' . __td('REGISTER', TD_THEME_NAME) . '">
	                <div class="td-login-info-text">' . __td('A password will be e-mailed to you.', TD_THEME_NAME) . '</div>
                </div>
            </div>';
}

echo '
            <div id="td-login-mob" class="td-login-animation td-login-hide-mob">
            	<!-- close button -->
	            <div class="td-login-close">
	                <a href="#" class="td-back-button"><i class="td-icon-read-down"></i></a>
	                <div class="td-login-title">' . __td('Sign in', TD_THEME_NAME) . '</div>
	                <!-- close button -->
		            <div class="td-mobile-close">
		                <a href="#"><i class="td-icon-close-mobile"></i></a>
		            </div>
	            </div>
	            <div class="td-login-form-wrap">
	                <div class="td-login-panel-title"><span>' . __td('Welcome!', TD_THEME_NAME) . '</span>' . __td('Log into your account', TD_THEME_NAME) .'</div>
	                <div class="td_display_err"></div>';

									do_action( 'wordpress_social_login' ); // add social login

echo             '<div class="td-login-inputs"><input class="td-login-input" type="text" name="login_email" id="login_email-mob" value="" required><label>' . __td('your username', TD_THEME_NAME) .'</label></div>
	                <div class="td-login-inputs"><input class="td-login-input" type="password" name="login_pass" id="login_pass-mob" value="" required><label>' . __td('your password', TD_THEME_NAME) .'</label></div>
	                <input type="button" name="login_button" id="login_button-mob" class="td-login-button" value="' . __td('LOG IN', TD_THEME_NAME) . '">
	                <div class="td-login-info-text"><a href="#" id="forgot-pass-link-mob">' . __td('Forgot your password?', TD_THEME_NAME) . '</a></div>
                </div>
            </div>

            ' . $users_can_register_form . '

            <div id="td-forgot-pass-mob" class="td-login-animation td-login-hide-mob">
                <!-- close button -->
	            <div class="td-forgot-pass-close">
	                <a href="#" class="td-back-button"><i class="td-icon-read-down"></i></a>
	                <div class="td-login-title">' . __td('Password recovery', TD_THEME_NAME) . '</div>
	            </div>
	            <div class="td-login-form-wrap">
	                <div class="td-login-panel-title">' . __td('Recover your password', TD_THEME_NAME) .'</div>
	                <div class="td_display_err"></div>
	                <div class="td-login-inputs"><input class="td-login-input" type="text" name="forgot_email" id="forgot_email-mob" value="" required><label>' . __td('your email', TD_THEME_NAME) .'</label></div>
	                <input type="button" name="forgot_button" id="forgot_button-mob" class="td-login-button" value="' . __td('Send My Pass', TD_THEME_NAME) . '">
                </div>
            </div>
';
