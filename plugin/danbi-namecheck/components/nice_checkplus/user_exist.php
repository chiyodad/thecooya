<!DOCTYPE html>

<!--[if IE 8]> <html class="ie ie8" lang="ko-KR"> <![endif]-->
<!--[if IE 9]> <html class="ie ie9" lang="ko-KR"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="ko-KR"> <!--<![endif]-->
<head>
<script type="text/javascript">
if (confirm('이미 회원으로 가입하셨습니다.\n로그인 페이지로 이동하시겠습니까?')) {
    opener.location.href = '<?php echo wp_login_url(); ?>';
}
window.close();
</script>
</head>
</html>