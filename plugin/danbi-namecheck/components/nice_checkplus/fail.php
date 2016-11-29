<!DOCTYPE html>
<?php
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

$result = Danbi_Namecheck::get_result();
// print_r(Danbi_Namecheck::get_result());
?>
<!--[if IE 8]> <html class="ie ie8" lang="ko-KR"> <![endif]-->
<!--[if IE 9]> <html class="ie ie9" lang="ko-KR"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="ko-KR"> <!--<![endif]-->
<head>
    <script type="text/javascript">
        alert('본인인증에 실패하였습니다.\n(<?php echo $data['message']; ?>)');
        window.close();
    </script>
</head>
</html>