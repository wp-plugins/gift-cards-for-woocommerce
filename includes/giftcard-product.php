<?php
/**
 * Gift Card Product Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

	

function rpgc_extra_check( $product_type_options ) {

	$giftcard = array(
		'giftcard' => array(
			'id' => '_giftcard',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label' => __( 'Gift Card', 'rpgiftcards' ),
			'description' => __( 'Make product a gift card.', 'rpgiftcards' )
		),
	);

	// combine the two arrays
	$product_type_options = array_merge( $giftcard, $product_type_options );

	return apply_filters( 'rpgc_extra_check', $product_type_options );
}
add_filter( 'product_type_options', 'rpgc_extra_check' );

function rpgc_process_meta( $post_id, $post ) {
	global $wpdb, $woocommerce, $woocommerce_errors;

	if ( get_post_type( $post_id ) == 'product' ) {

		$is_giftcard  = isset( $_POST['_giftcard'] ) ? 'yes' : 'no';

		if( $is_giftcard == 'yes' ) {

			update_post_meta( $post_id, '_giftcard', $is_giftcard );
			
			if ( get_option( "woocommerce_enable_multiples") != "yes" ) {
				update_post_meta( $post_id, '_sold_individually', $is_giftcard );
			}

			$want_physical = get_option( 'woocommerce_enable_physical' );

			if ( $want_physical == "no" ) {
				update_post_meta( $post_id, '_virtual', $is_giftcard );
			}
		}
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

	if ( preventAddToCart( $post->ID ) ) {
		$giftCard_button = get_option( "woocommerce_giftcard_button" );

		if( $giftCard_button <> '' ){
			$giftCardText = get_option( "woocommerce_giftcard_button" );
		} else {
			$giftCardText = 'Customize';
		}

		$link = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" rel="nofollow" data-product_id="' . esc_attr( $post->ID ) . '" data-product_sku="' . esc_attr( $post->ID ) . '" class="button product_type_' . esc_attr( $post->product_type ) . '">' . $giftCardText . '</a>';
	}

	return  apply_filters( 'wpr_change_add_to_cart_button', $link, $post);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'wpr_change_add_to_cart_button' );


function preventAddToCart( $id ){
	$return = false;
	$is_giftcard = get_post_meta( $id, '_giftcard', true );

	if ( $is_giftcard == "yes" && get_option( 'woocommerce_enable_addtocart' ) == "yes" )
		$return = true;

	return apply_filters( 'wpr_preventAddToCart', $return, $id );
}


function rpgc_cart_fields( ) {
	global $post;

	$is_giftcard = get_post_meta( $post->ID, '_giftcard', true );
	$is_required_field_giftcard = get_option( 'woocommerce_enable_giftcard_info_requirements' );

	if ( $is_giftcard == 'yes' ) {

		do_action( 'rpgc_before_all_giftcard_fields', $post );
		
		$rpgc_to 		= ( isset( $_POST['rpgc_to'] ) ? sanitize_text_field( $_POST['rpgc_to'] ) : "" );
		$rpgc_to_email 	= ( isset( $_POST['rpgc_to_email'] ) ? sanitize_text_field( $_POST['rpgc_to_email'] ) : "" );
		$rpgc_note		= ( isset( $_POST['rpgc_note'] ) ? sanitize_text_field( $_POST['rpgc_note'] ) : ""  );

		$rpw_to_check 		= ( get_option( 'woocommerce_giftcard_to' ) <> NULL ? get_option( 'woocommerce_giftcard_to' ) : __('To', 'rpgiftcards' ) );
		$rpw_toEmail_check 	= ( get_option( 'woocommerce_giftcard_toEmail' ) <> NULL ? get_option( 'woocommerce_giftcard_toEmail' ) : __('To Email', 'rpgiftcards' )  );
		$rpw_note_check		= ( get_option( 'woocommerce_giftcard_note' ) <> NULL ? get_option( 'woocommerce_giftcard_note' ) : __('Note', 'rpgiftcards' )  );

		?>

		<div>
			<?php if ( $is_required_field_giftcard == "yes" ) { ?>
				<div class="rpw_product_message"><?php _e('All fields below are required', 'rpgiftcards' ); ?></div>
			<?php } else { ?>
				<div class="rpw_product_message"><?php _e('All fields below are optional', 'rpgiftcards' ); ?></div>
			<?php } ?>

			<?php  do_action( 'rpgc_before_product_fields' ); ?>
			<input type="hidden" id="rpgc_description" name="rpgc_description" value="<?php _e('Generated from the website.', 'rpgiftcards' ); ?>" />
			<input type="text" name="rpgc_to" id="rpgc_to" class="input-text" placeholder="<?php echo $rpw_to_check; ?>" style="margin-bottom:5px;" value="<?php echo $rpgc_to; ?>">
			<input type="email" name="rpgc_to_email" id="rpgc_to_email" class="input-text" placeholder="<?php echo $rpw_toEmail_check; ?>" style="margin-bottom:5px;" value="<?php echo $rpgc_to_email; ?>">
			<textarea class="input-text" id="rpgc_note" name="rpgc_note" rows="2" placeholder="<?php echo $rpw_note_check; ?>" style="margin-bottom:5px;"><?php echo $rpgc_note; ?></textarea>
			<?php  do_action( 'rpgc_after_product_fields' ); ?>
		</div>
		<?php

		if ( get_option( "woocommerce_enable_multiples") != 'yes' ) {
			echo '
				<script>
					jQuery( document ).ready( function( $ ){ $( ".quantity" ).hide( ); });
				</script>
		    ';
		}
	}
}
add_action( 'woocommerce_before_add_to_cart_button', 'rpgc_cart_fields' );

function wpr_add_to_cart_validation( $passed, $product_id, $quantity ) {
	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );
	$is_required_field_giftcard = get_option( 'woocommerce_enable_giftcard_info_requirements' );

	if ( $is_required_field_giftcard == "yes" && $is_giftcard == "yes" ) {

		if ( $_POST['rpgc_to'] == "" ) {
			$notice = __( 'Please enter a name for the gift card.', 'rpgiftcards' );
			wc_add_notice( $notice, 'error' );
			$passed = false;
		}
		if ( $_POST['rpgc_to_email'] == "" ) {
			$notice = __( 'Please enter an email address for the gift card.', 'rpgiftcards' );
			wc_add_notice( $notice, 'error' );
			$passed = false;
		}
		if ( $_POST['rpgc_note'] == "" ) {
			$notice = __( 'Please enter a note for the gift card.', 'rpgiftcards' );
			wc_add_notice( $notice, 'error' );
			$passed = false;
		}
		
		$passed = apply_filters( 'wpr_other_validations', $passed, $product_id, $quantity );
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wpr_add_to_cart_validation', 10, 3 );


function rpgc_add_card_data( $cart_item_key, $product_id, $quantity ) {
	global $woocommerce, $post;

	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( $is_giftcard == "yes" ) {

		$giftcard_data = array(
			'To'    	=> 'NA',
			'To Email'  => 'NA',
			'Note'   	=> 'NA',
		);

		if ( isset( $_POST['rpgc_to'] ) && ( $_POST['rpgc_to'] <> '' ) ) 
			$giftcard_data['To'] = woocommerce_clean( $_POST['rpgc_to'] );

		if ( isset( $_POST['rpgc_to_email'] ) && ( $_POST['rpgc_to_email'] <> '' ) ) 
			$giftcard_data['To Email'] = woocommerce_clean( $_POST['rpgc_to_email'] );

		if ( isset( $_POST['rpgc_note'] ) && ( $_POST['rpgc_note'] <> '' ) ) 
			$giftcard_data['Note'] = woocommerce_clean( $_POST['rpgc_note'] );

		$giftcard_data = apply_filters( 'rpgc_giftcard_data', $giftcard_data, $_POST );

		WC()->cart->cart_contents[$cart_item_key]["variation"] = $giftcard_data;
		return $woocommerce;
	}
	
}
add_action( 'woocommerce_add_to_cart', 'rpgc_add_card_data', 10, 3 );




