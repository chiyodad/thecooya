<?php
/*
Plugin Name: thecooya-rating-for-booth
Description: Add thumbs up/down rating to your content.
Author: Ricard Torres
Version: 3.1
Author URI: http://php.quicoto.com/
Text Domain: thecooya-rating-for-booth
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*-----------------------------------------------------------------------------------*/
/* Define the URL and DIR path */
/*-----------------------------------------------------------------------------------*/

define('thumbs_rating_url', plugins_url() ."/".dirname( plugin_basename( __FILE__ ) ) );
define('thumbs_rating_path', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );


/*-----------------------------------------------------------------------------------*/
/* Init */
/* Localization */
/*-----------------------------------------------------------------------------------*/


if  ( ! function_exists( 'thumbs_rating_init' ) ):

	function thumbs_rating_init() {

		load_plugin_textdomain( 'thumbs-rating', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	add_action('plugins_loaded', 'thumbs_rating_init');

endif;



/*-----------------------------------------------------------------------------------*/
/* Encue the Scripts for the Ajax call */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_scripts' ) ):

	function thumbs_rating_scripts()
	{
		wp_enqueue_script('thumbs_rating_scripts', thumbs_rating_url . '/js/general.js', array('jquery'), '4.0.1');
		wp_localize_script( 'thumbs_rating_scripts', 'thumbs_rating_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'thumbs-rating-nonce' ) ) );
	}
	add_action('wp_enqueue_scripts', 'thumbs_rating_scripts');

endif;


/*-----------------------------------------------------------------------------------*/
/* Encue the Styles for the Thumbs up/down */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_styles' ) ):

	function thumbs_rating_styles()
	{

	    wp_register_style( "thumbs_rating_styles",  thumbs_rating_url . '/css/style.css' , "", "1.0.0");
	    wp_enqueue_style( 'thumbs_rating_styles' );
	}
	add_action('wp_enqueue_scripts', 'thumbs_rating_styles');

endif;

/*-----------------------------------------------------------------------------------*/
/* Add the thumbs up/down links to the content */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_getlink' ) ):

	function thumbs_rating_getlink($post_ID = '', $type_of_vote = '')
	{

		// Sanatize params

		$post_ID = intval( sanitize_text_field( $post_ID ) );
		$type_of_vote = intval ( sanitize_text_field( $type_of_vote ) );

		$thumbs_rating_link = "";

		if( $post_ID == '' ) $post_ID = get_the_ID();

		$thumbs_rating_up_count = get_post_meta($post_ID, '_thumbs_rating_up', true) != '' ? get_post_meta($post_ID, '_thumbs_rating_up', true) : '0';
		$thumbs_rating_down_count = get_post_meta($post_ID, '_thumbs_rating_down', true) != '' ? get_post_meta($post_ID, '_thumbs_rating_down', true) : '0';

		$link_up = '<span class="thumbs-rating-up'. ( (isset($thumbs_rating_up_count) && intval($thumbs_rating_up_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 1);" data-text="' . __('성인 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_up_count . '</span>';
		$link_down = '<span class="thumbs-rating-down'. ( (isset($thumbs_rating_down_count) && intval($thumbs_rating_down_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 2);" data-text="' . __('미성년 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_down_count . '</span>';

		$total_vote_count = $thumbs_rating_up_count + $thumbs_rating_down_count ;

		$user_id = get_current_user_id();
		$user_info = get_userdata(get_current_user_id());
		$users_meta = '_thumbs_rating_users' ;
		$rating_users = get_post_meta($post_ID, $users_meta, true) != '' ? get_post_meta($post_ID, $users_meta, true) : '';
    $is_voted = strpos($rating_users, $user_info->user_email);

	  $thumbs_rating_link = '<div  class="thumbs-rating-container" id="thumbs-rating-'.$post_ID.'" data-content-id="'.$post_ID.'">';

		// 로그인 확인
		if( !is_user_logged_in() ) {
			$thumbs_rating_link .= '<span class="thumbs-rating-up disabled'. ( (isset($thumbs_rating_up_count) && intval($thumbs_rating_up_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 1);" data-text="' . __('성인 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_up_count . '</span>';
			$thumbs_rating_link .= '  ';
			$thumbs_rating_link .= '<span class="thumbs-rating-down disabled'. ( (isset($thumbs_rating_down_count) && intval($thumbs_rating_down_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 2);" data-text="' . __('미성년 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_down_count . '</span>';
			$thumbs_rating_link .= '<p><br/><span class="thumbs-rating-total"> 전체 득표수 : '.$total_vote_count.'/100 </span></p>';
			$thumbs_rating_link .= '<span class="thumbs-rating-end" data-text="' . __('로그인 후 투표가 가능합니다.', 'thumbs-rating') . '"></span>';

		// 총 득표수 확인
	  } else if($total_vote_count > 99) {
			$thumbs_rating_link .= '<span class="thumbs-rating-up disabled'. ( (isset($thumbs_rating_up_count) && intval($thumbs_rating_up_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 1);" data-text="' . __('성인 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_up_count . '</span>';
			$thumbs_rating_link .= '  ';
			$thumbs_rating_link .= '<span class="thumbs-rating-down disabled'. ( (isset($thumbs_rating_down_count) && intval($thumbs_rating_down_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 2);" data-text="' . __('미성년 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_down_count . '</span>';
			$thumbs_rating_link .= '<p><br/><span class="thumbs-rating-total"> 전체 득표수 : '.$total_vote_count.'/100 </span></p>';
			$thumbs_rating_link .= '<span class="thumbs-rating-end" data-text="' . __('투표가 종료 되었습니다.', 'thumbs-rating') . '"></span>';

		// 투표 참여 여부 확인
		} else if($is_voted !== FALSE){
			$thumbs_rating_link .= '<span class="thumbs-rating-up disabled'. ( (isset($thumbs_rating_up_count) && intval($thumbs_rating_up_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 1);" data-text="' . __('성인 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_up_count . '</span>';
			$thumbs_rating_link .= '  ';
			$thumbs_rating_link .= '<span class="thumbs-rating-down disabled'. ( (isset($thumbs_rating_down_count) && intval($thumbs_rating_down_count) > 0 ) ? ' thumbs-rating-voted' : '' ) .'" onclick="thumbs_rating_vote(' . $post_ID . ', 2);" data-text="' . __('미성년 부스 참여','thumbs-rating') . '  +">' . $thumbs_rating_down_count . '</span>';
			$thumbs_rating_link .= '<p><br/><span class="thumbs-rating-total"> 전체 득표수 : '.$total_vote_count.'/100 </span></p>';
			$thumbs_rating_link .= '<span class="thumbs-rating-end" data-text="' . __('투표에 참여하셨습니다.', 'thumbs-rating') . '"></span>';

		} else {
		  $thumbs_rating_link .= $link_up;
		  $thumbs_rating_link .= '  ';
		  $thumbs_rating_link .= $link_down;
		  $thumbs_rating_link .= '<p><br/><span class="thumbs-rating-total"> 전체 득표수 : '.$total_vote_count.'/100 </span></p>'.$is_voted;
		  $thumbs_rating_link .= '<span class="thumbs-rating-already-voted" data-text="' . __('이미 투표에 참여하셨습니다.', 'thumbs-rating') . '"></span>';
		  $thumbs_rating_link .= '</div>';
	  }

		return $thumbs_rating_link;
	}

endif;


/*-----------------------------------------------------------------------------------*/
/* Handle the Ajax request to vote up or down */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_add_vote_callback' ) ):

	function thumbs_rating_add_vote_callback()
	{

		// Check the nonce - security
		check_ajax_referer( 'thumbs-rating-nonce', 'nonce' );

		global $wpdb;

		// Get the POST values
		$post_ID = intval( $_POST['postid'] );
		$type_of_vote = intval( $_POST['type'] );

		//check and update the user value
		$user_info = get_userdata(get_current_user_id());
		$users_meta = '_thumbs_rating_users' ;
		$rating_users = get_post_meta($post_ID, $users_meta, true) != '' ? get_post_meta($post_ID, $users_meta, true) : '';
		$rating_users = $user_info->user_email.";".$rating_users;
		update_post_meta($post_ID, $users_meta, $rating_users);

		// Check the type and retrieve the meta values
		if ( $type_of_vote == 1 ){

			$meta_name = "_thumbs_rating_up";

		}elseif( $type_of_vote == 2){

			$meta_name = "_thumbs_rating_down";

		}

		// Retrieve the meta value from the DB
		$thumbs_rating_count = get_post_meta($post_ID, $meta_name, true) != '' ? get_post_meta($post_ID, $meta_name, true) : '0';
		$thumbs_rating_count = $thumbs_rating_count + 1;

		// Update the meta value
		update_post_meta($post_ID, $meta_name, $thumbs_rating_count);
		$results = thumbs_rating_getlink($post_ID, $type_of_vote);

		die($results);
	}

	add_action( 'wp_ajax_thumbs_rating_add_vote', 'thumbs_rating_add_vote_callback' );
	add_action('wp_ajax_nopriv_thumbs_rating_add_vote', 'thumbs_rating_add_vote_callback');

endif;


/*-----------------------------------------------------------------------------------*/
/* Add Votes +/- columns to the Admin */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_columns' ) ):

	function thumbs_rating_columns($columns)
	{
	    return array_merge($columns,
	              array('thumbs_rating_up_count'   => __( '성인',    'thumbs-rating' ),
	                    'thumbs_rating_down_count' => __( '미성년자', 'thumbs-rating' ),
											'thumbs_rating_users' 		 => __( '참가자',  'thumbs-rating')));
	}

	add_filter('manage_posts_columns' , 'thumbs_rating_columns');
	add_filter('manage_pages_columns' , 'thumbs_rating_columns');

endif;


/*-----------------------------------------------------------------------------------*/
/* Add Values to the new Admin columns */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_column_values' ) ):

	function thumbs_rating_column_values( $column, $post_id ) {
	    switch ( $column ) {
		case 'thumbs_rating_up_count' :
		  echo get_post_meta($post_id, '_thumbs_rating_up', true) != '' ? get_post_meta($post_id, '_thumbs_rating_up', true) : '0';
		  break;

		case 'thumbs_rating_down_count' :
		  echo get_post_meta($post_id, '_thumbs_rating_down', true) != '' ? get_post_meta($post_id, '_thumbs_rating_down', true) : '0';
		  break;

		case 'thumbs_rating_users' :
			echo get_post_meta($post_id, '_thumbs_rating_users', true) != '' ? get_post_meta($post_id, '_thumbs_rating_users', true) : '';
			break;
		}
	}

	add_action( 'manage_posts_custom_column' , 'thumbs_rating_column_values', 10, 2 );
	add_action( 'manage_pages_custom_column' , 'thumbs_rating_column_values', 10, 2 );

endif;


/*-----------------------------------------------------------------------------------*/
/* Make our columns are sortable */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_sortable_columns' ) ):

	function thumbs_rating_sortable_columns( $columns )
	{
		$columns[ 'thumbs_rating_up_count' ] = 'thumbs_rating_up_count';
		$columns[ 'thumbs_rating_down_count' ] = 'thumbs_rating_down_count';
		return $columns;
	}


	// Apply this to all public post types

	add_action( 'admin_init', 'thumbs_rating_sort_all_public_post_types' );

	function thumbs_rating_sort_all_public_post_types() {

		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $post_type_name ) {

			add_action( 'manage_edit-' . $post_type_name . '_sortable_columns', 'thumbs_rating_sortable_columns' );
		}

		add_filter( 'request', 'thumbs_rating_column_sort_orderby' );
	}

	// Tell WordPress our fields are numeric

	function thumbs_rating_column_sort_orderby( $vars ) {

		if ( isset( $vars['orderby'] ) && 'thumbs_rating_up_count' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_thumbs_rating_up',
				'orderby'  => 'meta_value_num'
			) );
		}
		if ( isset( $vars['orderby'] ) && 'thumbs_rating_down_count' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_thumbs_rating_down',
				'orderby'  => 'meta_value_num'
			) );
		}
		return $vars;
	}

endif;


/*-----------------------------------------------------------------------------------*/
/* Print our JavaScript function in the footer. We want to check if the user has already voted on the page load */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_check' ) ):

	function thumbs_rating_check(){ ?>

	<script>
		jQuery(document).ready(function() {

			// Get all thumbs containers
			jQuery( ".thumbs-rating-container" ).each(function( index ) {

			 	// Get data attribute
			 	 var content_id = jQuery(this).data('content-id');

			 	 var itemName = "thumbsrating"+content_id;

			 	      // Check if this content has localstorage
			 	 	if (localStorage.getItem(itemName)){

						// Check if it's Up or Down vote
						if ( localStorage.getItem("thumbsrating" + content_id + "-1") ){
							jQuery(this).find('.thumbs-rating-up').addClass('thumbs-rating-voted');
						}
						if ( localStorage.getItem("thumbsrating" + content_id + "-0") ){
							jQuery(this).find('.thumbs-rating-down').addClass('thumbs-rating-voted');
						}
					}
			});
		});
	</script>

	<?php }

	add_action('wp_footer', 'thumbs_rating_check');

endif;


/*-----------------------------------------------------------------------------------*/
/* Two functions to show the ratings values in your theme */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_show_up_votes' ) ):
	function thumbs_rating_show_up_votes ( $post_id = "") {

		   if( $post_id == "" ){

		   	$post_id = get_the_ID();
		   }else{

		   	$post_id = intval( sanitize_text_field( $post_id ) );
		   }

		    return get_post_meta($post_id, '_thumbs_rating_up', true) != '' ? get_post_meta($post_id, '_thumbs_rating_up', true) : '0';
	}
endif;

if  ( ! function_exists( 'thumbs_rating_show_down_votes' ) ):
	function thumbs_rating_show_down_votes ( $post_id = "") {

		   if( $post_id == "" ){

		   	$post_id = get_the_ID();
		   }else{

		   	$post_id = intval( sanitize_text_field( $post_id ) );
		   }

		    return get_post_meta($post_id, '_thumbs_rating_down', true) != '' ? get_post_meta($post_id, '_thumbs_rating_down', true) : '0';
	}
endif;


/*-----------------------------------------------------------------------------------*/
/* Top Votes Shortcode [thumbs_rating_top] */
/*-----------------------------------------------------------------------------------*/

if  ( ! function_exists( 'thumbs_rating_top_func' ) ):
	function thumbs_rating_top_func( $atts ) {

		$return = '';

		// Parameters accepted

		extract( shortcode_atts( array(
			'type' => 'positive',
			'posts_per_page' => 5,
			'category' => '',
			'show_votes' => 'yes',
			'post_type' => 'any',
			'show_both' => 'no',
			'order' => 'DESC'
		), $atts ) );

		// Check wich meta_key the user wants

		if( $type == 'positive' ){

				$meta_key = '_thumbs_rating_up';
				$other_meta_key = '_thumbs_rating_down';
				$sign = "+";
				$other_sign = "+";
		}
		else{
				$meta_key = '_thumbs_rating_down';
				$other_meta_key = '_thumbs_rating_up';
				$sign = "+";
				$other_sign = "+";
		}

		// Build up the args array

	    $args = array (
	    	'post_type'				=> $post_type,
				'post_status'			=> 'publish',
				'cat'					=> $category,
				'pagination'			=> false,
				'posts_per_page'		=> $posts_per_page,
				'cache_results'			=> true,
				'meta_key'				=> $meta_key,
				'order'					=> $order,
				'orderby'				=> 'meta_value_num',
				'ignore_sticky_posts'	=> true
		);

		// Get the posts
		$thumbs_ratings_top_query = new WP_Query($args);

		// Build the post list

		if($thumbs_ratings_top_query->have_posts()) :

			$return .= '<ol class="thumbs-rating-top-list">';

			while($thumbs_ratings_top_query->have_posts()){

				$thumbs_ratings_top_query->the_post();

				$return .= '<li>';

				$return .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';

				if( $show_votes == "yes" ){

					// Get the votes

					$meta_values = get_post_meta(get_the_ID(), $meta_key);

					// Add the votes to the HTML

						$return .= ' (' . $sign;

						if( sizeof($meta_values) > 0){

							$return .= $meta_values[0];

						}else{

							$return .= "0";
						}

						// Show the other votes if needed

						if( $show_both == 'yes' ){

							$other_meta_values = get_post_meta(get_the_ID(), $other_meta_key);

							$return .= " " . $other_sign;

							if( sizeof($other_meta_values) > 0){

								$return .= $other_meta_values[0];

							}else{

								$return .= "0";
							}
						}

						$return .= ')';

					}
				}

				$return .= '</li>';


			$return .= '</ol>';

			// Reset the post data or the sky will fall

			wp_reset_postdata();

		endif;

		return $return;
	}

	add_shortcode( 'thumbs_rating_top', 'thumbs_rating_top_func' );
endif;


/*-----------------------------------------------------------------------------------*/
/* Create Shortcode for the buttons */
/*-----------------------------------------------------------------------------------*/

function thumbs_rating_shortcode_func( $atts ){

	$return = thumbs_rating_getlink();

	return $return;
}
add_shortcode( 'thumbs-rating-buttons', 'thumbs_rating_shortcode_func' );
