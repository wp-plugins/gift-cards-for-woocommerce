<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

	

function rpgc_extra_check( $product_type_options ) {

	$giftcard = array(
		'giftcard' => array(
			'id' => '_giftcard',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label' => __( 'Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
			'description' => __( 'Make product a gift card.', RPWCGC_CORE_TEXT_DOMAIN )
		),
	);

	// combine the two arrays
	$product_type_options = array_merge( $giftcard, $product_type_options );

	return apply_filters( 'rpgc_extra_check', $product_type_options );
}
add_filter( 'product_type_options', 'rpgc_extra_check' );


function rpgc_process_meta( $post_id, $post ) {
	global $wpdb, $woocommerce, $woocommerce_errors;

	$is_giftcard  = isset( $_POST['_giftcard'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_giftcard', $is_giftcard );

	$want_physical = get_option( 'woocommerce_enable_physical' );

	if ( $want_physical == "no" ) {

		update_post_meta( $post_id, '_virtual', $is_giftcard );
	}

}
add_action( 'save_post', 'rpgc_process_meta', 10, 2 );


//  Sets a unique ID for gift cards so that multiple giftcards can be purchased (Might move to the main gift card Plugin)
function wpr_uniqueID($cart_item_data, $product_id) {
	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( $is_giftcard == "yes" ) {

		$unique_cart_item_key = md5("gc" . microtime().rand());
		$cart_item_data['unique_key'] = $unique_cart_item_key;

	}
	
	return apply_filters( 'wpr_uniqueID', $cart_item_data, $product_id );
}
add_filter('woocommerce_add_cart_item_data','wpr_uniqueID',10,2);




function wpr_change_add_to_cart_button ( $link ) {
	global $post;

	$giftCardText = get_option( "woocommerce_giftcard_button" );
	$is_giftcard = get_post_meta( $post->ID, '_giftcard', true );
	
	if ( $is_giftcard == "yes" && get_option( 'woocommerce_enable_addtocart' ) == "yes" )
		$link = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" rel="nofollow" data-product_id="' . esc_attr( $post->ID ) . '" data-product_sku="' . esc_attr( $post->ID ) . '" class="button product_type_' . esc_attr( $post->product_type ) . '">' . $giftCardText . '</a>';

	return  apply_filters( 'wpr_change_add_to_cart_button', $link, $post);
}


add_filter( 'woocommerce_loop_add_to_cart_link', 'wpr_change_add_to_cart_button' );


