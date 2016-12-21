<!doctype html >
<!--[if IE 8]>    <html class="ie8" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head>
    <title><?php wp_title('|', true, 'right'); ?></title>
    <meta charset="<?php bloginfo( 'charset' );?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <?php
    wp_head(); /** we hook up in wp_booster @see td_wp_booster_functions::hook_wp_head */
    ?>
</head>

<body <?php body_class() ?> itemscope="itemscope" itemtype="<?php echo td_global::$http_or_https?>://schema.org/WebPage">

    <?php /* scroll to top */?>
    <div class="td-scroll-up"><i class="td-icon-menu-up"></i></div>
    <div id="td-outer-wrap">
    <?php //this is closing in the footer.php file ?>

    <!-- header section -->
    <div class="td-header-wrap td-header-style-1">

    <div class="td-header-top-menu-full">
        <div class="td-container td-header-row td-header-top-menu">

    </div>
    </div>

    <div class="td-banner-wrap-full td-logo-wrap-full">
    <div class="td-container td-header-row td-header-header">
    <div class="td-header-sp-logo">
    <a class="td-main-logo" href="http://thecooya.kr/soomduck">
        <img class="td-retina-data td-retina-version" data-retina="http://thecooya.kr/wp-content/uploads/2016/11/soomduck_logo.png" src="http://thecooya.kr/wp-content/uploads/2016/11/soomduck_logo.png" alt="SOOMDUCK" title="SOOMDUCK" scale="0">
        <span class="td-visual-hidden">soomduck</span>
    </a>
    </div>

        </div>
    </div>

    <div class="td-header-menu-wrap-full" style="height: 48px;">
        <div class="td-header-menu-wrap td-header-gradient" style="transform: translate3d(0px, 0px, 0px);">
            <div class="td-container td-header-row td-header-main-menu">
                <div class="menu-sd" style="float:left; margin-left:3em; ">
                    <a href="http://thecooya.kr/soomduck"> 전체 제품 보기</a>
                </div>
                <div class="menu-sd" style="float:right;">
                    <?php if(is_user_logged_in()){
                      echo '<a href="http://thecooya.kr/soomduck/sd-my-account/customer-logout"> 로그아웃 </a>
                    | <a href="http://thecooya.kr/soomduck/sd-my-account"> 마이 페이지</a>' ;
                    } else {
                      echo '<a href="http://thecooya.kr/soomduck/sd-my-account/customer-logout"> 로그인 </a>' ;
                    } ?>
                </div>
            </div>
        </div>
    </div>

</div>


        <?php
        /*
         * loads the header template set in Theme Panel -> Header area
         * the template files are located in ../parts/header
         */
        //td_api_header_style::_helper_show_header();

        do_action('td_wp_booster_after_header'); //used by unique articles

        ?>
