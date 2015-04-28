<?php
/**
 * Gift Card Save Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Creates a random 15 digit giftcard number
 *
 */
function rpgc_create_number( $data , $postarr ) {
	
	if ( $data['post_type'] == 'rp_shop_giftcard'  ) {
		if( ! isset ( $_POST['original_publish'] ) || ( $_POST["rpgc_regen_number"] == "yes" ) ) {
			$myNumber = rpgc_generate_number( );
			
			$data["post_title"] = $myNumber;
			$data["post_name"] = $myNumber;
		}
	}

	return apply_filters('rpgc_create_number', $data);
}
add_filter( 'wp_insert_post_data' , 'rpgc_create_number' , 10, 2 );


function rpgc_email_content_return( $email ) {
	$customEmail = get_option( 'woocommerce_enable_giftcard_custom_message' );

	if ($customEmail <> '' ) {
		$customEmail .= '<div>';
		$customEmial .= '<h4>' . __( 'Gift Card Amount', 'rpgiftcards' ) . ': ' . woocommerce_price( wpr_get_giftcard_balance( $giftCard->ID ) ) . '</h4>';
		$customEmail .= '<h4>' . __( 'Gift Card Number', 'rpgiftcards' ) . ': ' . $giftCard->post_title . '</h4>';
		$customEmail .= '</div>';
		
		return $customEmail;

	}
	
	return $email;
}




function rpgc_generate_number( ) {
	$randomNumber = substr( number_format( time() * rand(), 0, '', '' ), 0, 15 );

	return apply_filters('rpgc_generate_number', $randomNumber);
}

/**
 * Function to refund the amount paid by Giftcard back to the Card when the entire order is refunded
 *
 */
function rpgc_refund_order( $order_id ) {
	
	$giftCard_id = get_post_meta( $order_id, 'rpgc_id', true );
	$giftCard_refunded = get_post_meta( $order_id, 'rpgc_refunded', true );

	if ( $giftCard_id  && ! ( $giftCard_refunded == 'yes' ) ) {

		$oldBalance = wpr_get_giftcard_balance( $giftCard_id );
		$refundAmount = get_post_meta( $order_id, 'rpgc_payment', true );

		$giftcard_balance = (float) $oldBalance + (float) $refundAmount;

		update_post_meta( $giftCard_id, 'rpgc_balance', $giftcard_balance ); // Update balance of Giftcard
		update_post_meta( $order_id, 'rpgc_refunded', 'yes' ); // prevents multiple refunds of Giftcard
	}
}
add_action( 'woocommerce_order_status_refunded', 'rpgc_refund_order' );
add_action( 'woocommerce_order_status_pending_to_cancelled', 'rpgc_refund_order' );
add_action( 'woocommerce_order_status_on-hold_to_cancelled', 'rpgc_refund_order' );

function wpr_display_giftcard_on_order ( $order_id ) {
	
	$giftPayment = wpr_get_order_card_payment( $order_id );

	if( $giftPayment > 0 ) {
		?>
		<tr>
			<td class="label"><?php _e( 'Gift Card Payment', 'rpgiftcards' ); ?>:</td>
			<td class="giftcardTotal">
				<div class="view"><?php echo wc_price( $giftPayment ); ?></div>
			</td>
		</tr>
		<?php
	}

}
add_action ( 'woocommerce_admin_order_totals_after_discount', 'wpr_display_giftcard_on_order' );
