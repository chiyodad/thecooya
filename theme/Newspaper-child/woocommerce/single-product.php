<?php
/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/woocommerce/single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

td_global::$current_template = 'woo_single';

if(has_term( 'soomduck', 'product_cat' )){
    get_header('sd');
} else {
    get_header();
}

//set the template id, used to get the template specific settings
$template_id = 'woo_single';


$loop_sidebar_position = td_util::get_option('tds_' . $template_id . '_sidebar_pos'); //sidebar right is default (empty)


// read the custom single post settings - this setting overwrites all of them
// YES! WE USE THE SAME SINGLE POST SETTINGS for woo commerce
$td_post_theme_settings = get_post_meta($post->ID, 'td_post_theme_settings', true);
if (!empty($td_post_theme_settings['td_sidebar_position'])) {
    $loop_sidebar_position = $td_post_theme_settings['td_sidebar_position'];
}


// sidebar position used to align the breadcrumb on sidebar left + sidebar first on mobile issue
$td_sidebar_position = '';
if($loop_sidebar_position == 'sidebar_left') {
    $td_sidebar_position = 'td-sidebar-left';
}

//check adult
$user_id = get_current_user_id();
$adult_date = date("Ymd", strtotime("-19 years"));
$birth_date = get_user_meta($user_id, 'namecheck_birthdate', true);
$is_adult = false;
if($birth_date && ((int)$birth_date < (int)$adult_date)){
    $is_adult = true;
}

if(!has_term( 'soomduck', 'product_cat' ) && !$user_id){
   echo '<div class="td-main-content-wrap td-main-page-wrap">'.
        '<div class="td-container">'.
        '<h1> 로그인 후 이용해 주세요. </h1>'.
        '</div></div>';

 }else if(has_term( 'adult', 'product_cat' ) && !$is_adult){
   echo '<div class="td-main-content-wrap td-main-page-wrap">'.
        '<div class="td-container">'.
        '<h1> 성인용 콘텐트입니다. </h1>'.
        '<h1> <a class="content-header-link" href="http://thecooya.kr/profile/"> 성인 인증</a>이 필요합니다. </h1>'.
        '<div class="button-div" id="profile-div-button">'.
        '<a href="http://thecooya.kr/profile/" class="buttons" id="profile-button"> 성인 인증하기 </a>'.
        '</div></div></div>';
 } else {

?>
    <div class="td-main-content-wrap td-main-page-wrap">
        <div class="td-container <?php echo $td_sidebar_position; ?>">
            <div class="td-pb-row">
                <?php
                switch ($loop_sidebar_position) {
                    case 'sidebar_left':
                        ?>
                        <div class="td-pb-span8 td-main-content <?php echo $td_sidebar_position; ?>-content">
                            <div class="td-ss-main-content">
                                <?php
                                    woocommerce_breadcrumb();
                                    woocommerce_content();
                                ?>
                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar">
                            <div class="td-ss-main-sidebar">
                                <?php get_sidebar(); ?>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'no_sidebar':
                        ?>
                        <div class="td-pb-span12 td-main-content">
                            <div class="td-ss-main-content">
                                <?php
                                    woocommerce_breadcrumb();
                                    woocommerce_content();
                                ?>
                            </div>
                        </div>
                        <?php
                        break;


                    default:
                        ?>
                        <div class="td-pb-span8 td-main-content">
                            <div class="td-ss-main-content">
                                <?php
                                    woocommerce_breadcrumb();
                                    woocommerce_content();
                                ?>
                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar">
                            <div class="td-ss-main-sidebar">
                                <?php get_sidebar(); ?>
                            </div>
                        </div>
                        <?php
                        break;
                }?>
            </div>
        </div>
    </div> <!-- /.td-main-content-wrap -->

<?php
 }

if(has_term( 'soomduck', 'product_cat' )){
    get_footer('sd');
} else {
    get_footer( );
}

?>
