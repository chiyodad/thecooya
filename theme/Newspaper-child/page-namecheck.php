<?php
/*  ----------------------------------------------------------------------------
    the default page template
 */


get_header();

//set the template id, used to get the template specific settings
$template_id = 'page';


$loop_sidebar_position = td_util::get_option('tds_' . $template_id . '_sidebar_pos'); //sidebar right is default (empty)

//get theme panel variable for page comments side wide
$td_enable_or_disable_page_comments = td_util::get_option('tds_disable_comments_pages');


//read the custom single post settings - this setting overids all of them
$td_page = get_post_meta($post->ID, 'td_page', true);
if (!empty($td_page['td_sidebar_position'])) {
    $loop_sidebar_position = $td_page['td_sidebar_position'];
}

// sidebar position used to align the breadcrumb on sidebar left + sidebar first on mobile issue
$td_sidebar_position = '';
if($loop_sidebar_position == 'sidebar_left') {
	$td_sidebar_position = 'td-sidebar-left';
}
?>

<div class="td-main-content-wrap">
    <div class="td-container <?php echo $td_sidebar_position; ?>">
        <div class="td-crumb-container">
            <?php echo td_page_generator::get_page_breadcrumbs(get_the_title()); ?>
        </div>
        <div class="td-pb-row">
            <div class="td-pb-span12 td-main-content" role="main">
                <?php
                    if(!is_user_logged_in()) {
                       echo '<h1>본인인증을 위해 먼저 <a class="content-header-link" style="color: #18bc9c;" id="content-login" href="#login-form" onclick="login();">로그인</a> 해주세요.</h1>';
                    } else {
                ?>
                    <!-- 본인인증 모듈 -->
                    <h1> 본인인증 </h1>
                    <fieldset>
                      <legend>본인인증</legend>
                        <div class="namecheck-area">
                          <ul class="namecheck-list <?php echo $class; ?>">
                          <?php
                              echo apply_filters('nice_checkplus_button', '핸드폰 인증하기');
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
                    </fieldset>
                <?php
                    }
                ?>
            </div>
        </div> <!-- /.td-pb-row -->
    </div> <!-- /.td-container -->
</div> <!-- /.td-main-content-wrap -->
<script>
    var login = function(){
        document.getElementsByClassName("td-login-modal-js")[0].click();
    }
</script>

<?php
get_footer();
?>
