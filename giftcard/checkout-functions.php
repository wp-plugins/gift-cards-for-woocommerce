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


if ( ! function_exists( 'rpgc_checkout_form' ) ) {

	/**
	 * Output the Giftcard form for the checkout.
	 * @access public
	 * @subpackage Checkout
	 * @return void
	 */
	function rpgc_checkout_form() {
		global $woocommerce;

		$info_message = apply_filters( 'woocommerce_checkout_giftcaard_message', __( 'Have a giftcard?', RPWCGC_CORE_TEXT_DOMAIN ) );
		?>

		<p class="woocommerce-info"><?php echo $info_message; ?> <a href="#" class="showgiftcard"><?php _e( 'Click here to enter your giftcard', RPWCGC_CORE_TEXT_DOMAIN ); ?></a></p>

		<form class="checkout_giftcard" method="post" style="display:none">

			<p class="form-row form-row-first">
				<input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift Card', RPWCGC_CORE_TEXT_DOMAIN ); ?>" id="giftcard_code" value="" />
			</p>

			<p class="form-row form-row-last">
				<input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Giftcard', RPWCGC_CORE_TEXT_DOMAIN ); ?>" />
			</p>

			<div class="clear"></div>
		</form>

		<script>
			jQuery(document).ready(function($) {
				$('a.showgiftcard').click(function(){
					$('.checkout_giftcard').slideToggle();
					$('#giftcard_code').focus();
						return false;
					});

					/* AJAX Coupon Form Submission */
					$('form.checkout_giftcard').submit( function() {
						var $form = $(this);

						if ( $form.is('.processing') ) return false;

						$form.addClass('processing').block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

						var data = {
							action: 			'woocommerce_apply_giftcard',
							security: 			'apply-giftcard',
							giftcard_code:		$form.find('input[name=giftcard_code]').val()
						};

						$.ajax({
							type: 		'POST',
							url: 		woocommerce_params.ajax_url,
							data:		data,
							success: 	function( code ) {
								$('.woocommerce-error, .woocommerce-message').remove();
								$form.removeClass('processing').unblock();

								if ( code ) {
									$form.before( code );
									$form.slideUp();

									$('body').trigger('update_checkout');
								}
							},
							dataType: 	"html"
						});
						return false;
					});

			});

		</script>

		<?php
	}
}
add_action( 'woocommerce_before_checkout_form', 'rpgc_checkout_form', 10 );



function rpgc_add_card_data( $cart_item_key, $product_id, $quantity ) {
	global $woocommerce, $post;

	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( $is_giftcard == "yes" ) {

		$giftcard_data = array(
			'To'    	=> 'NA',
			'To Email'  => 'NA',
			'Note'   	=> 'NA',
		);

		if ( isset( $_POST['rpgc_to'] ) )
			$giftcard_data['To'] = woocommerce_clean( $_POST['rpgc_to'] );

		if ( isset( $_POST['rpgc_to_email'] ) )
			$giftcard_data['To Email'] = woocommerce_clean( $_POST['rpgc_to_email'] );

		if ( isset( $_POST['rpgc_note'] ) )
			$giftcard_data['Note'] = woocommerce_clean( $_POST['rpgc_note'] );

		$giftcard_data = apply_filters( 'rpgc_giftcard_data', $giftcard_data, $_POST );

		$woocommerce->cart->cart_contents[$cart_item_key]["variation"] = $giftcard_data;
		return $woocommerce;
	}
	
}
add_action( 'woocommerce_add_to_cart', 'rpgc_add_card_data', 10, 3 );

function rpgc_add_giftcard_to_paypal( $paypal_args ) {
	global $woocommerce;

	$giftCardPayment = $woocommerce->session->giftcard_payment;

	if ( isset( $paypal_args['discount_amount_cart'] ) ) {
		$paypal_args['discount_amount_cart'] = $paypal_args['discount_amount_cart'] + $giftCardPayment;
	} else { 
		$paypal_args['discount_amount_cart'] = $giftCardPayment;
	}

	return $paypal_args;
}

add_filter( 'woocommerce_paypal_args', 'rpgc_add_giftcard_to_paypal');


/**
 * AJAX apply coupon on checkout page
 * @access public
 * @return void
 */
function woocommerce_ajax_apply_giftcard() {
	global $woocommerce, $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftCardNumber = sanitize_text_field( $_POST['giftcard_code'] );

		$woocommerce->cart->total = $woocommerce->session->giftcard_payment + $woocommerce->cart->total;

		unset( $woocommerce->session->giftcard_payment, $woocommerce->session->giftcard_id, $woocommerce->session->giftcard_post, $woocommerce->session->giftcard_balance );

		// Check for Giftcard
		$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_title = '%s'
		", $giftCardNumber ) );

		$orderTotal = (float) $woocommerce->cart->total;

		$current_date = date("Y-m-d");
		$cardExperation = get_post_meta( $giftcard_found, 'rpgc_expiry_date', true );

		if ( $giftcard_found ) {
			// Valid Gift Card Entered		
			if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {

				$oldBalance = get_post_meta( $giftcard_found, 'rpgc_balance' );

				if ( is_string( $oldBalance[0] ) )  // Determin if the Value from $oldBalance is a String and convert it
					$oldGiftcardValue = (float) $oldBalance[0];

				if ( is_string( $orderTotal ) )   // Determin if the Value from $orderTotal is a String and convert it
					$orderTotalCost = (float) $orderTotal;

				$woocommerce->session->giftcard_post = $giftcard_found;
				$woocommerce->session->giftcard_id = $giftCardNumber;


				if ( $oldGiftcardValue == 0 ) {
					// Giftcard Entered does not have a balance
					wc_add_notice( __( 'Gift Card does not have a balance!', RPWCGC_CORE_TEXT_DOMAIN ), 'error' );

				} elseif ( $oldGiftcardValue >= $orderTotal ) {
					//  Giftcard Balance is more than the order total.
					//  Subtract the order from the card
					$woocommerce->session->giftcard_payment = $orderTotal;

					if( get_option( 'woocommerce_enable_giftcard_process' ) == 'no' )
						$woocommerce->session->giftcard_payment = $woocommerce->session->giftcard_payment - $woocommerce->cart->shipping_total;


					$woocommerce->session->giftcard_balance = $oldGiftcardValue - $orderTotal;
					$msg = __( 'Gift card applied successfully.', RPWCGC_CORE_TEXT_DOMAIN );
					wc_add_notice(  __( 'Gift card applied successfully.', RPWCGC_CORE_TEXT_DOMAIN ), 'success' );

				} elseif ( $oldGiftcardValue < $orderTotal ) {
					//  Giftcard Balance is less than the order total.
					//  Subtract the giftcard from the order total
					
					$woocommerce->session->giftcard_payment = $oldGiftcardValue;
					$woocommerce->session->giftcard_balance = 0;
					
					if( get_option( 'woocommerce_enable_giftcard_process' ) == 'no' ) {
						$cartSubtotal = $orderTotal - $woocommerce->cart->shipping_total;
						if ( $oldGiftcardValue > $cartSubtotal ) {
							$woocommerce->session->giftcard_balance = $oldGiftcardValue - $cartSubtotal;
							$woocommerce->session->giftcard_payment = $cartSubtotal;
						}
					}

					wc_add_notice(  __( 'Gift card applied successfully.', RPWCGC_CORE_TEXT_DOMAIN ), 'success' );
				}
			} else {
				// Giftcard Entered has expired
				wc_add_notice( __( 'Gift Card has expired!', RPWCGC_CORE_TEXT_DOMAIN ), 'error' );


			}
		} else {
			// Giftcard Entered does not exist
			wc_add_notice( __( 'Gift Card does not exist!', RPWCGC_CORE_TEXT_DOMAIN ), 'error' );
		}
	}

	wc_print_notices();

	die();
}
add_action( 'wp_ajax_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );
add_action( 'wp_ajax_nopriv_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );

/**
 * Function to decrease the cart amount by the amount in the giftcard
 *
 */
function subtract_giftcard( $wc_cart ) {
	global $woocommerce;

	$wc_cart->cart_contents_total = $wc_cart->cart_contents_total - $woocommerce->session->giftcard_payment;
}
add_action( 'woocommerce_calculate_totals', 'subtract_giftcard' );

/**
 * Function to add the giftcard data to the cart display
 *
 */
function rpgc_order_giftcard( ) {
	global $woocommerce;

	if ( isset( $_GET['remove_giftcards'] ) ) {
		$type = $_GET['remove_giftcards'];

		if ( 1 == $type )
			unset( $woocommerce->session->giftcard_payment, $woocommerce->session->giftcard_id, $woocommerce->session->giftcard_post, $woocommerce->session->giftcard_balance );
	}

	if ( isset( $woocommerce->session->giftcard_payment ) ) {
		if ( $woocommerce->session->giftcard_payment > 0 ){

			$currency_symbol = get_woocommerce_currency_symbol();
			$price = $woocommerce->session->giftcard_payment;
			?>

			<tr class="giftcard">
				<th><?php _e( 'Gift Card Payment', RPWCGC_CORE_TEXT_DOMAIN ); ?> </th>
				<td style="font-size:0.85em;"><?php echo woocommerce_price( $price ); ?> <a alt="<?php echo $woocommerce->session->giftcard_id; ?>" href="<?php echo add_query_arg( 'remove_giftcards', '1', $woocommerce->cart->get_checkout_url() ) ?>">[<?php _e( 'Remove Gift Card', RPWCGC_CORE_TEXT_DOMAIN ); ?>]</a></td>
			</tr>

			<?php
		}
	}
}
add_action( 'woocommerce_review_order_before_order_total', 'rpgc_order_giftcard' );


/**
 * Function to add the giftcard data to the cart display
 *
 */
function rpgc_cart_giftcard( $order ) {
	global $woocommerce;

	$thePayment = get_post_meta( $order->id, 'rpgc_payment', true );
	
	if ( $thePayment  > 0 ){
		?>

		<tr class="giftcard">
			<th><?php _e( 'Gift Card Payment', RPWCGC_CORE_TEXT_DOMAIN ); ?> </th>
			<td style="font-size:0.85em;"> - <?php echo woocommerce_price( $thePayment ); ?> </td>
		</tr>

	<?php
	}
}
add_action( 'woocommerce_order_items_table', 'rpgc_cart_giftcard' );

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
			<h4><?php _e( 'Remaining Gift Card Balance:', RPWCGC_CORE_TEXT_DOMAIN ); ?><?php echo ' ' . woocommerce_price( $theBalance ); ?> </h4>
			<?php
		}
	}

	$theGiftCardData = get_post_meta( $order->id, 'rpgc_data', true );
	if( isset( $theGiftCardData ) ) {
		if ( $theGiftCardData <> '' ) {
	?>
			<h4><?php _e( 'Gift Card Information:', RPWCGC_CORE_TEXT_DOMAIN ); ?></h4>
			<?php
			$i = 1;

			foreach ( $theGiftCardData as $giftcard ) {

				if ( $i % 2 ) echo '<div style="margin-bottom: 10px;">';
				echo '<div style="float: left; width: 45%; margin-right: 2%;>';
				echo '<h6><strong> ' . __('Giftcard',  RPWCGC_CORE_TEXT_DOMAIN ) . ' ' . $i . '</strong></h6>';
				echo '<ul style="font-size: 0.85em; list-style: none outside none;">';
				if ( $giftcard[rpgc_product_num] ) echo '<li>' . __('Card', RPWCGC_CORE_TEXT_DOMAIN) . ': ' . get_the_title( $giftcard[rpgc_product_num] ) . '</li>';
				if ( $giftcard[rpgc_to] ) echo  '<li>' . __('To',  RPWCGC_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_to] . '</li>';
				if ( $giftcard[rpgc_to_email] ) echo  '<li>' . __('Send To',  RPWCGC_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_to_email] . '</li>';
				if ( $giftcard[rpgc_balance] ) echo  '<li>' . __('Balance',  RPWCGC_CORE_TEXT_DOMAIN ) . ': ' . woocommerce_price( $giftcard[rpgc_balance] ) . '</li>';
				if ( $giftcard[rpgc_note] ) echo  '<li>' . __('Note',  RPWCGC_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_note] . '</li>';
				if ( $giftcard[rpgc_quantity] ) echo  '<li>' . __('Quantity',  RPWCGC_CORE_TEXT_DOMAIN ) . ': ' . $giftcard[rpgc_quantity] . '</li>';
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