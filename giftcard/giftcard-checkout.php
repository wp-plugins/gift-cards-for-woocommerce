<?php
/**
 * Product Functions
 *
 * @package     Woocommerce
 * @subpackage  Giftcards
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * AJAX apply coupon on checkout page
 * @access public
 * @return void
 */
function woocommerce_ajax_apply_giftcard($giftcard_code) {
	global $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftcard_number = sanitize_text_field( $_POST['giftcard_code'] );

		if ( ! isset( WC()->session->giftcard_post ) ) {
			$giftcard_id = wpr_get_giftcard_by_code( $giftcard_number );

			if ( $giftcard_id ) {
				$current_date = date("Y-m-d");
				$cardExperation = wpr_get_giftcard_expiration( $giftcard_id );

				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {
					if( wpr_get_giftcard_balance( $giftcard_id ) > 0 ) {
						WC()->session->giftcard_post = $giftcard_id;

						wc_add_notice(  __( 'Gift card applied successfully.', WPR_CORE_TEXT_DOMAIN ), 'success' );

					} else {
						wc_add_notice( __( 'Gift Card does not have a balance!', WPR_CORE_TEXT_DOMAIN ), 'error' );
					}
				} else {
					wc_add_notice( __( 'Gift Card has expired!', WPR_CORE_TEXT_DOMAIN ), 'error' ); // Giftcard Entered has expired
				}
			} else {
				wc_add_notice( __( 'Gift Card does not exist!', WPR_CORE_TEXT_DOMAIN ), 'error' ); // Giftcard Entered does not exist
			}
		} else {		
			wc_add_notice( __( 'Gift Card already in the cart!', WPR_CORE_TEXT_DOMAIN ), 'error' );  //  You already have a gift card in the cart
		}

		wc_print_notices();

		die();
	}
}
add_action( 'wp_ajax_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );
add_action( 'wp_ajax_nopriv_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );


function woocommerce_apply_giftcard($giftcard_code) {
	global $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftcard_number = sanitize_text_field( $_POST['giftcard_code'] );

		if ( ! isset( WC()->session->giftcard_post ) ) {
			$giftcard_id = wpr_get_giftcard_by_code( $giftcard_number );

			if ( $giftcard_id ) {
				$current_date = date("Y-m-d");
				$cardExperation = wpr_get_giftcard_expiration( $giftcard_id );

				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {
					if( wpr_get_giftcard_balance( $giftcard_id ) > 0 ) {
						WC()->session->giftcard_post = $giftcard_id;

						wc_add_notice(  __( 'Gift card applied successfully.', WPR_CORE_TEXT_DOMAIN ), 'success' );

					} else {
						wc_add_notice( __( 'Gift Card does not have a balance!', WPR_CORE_TEXT_DOMAIN ), 'error' );
					}
				} else {
					wc_add_notice( __( 'Gift Card has expired!', WPR_CORE_TEXT_DOMAIN ), 'error' ); // Giftcard Entered has expired
				}
			} else {
				wc_add_notice( __( 'Gift Card does not exist!', WPR_CORE_TEXT_DOMAIN ), 'error' ); // Giftcard Entered does not exist
			}
		} else {		
			wc_add_notice( __( 'Gift Card already in the cart!', WPR_CORE_TEXT_DOMAIN ), 'error' );  //  You already have a gift card in the cart
		}

		wc_print_notices();

	}
}













/**
 * Function to add the giftcard data to the cart display on both the card page and the checkout page WC()->session->giftcard_balance
 *
 */
function rpgc_order_giftcard( ) {
	global $woocommerce;

	if ( isset( $_GET['remove_giftcards'] ) ) {
		$type = $_GET['remove_giftcards'];

		if ( 1 == $type ) {
			unset( WC()->session->giftcard_payment, WC()->session->giftcard_post );
			WC()->cart->calculate_totals();
		}
	}

	if ( isset( WC()->session->giftcard_post ) ) {
		if ( WC()->session->giftcard_post ){

			$currency_symbol = get_woocommerce_currency_symbol();
			//$price = WC()->session->giftcard_payment;

			$price = WC()->session->giftcard_payment;

			$gotoPage = WC()->cart->get_checkout_url();

			if ( is_cart() )
				$gotoPage = WC()->cart->get_cart_url();


			?>

			<tr class="giftcard">
				<th><?php _e( 'Gift Card Payment', WPR_CORE_TEXT_DOMAIN ); ?> </th>

				<td style="font-size:0.85em;"><?php echo woocommerce_price( $price ); ?> <a href="<?php echo add_query_arg( 'remove_giftcards', '1', $gotoPage ) ?>">[<?php _e( 'Remove Gift Card', WPR_CORE_TEXT_DOMAIN ); ?>]</a></td>
			</tr>

			<?php

		}
	}
}
add_action( 'woocommerce_review_order_before_order_total', 'rpgc_order_giftcard' );
add_action( 'woocommerce_cart_totals_before_order_total', 'rpgc_order_giftcard' );

/**
 * Function to decrease the cart amount by the amount in the giftcard
 *
 */
function subtract_giftcard( $wc_cart ) {
	$giftcard_id = WC()->session->giftcard_post;

	if ( isset( $giftcard_id ) ) {
		$balance = wpr_get_giftcard_balance( WC()->session->giftcard_post );
		$whenToProcess = get_option('woocommerce_enable_giftcard_process');

		if ( $whenToProcess == 'no' ) {
			if ( $wc_cart->cart_contents_total > $balance ) {
				$wc_cart->cart_contents_total = $wc_cart->cart_contents_total - $balance;
				WC()->session->giftcard_payment = $balance;
			} else {
				WC()->session->giftcard_payment = $wc_cart->cart_contents_total;
				$wc_cart->cart_contents_total = 0;
			}
		} else {
			$myTotal 		= $wc_cart->cart_contents_total;
			$myShipping 	= $wc_cart->shipping_total;
			$myTax			= $wc_cart->tax_total;
			$grandTotal		= $myTotal + $myShipping + $myTax;
			
			if ( $grandTotal > $balance ) {
				
				WC()->session->giftcard_payment = $balance;

				if( $myTax > $balance ){
					$wc_cart->tax_total = $wc_cart->tax_total - $balance;
					$balance = 0;
				} else {
					$wc_cart->tax_total = 0;
					$balance = $balance - $myTax;
				}

				if( $myShipping > $balance ){
					$wc_cart->shipping_total = $wc_cart->shipping_total - $balance;
					$balance = 0;
				} else {
					$wc_cart->shipping_total = 0;
					$balance = $balance - $myShipping;
				}

				if( $myTotal > $balance ){
					$wc_cart->cart_contents_total = $wc_cart->cart_contents_total - $balance;
					$balance = 0;
				} else {
					$wc_cart->cart_contents_total = 0;
					$balance = $balance - $myTotal;
				}

			} else {
				WC()->session->giftcard_payment = $myTotal + $myShipping + $myTax;
				$wc_cart->cart_contents_total 	= 0;
				$wc_cart->shipping_total 		= 0;
				$wc_cart->tax_total 			= 0;
			}
		}
	}
}
add_action( 'woocommerce_calculate_totals', 'subtract_giftcard' );

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

function wpr_validate_form_complete( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {

	$passed = true;

	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );
	$is_required_field_giftcard = get_option( 'woocommerce_enable_giftcard_info_requirements' );

	if ( ( $is_giftcard == "yes" ) && ( $is_required_field_giftcard == "yes" ) ) {

		if ( $_POST["rpgc_to"] == '' ) { $passed = false; }
		if ( $_POST["rpgc_to_email"] == '' ) { $passed = false; }

		if ( $passed == false ) {
	        wc_add_notice( __( 'Please complete form.', WPR_CORE_TEXT_DOMAIN ), 'error' );
		}
	}
	
    return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'wpr_validate_form_complete', 10, 5 );

/**
 * Displays the giftcard data on the order thank you page
 *
 */
function rpgc_display_giftcard( $order ) {
	global $woocommerce;

	
	$theIDNum =  get_post_meta( $order->id, 'rpgc_id', true );
	$theBalance = get_post_meta( $order->id, 'rpgc_balance', true );

	if( isset( $theIDNum ) ) {
		if ( $theIDNum <> '' ) {
		?>
			<h4><?php _e( 'Gift Card Balance After Order:', WPR_CORE_TEXT_DOMAIN ); ?><?php echo ' ' . woocommerce_price( $theBalance ); ?> <?php do_action('wpr_after_remaining_balance', $theIDNum, $theBalance ); ?></h4>

			<?php
			
		}
	}

	$theGiftCardData = get_post_meta( $order->id, 'rpgc_data', true );
	if( isset( $theGiftCardData ) ) {
		if ( $theGiftCardData <> '' ) {
	?>
			<h4><?php _e( 'Gift Card Information:', WPR_CORE_TEXT_DOMAIN ); ?></h4>
			<?php
			$i = 1;

			foreach ( $theGiftCardData as $giftcard ) {

				if ( $i % 2 ) echo '<div style="margin-bottom: 10px;">';
				echo '<div style="float: left; width: 45%; margin-right: 2%;>';
				echo '<h6><strong> ' . __('Giftcard',  WPR_CORE_TEXT_DOMAIN ) . ' ' . $i . '</strong></h6>';
				echo '<ul style="font-size: 0.85em; list-style: none outside none;">';
				if ( $giftcard[rpgc_product_num] ) 	echo '<li>' . __('Card', WPR_CORE_TEXT_DOMAIN) . ': ' . get_the_title( $giftcard[rpgc_product_num] ) . '</li>';
				if ( $giftcard[rpgc_to] ) 			echo '<li>' . __('To',  WPR_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_to] . '</li>';
				if ( $giftcard[rpgc_to_email] ) 	echo '<li>' . __('Send To',  WPR_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_to_email] . '</li>';
				if ( $giftcard[rpgc_balance] ) 		echo '<li>' . __('Balance',  WPR_CORE_TEXT_DOMAIN ) . ': ' . woocommerce_price( $giftcard[rpgc_balance] ) . '</li>';
				if ( $giftcard[rpgc_note] ) 		echo '<li>' . __('Note',  WPR_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_note] . '</li>';
				if ( $giftcard[rpgc_quantity] ) 	echo '<li>' . __('Quantity',  WPR_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_quantity] . '</li>';
				echo '</ul>';
				echo '</div>';
				if ( !( $i % 2 ) ) echo '</div>';
				$i++;
			}
			echo '<div class="clear"></div>';
		}
	}
}
add_action( 'woocommerce_order_details_after_order_table', 'rpgc_display_giftcard' );
add_action( 'woocommerce_email_after_order_table', 'rpgc_display_giftcard' );


function rpgc_add_order_giftcard( $total_rows,$order ) {
	global $woocommerce;

	$return = array();

	$order_id = $order->id;

	$giftCardPayment = get_post_meta( $order_id, 'rpgc_payment', true);

	if ( $giftCardPayment <> 0 ) {
		$newRow['rpgc_data'] = array(
			'label' => __( 'Gift Card Payment:', WPR_CORE_TEXT_DOMAIN ),
			'value'	=> woocommerce_price( -1 * $giftCardPayment )
		);

		if( get_option( 'woocommerce_enable_giftcard_process' ) == 'no' ){
			array_splice($total_rows, 1, 0, $newRow);	
		} else {
			array_splice($total_rows, 2, 0, $newRow);
		}
	}

	return $total_rows;
}
add_filter( 'woocommerce_get_order_item_totals', 'rpgc_add_order_giftcard', 10, 2);





/**
 * Updates the Gift Card and the order information when the order is processed
 *
 */
function rpgc_update_card( $order_id ) {
	global $woocommerce;

	$giftCard_id = WC()->session->giftcard_post;

	if ( $giftCard_id <> '' ) {
		$newBalance = wpr_get_giftcard_balance( $giftCard_id ) - WC()->session->giftcard_payment;

		// Check if the gift card ballance is 0 and if it is change the post status to zerobalance
		if( wpr_get_giftcard_balance( $giftCard_id ) == 0 )
			wpr_update_giftcard_status( $giftCard_id, 'zerobalance' );
		
		update_post_meta( $giftCard_id, 'rpgc_balance', $newBalance ); // Update balance of Giftcard
		update_post_meta( $order_id, 'rpgc_id', $giftCard_id );
		update_post_meta( $order_id, 'rpgc_payment', WC()->session->giftcard_payment );
		update_post_meta( $order_id, 'rpgc_balance', $newBalance );

		WC()->session->idForEmail = $order_id;
		unset( WC()->session->giftcard_payment, WC()->session->giftcard_post );
	}

	if ( isset ( WC()->session->giftcard_data ) ) {
		update_post_meta( $order_id, 'rpgc_data', WC()->session->giftcard_data );

		unset( WC()->session->giftcard_data );
	}

}
add_action( 'woocommerce_payment_complete', 'rpgc_update_card' );
add_action( 'woocommerce_thankyou_paypal', 'rpgc_update_card' );


