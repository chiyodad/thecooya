<script type="text/javascript">
jQuery(function($) {
    $('#namecheck-redo').click(function() {
        $('.namecheck-complete').hide();
        $('.namecheck-area').show();
        return false;
    });
});
</script>
<legend>본인 인증</legend>
<?php if ($namechecked): ?>
    <div class="namecheck-complete">
        <label><span class="dashicons dashicons-yes"></span>본인 인증을 완료하셨습니다.</label>
        <a href="#" id="namecheck-redo">다시 하기</a>
        <br><br>
        <?php if ($display === '1'): ?>
            <label for="email">이름</label>
            <div class="div_text">
                <p class="noinput"><?php echo $user->namecheck_utf8_name; ?></p>
            </div>
        	<label for="email">생년월일</label>
            <div class="div_text">
                <p class="noinput"><?php echo $user->namecheck_birthdate; ?></p>
            </div>
            <?php if (!empty($user->namecheck_mobile_no)): ?>
        	<label for="email">핸드폰번호</label>
            <div class="div_text">
                <p class="noinput"><?php echo $user->namecheck_mobile_no; ?></p>
            </div>
            <?php endif; ?>
        	<label for="email">성별</label>
            <div class="div_text">
                <p class="noinput"><?php switch ($user->namecheck_gender) { case '0': echo '여성'; break; case '1': echo '남성'; break; default: break; } ?></p>
            </div>
        	<label for="email">국적</label>
            <div class="div_text">
                <p class="noinput"><?php switch ($user->namecheck_nationalinfo) { case '0': echo '내국인'; break; case '1': echo '외국인'; break; default: break; } ?></p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="namecheck-area" <?php if ($namechecked): ?>style="display:none;"<?php endif; ?>>
<ul class="namecheck-list <?php echo $class; ?>">
<?php
if (isset($namecheck['checkplus']))
    echo apply_filters('nice_checkplus_button', '핸드폰 인증하기');
if (isset($namecheck['ipin']))
    echo apply_filters('nice_ipin_button', '아이핀 인증하기');
?>
<li id="namecheck-ok">
	<h4><span class="dashicons dashicons-yes"></span></h4>
    <p>본인인증 성공</p>
</li>
</ul>
<ul class="namecheck-desc">
	<li>※ 입력하신 정보는 본인 확인을 위해 NICE평가정보㈜에 제공됩니다.</li>
	<li>※ 타인의 정보 및 주민등록번호를 부정하게 사용하는 경우 3년 이하의 징역 또는 1천만원 이하의 벌금에 처해지게 됩니다. (관련법률 : 주민등록법 제37조(벌칙))</li>
	<li>※ 법인폰 사용자는 아이핀 인증만 가능합니다.</li>
</ul>
</div>
</fieldset><fieldset>
<legend>회원 정보</legend>
