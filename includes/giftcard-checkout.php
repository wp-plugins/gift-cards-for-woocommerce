<?php
/**
 * Gift Card Checkout Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Adds the Gift Card form to the checkout page so that customers can enter the gift card information
function rpgc_cart_form() {
	
	if( get_option( 'woocommerce_enable_giftcard_cartpage' ) == "yes" ) {
		do_action( 'wpr_before_cart_form' );
		
		?>
		
		<div class="giftcard" style="float: left;">
			<label type="text" for="giftcard_code" style="display: none;"><?php _e( 'Giftcard', 'rpgiftcards' ); ?>:</label>
			<input type="text" name="giftcard_code" class="input-text" id="giftcard_code" value="" placeholder="<?php _e( 'Gift Card', 'rpgiftcards' ); ?>" />
			<input type="submit" class="button" name="update_cart" value="<?php _e( 'Apply Gift card', 'rpgiftcards' ); ?>" />
		</div>
<?php
		do_action( 'wpr_after_cart_form' );
	}

}
add_action( 'woocommerce_cart_actions', 'rpgc_cart_form' );


if ( ! function_exists( 'rpgc_checkout_form' ) ) {

	/**
	 * Output the Giftcard form for the checkout.
	 * @access public
	 * @subpackage Checkout
	 * @return void
	 */
	function rpgc_checkout_form() {

		if( get_option( 'woocommerce_enable_giftcard_checkoutpage' ) == 'yes' ){

			$info_message = apply_filters( 'woocommerce_checkout_giftcaard_message', __( 'Have a giftcard?', 'rpgiftcards' ) );
			do_action( 'wpr_before_checkout_form' );

			?>
			<p class="woocommerce-info"><?php echo $info_message; ?> <a href="#" class="showgiftcard"><?php _e( 'Click here to enter your giftcard', 'rpgiftcards' ); ?></a></p>

			<form class="checkout_giftcard checkout_coupon" method="post" style="display:none">
				<p class="form-row form-row-first"><input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift card', 'rpgiftcards' ); ?>" id="giftcard_code" value="" /></p>
				<p class="form-row form-row-last"><input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Gift card', 'rpgiftcards' ); ?>" /></p>
				<div class="clear"></div>
			</form>

			<?php do_action( 'wpr_after_checkout_form' ); ?>

		<?php
		}
	}
	add_action( 'woocommerce_before_checkout_form', 'rpgc_checkout_form', 10 );
}


//  Display the current gift card information on the cart
//  *Plan on adding ability to edit the infomration in the future
function wpr_display_giftcard_in_cart() {
	$cart = WC()->session->cart;
	$gift = 0;
	$card = array();

	foreach( $cart as $key => $product ) {

		if( WPR_Giftcard::wpr_is_giftcard($product['product_id'] ) )
				$card[] = $product;

	}

	if( ! empty( $card ) ) {
		echo '<h6>Gift Cards In Cart</h6>';
		echo '<table width="100%" class="shop_table cart">';
		echo '<thead>';
		echo '<tr><td>' . __( 'Gift Card', 'rpgiftcards' ) . '</td><td>' . __( 'Name', 'rpgiftcards' ) . '</td><td>' . __( 'Email', 'rpgiftcards' ) . '</td><td>' . __( 'Price', 'rpgiftcards' ) . '</td><td>' . __( 'Note', 'rpgiftcards' ) . '</td></tr>';
		echo '</thead>';
		foreach( $card as $key => $information ) {
			
			if( WPR_Giftcard::wpr_is_giftcard($information['product_id'] ) ){
				$gift += 1;

				echo '<tr style="font-size: 0.8em">';
					echo '<td>Gift Card ' . $gift . '</td>';
					echo '<td>' . $information["variation"]["To"] . '</td>';
					echo '<td>' . $information["variation"]["To Email"] . '</td>';
					echo '<td>' . woocommerce_price( $information["line_total"] ) . '</td>';
					echo '<td>' . $information["variation"]["Note"] . '</td>';
				echo '</tr>';
			}
		}
		echo '</table>';
	}
}
add_action( 'woocommerce_after_cart_table', 'wpr_display_giftcard_in_cart' );



function woocommerce_apply_giftcard($giftcard_code) {
	global $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftcard_number = sanitize_text_field( $_POST['giftcard_code'] );

		if ( ! isset( WC()->session->giftcard_post ) ) {
			$giftcard_id = WPR_Giftcard::wpr_get_giftcard_by_code( $giftcard_number );

			if ( $giftcard_id ) {
				$current_date = date("Y-m-d");
				$cardExperation = wpr_get_giftcard_expiration( $giftcard_id );

				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {
					if( wpr_get_giftcard_balance( $giftcard_id ) > 0 ) {
						WC()->session->giftcard_post = $giftcard_id;

						wc_add_notice(  __( 'Gift card applied successfully.', 'rpgiftcards' ), 'success' );

					} else {
						wc_add_notice( __( 'Gift Card does not have a balance!', 'rpgiftcards' ), 'error' );
					}
				} else {
					wc_add_notice( __( 'Gift Card has expired!', 'rpgiftcards' ), 'error' ); // Giftcard Entered has expired
				}
			} else {
				wc_add_notice( __( 'Gift Card does not exist!', 'rpgiftcards' ), 'error' ); // Giftcard Entered does not exist
			}
		} else {		
			wc_add_notice( __( 'Gift Card already in the cart!', 'rpgiftcards' ), 'error' );  //  You already have a gift card in the cart
		}

		wc_print_notices();

		die();
	}
}
add_action( 'wp_ajax_woocommerce_apply_giftcard', 'woocommerce_apply_giftcard' );
add_action( 'wp_ajax_nopriv_woocommerce_apply_giftcard', 'woocommerce_apply_giftcard' );


function apply_cart_giftcard( ) {
	if ( isset( $_POST['giftcard_code'] ) ) 
		woocommerce_apply_giftcard( $_POST['giftcard_code'] );
	
	WC()->cart->calculate_totals();

}
add_action ( 'woocommerce_before_cart', 'apply_cart_giftcard' );



/**
 * Function to add the giftcard data to the cart display on both the card page and the checkout page WC()->session->giftcard_balance
 *
 */
function rpgc_order_giftcard( ) {
	global $woocommerce;

	if ( isset( $_GET['remove_giftcards'] ) ) {
		if ( 1 == $_GET['remove_giftcards'] ) {
			unset( WC()->session->giftcard_post );
			WC()->cart->calculate_totals();
		}
	}

	if ( isset( WC()->session->giftcard_post ) ) {
		if ( WC()->session->giftcard_post ){

			$giftcard = new WPR_Giftcard();
			$price = $giftcard->wpr_get_payment_amount();

			if ( is_cart() ) {
				$gotoPage = WC()->cart->get_cart_url();
			} else {
				$gotoPage = WC()->cart->get_checkout_url();	
			}

			?>
			<tr class="giftcard">
				<th><?php _e( 'Gift Card Payment', 'rpgiftcards' ); ?> </th>
				<td style="font-size:0.85em;"><?php echo woocommerce_price( $price ); ?> <a href="<?php echo add_query_arg( 'remove_giftcards', '1', $gotoPage ) ?>">[<?php _e( 'Remove Gift Card', 'rpgiftcards' ); ?>]</a></td>
			</tr>
			<?php

		}
	}
}
add_action( 'woocommerce_review_order_before_order_total', 'rpgc_order_giftcard' );
add_action( 'woocommerce_cart_totals_before_order_total', 'rpgc_order_giftcard' );



function wpr_add_giftcard_discount( $cart ) {

	$giftcard = new WPR_Giftcard();
	$giftcardDiscount = $giftcard->wpr_get_payment_amount();

	// Alter the cart discount total
	wc()->cart->discount_cart = (float) $giftcardDiscount;

}
//add_action('woocommerce_calculate_totals', 'wpr_add_giftcard_discount');


/**
 * Updates the Gift Card and the order information when the order is processed
 *
 */
function rpgc_update_card( $order_id ) {
	global $woocommerce;

	$giftCard_id = WC()->session->giftcard_post;
	if ( $giftCard_id != '' ) {
		//Decrease Ballance of card
		$giftcard = new WPR_Giftcard();

		$payment = $giftcard->wpr_get_payment_amount();

		$giftcard->wpr_decrease_balance( $giftCard_id );

		$giftCard_IDs = get_post_meta ( $giftCard_id, 'wpr_existingOrders_id', true );
		$giftCard_IDs[] = $order_id;

		$newBalance = wpr_get_giftcard_balance( $giftCard_id );

		update_post_meta( $giftCard_id, 'rpgc_balance', $newBalance ); // Update balance of Giftcard
		update_post_meta( $giftCard_id, 'wpr_existingOrders_id', $giftCard_IDs ); // Saves order id to gifctard post
		update_post_meta( $order_id, 'rpgc_id', $giftCard_id );
		update_post_meta( $order_id, 'rpgc_payment', $payment );
		update_post_meta( $order_id, 'rpgc_balance', $newBalance );

		WC()->session->idForEmail = $order_id;
		unset( WC()->session->giftcard_payment, WC()->session->giftcard_post );
	}

	if ( isset ( WC()->session->giftcard_data ) ) {
		update_post_meta( $order_id, 'rpgc_data', WC()->session->giftcard_data );

		unset( WC()->session->giftcard_data );
	}

}
add_action( 'woocommerce_checkout_order_processed', 'rpgc_update_card' );



/**
 * Displays the giftcard data on the order thank you page
 *
 */
function rpgc_display_giftcard( $order ) {

	$theIDNum =  get_post_meta( $order->id, 'rpgc_id', true );
	$theBalance = get_post_meta( $order->id, 'rpgc_balance', true );

	if( isset( $theIDNum ) ) {
		if ( $theIDNum <> '' ) {
		?>
			<h4><?php _e( 'Gift Card Balance After Order:', 'rpgiftcards' ); ?><?php echo ' ' . woocommerce_price( $theBalance ); ?> <?php do_action('wpr_after_remaining_balance', $theIDNum, $theBalance ); ?></h4>

			<?php
			
		}
	}

	$theGiftCardData = get_post_meta( $order->id, 'rpgc_data', true );
	if( isset( $theGiftCardData ) ) {
		if ( $theGiftCardData <> '' ) {
	?>
			<h4><?php _e( 'Gift Card Information:', 'rpgiftcards' ); ?></h4>
			<?php
			$i = 1;

			foreach ( $theGiftCardData as $giftcard ) {

				if ( $i % 2 ) echo '<div style="margin-bottom: 10px;">';
				echo '<div style="float: left; width: 45%; margin-right: 2%;>';
				echo '<h6><strong> ' . __('Giftcard',  'rpgiftcards' ) . ' ' . $i . '</strong></h6>';
				echo '<ul style="font-size: 0.85em; list-style: none outside none;">';
				if ( $giftcard[rpgc_product_num] ) 	echo '<li>' . __('Card', 'rpgiftcards') . ': ' . get_the_title( $giftcard[rpgc_product_num] ) . '</li>';
				if ( $giftcard[rpgc_to] ) 			echo '<li>' . __('To',  'rpgiftcards' ) . ': ' . $giftcard[rpgc_to] . '</li>';
				if ( $giftcard[rpgc_to_email] ) 	echo '<li>' . __('Send To',  'rpgiftcards' ) . ': ' . $giftcard[rpgc_to_email] . '</li>';
				if ( $giftcard[rpgc_balance] ) 		echo '<li>' . __('Balance',  'rpgiftcards' ) . ': ' . woocommerce_price( $giftcard[rpgc_balance] ) . '</li>';
				if ( $giftcard[rpgc_note] ) 		echo '<li>' . __('Note',  'rpgiftcards' ) . ': ' . $giftcard[rpgc_note] . '</li>';
				if ( $giftcard[rpgc_quantity] ) 	echo '<li>' . __('Quantity',  'rpgiftcards' ) . ': ' . $giftcard[rpgc_quantity] . '</li>';
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


// NEED TO FIGURE THIS PART OUT
function rpgc_add_order_giftcard( $total_rows, $order ) {

	$return = array();

	$order_id = $order->id;

	$giftCardPayment = get_post_meta( $order_id, 'rpgc_payment', true);

	if ( $giftCardPayment <> 0 ) {

		$giftValue = get_post_meta( $order->id, 'rpgc_payment', true);
		$discount = get_post_meta( $order->id, '_cart_discount', true);

		if( $discount == $giftValue ) {
			unset( $total_rows['discount'] );
		} elseif ( $discount > $giftValue ) {
			$total_rows['discount']['value'] = $discount - $giftValue;
		}

		$newRow['rpgc_data'] = array(
			'label' => __( 'Gift Card Payment:', 'rpgiftcards' ),
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
//add_filter( 'woocommerce_get_order_item_totals', 'rpgc_add_order_giftcard', 10, 2);


function wpr_giftcard_in_order( $order_id ) {

	$giftCardPayment = get_post_meta( $order_id, 'rpgc_payment', true);

	if ( $giftCardPayment ) { ?>
	
		<tr>
			<td class="label"><?php _e( 'Gift Card Payment', 'rpgiftcards' ); ?> <span class="tips" data-tip="<?php _e( 'This is the amount used by gift cards.', 'rpgiftcards' ); ?>">[?]</span>:</td>
			<td class="total"><?php echo woocommerce_price($giftCardPayment); ?></td>
			<td width="1%"></td>
		</tr>
	
	<?php  }

}
add_action( 'woocommerce_admin_order_totals_after_tax', 'wpr_giftcard_in_order', 10, 1 );

function test( $discount, $test2 ){
	var_dump( $test, $test2 );

}
//add_filter( 'woocommerce_order_discount_to_display', 'test', 10, 2 );




