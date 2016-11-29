<?php
// for child Theme

// for thumbs rating plugin
add_filter ('the_content', 'add_vote_in_surveys');
function add_vote_in_surveys($content) {
  $post_type = get_post_type(get_the_ID());
  if(is_single() && $post_type == 'surveys') {
    $content .= thumbs_rating_getlink();
  }
  return $content;
}

add_theme_support( 'post-surveys' );

/**
 * goes in theme functions.php or a custom plugin
 *
 * By default login goes to my account
 */
add_filter('woocommerce_login_widget_redirect', 'custom_login_redirect');

function custom_login_redirect( $redirect_to ) {
    return 'http://thecooya.kr/soomduck';
}

// // Our hooked in function - $fields is passed via the filter!
// add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
// function custom_override_checkout_fields( $fields ) {
//      unset($fields['billing']['billing_last_name']);
//      unset($fields['billing']['billing_company']);
//      unset($fields['billing']['billing_country']);
//      unset($fields['order']['order_comments']);
//
//      return $fields;
// }

?>
