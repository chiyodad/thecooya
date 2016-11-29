<legend>본인 인증</legend>
<div class="namecheck-area">
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
