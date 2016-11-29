<h2>본인인증 정보</h2>
<table class="form-table">
<tbody>
    <tr class="user-email-wrap">
    	<th><label for="email">이름</label></th>
    	<td>
            <input type="text" value="<?php echo $user->namecheck_utf8_name; ?>" class="regular-text ltr" readonly>
    	</td>
    </tr>
    <tr class="user-email-wrap">
    	<th><label for="email">생년월일</label></th>
    	<td>
            <input type="text" value="<?php echo $user->namecheck_birthdate; ?>" class="regular-text ltr" readonly>
    	</td>
    </tr>
    <?php if (!empty($user->namecheck_mobile_no)): ?>
    <tr class="user-email-wrap">
    	<th><label for="email">핸드폰번호</label></th>
    	<td>
            <input type="text" value="<?php echo $user->namecheck_mobile_no; ?>" class="regular-text ltr" readonly>
    	</td>
    </tr>
    <?php endif; ?>
    <tr class="user-email-wrap">
    	<th><label for="email">성별</label></th>
    	<td>
            <input type="text" value="<?php switch ($user->namecheck_gender) { case '0': echo '여성'; break; case '1': echo '남성'; break; default: break; } ?>" class="regular-text ltr" readonly>
    	</td>
    </tr>
    <tr class="user-email-wrap">
    	<th><label for="email">국적</label></th>
    	<td>
            <input type="text" value="<?php switch ($user->namecheck_nationalinfo) { case '0': echo '내국인'; break; case '1': echo '외국인'; break; default: break; } ?>" class="regular-text ltr" readonly>
    	</td>
    </tr>
</tbody></table>
