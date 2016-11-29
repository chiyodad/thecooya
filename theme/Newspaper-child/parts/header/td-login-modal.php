<!-- LOGIN MODAL -->
<?php
//check if admin allow registration
$users_can_register = get_option('users_can_register');

//if admin permits registration
$users_can_register_link = '';
$users_can_register_form = '';
// add social login widget
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

if($users_can_register == 1){
    //add the Register tab to the modal window if `Anyone can register` check
    $users_can_register_link = '<a id="register-link">' . __td('Create an account', TD_THEME_NAME) . '</a>';

    $users_can_register_form = '
                <div id="td-register-div" class="td-login-form-div td-display-none">
                    <div class="td-login-panel-title">' . __td('Create an account', TD_THEME_NAME) . '</div>
                    <div class="td-login-panel-descr">' . __td('Welcome! Register for an account', TD_THEME_NAME) .'</div>
                    <div class="td_display_err"></div>'
                    .$social_login_widget.
                    '<div class="td-login-inputs"><input class="td-login-input" type="text" name="register_email" id="register_email" value="" required><label>' . __td('your email', TD_THEME_NAME) .'</label></div>
                    <div class="td-login-inputs"><input class="td-login-input" type="text" name="register_user" id="register_user" value="" required><label>' . __td('your username', TD_THEME_NAME) .'</label></div>
                    <input type="button" name="register_button" id="register_button" class="wpb_button btn td-login-button" value="' . __td('Register', TD_THEME_NAME) . '">
                    <div class="td-login-info-text">' . __td('A password will be e-mailed to you.', TD_THEME_NAME) . '</div>
                </div>';
}

echo '
                <div  id="login-form" class="white-popup-block mfp-hide mfp-with-anim">
                    <div class="td-login-wrap">
                        <a href="#" class="td-back-button"><i class="td-icon-modal-back"></i></a>
                        <div id="td-login-div" class="td-login-form-div td-display-block">
                            <div class="td-login-panel-title">' . __td('Sign in', TD_THEME_NAME) . '</div>
                            <div class="td-login-panel-descr">' . __td('Welcome! Log into your account', TD_THEME_NAME) .'</div>';
                            do_action( 'wordpress_social_login' ); // add social login
echo                       '<div class="td_display_err"></div>
                            <div class="td-login-inputs"><input class="td-login-input" type="text" name="login_email" id="login_email" value="" required><label>' . __td('your username', TD_THEME_NAME) .'</label></div>
	                        <div class="td-login-inputs"><input class="td-login-input" type="password" name="login_pass" id="login_pass" value="" required><label>' . __td('your password', TD_THEME_NAME) .'</label></div>
                            <input type="button" name="login_button" id="login_button" class="wpb_button btn td-login-button" value="' . __td('Login', TD_THEME_NAME) . '">
                            <div class="td-login-info-text"><a href="#" id="forgot-pass-link">' . __td('Forgot your password? Get help', TD_THEME_NAME) . '</a></div>
                            ' . $users_can_register_link . '
                        </div>

                        ' . $users_can_register_form . '

                         <div id="td-forgot-pass-div" class="td-login-form-div td-display-none">
                            <div class="td-login-panel-title">' . __td('Password recovery', TD_THEME_NAME) . '</div>
                            <div class="td-login-panel-descr">' . __td('Recover your password', TD_THEME_NAME) .'</div>
                            <div class="td_display_err"></div>
                            <div class="td-login-inputs"><input class="td-login-input" type="text" name="forgot_email" id="forgot_email" value="" required><label>' . __td('your email', TD_THEME_NAME) .'</label></div>
                            <input type="button" name="forgot_button" id="forgot_button" class="wpb_button btn td-login-button" value="' . __td('Send My Password', TD_THEME_NAME) . '">
                            <div class="td-login-info-text">' . __td('A password will be e-mailed to you.', TD_THEME_NAME) . '</div>
                        </div>
                    </div>
                </div>
                ';
?>
