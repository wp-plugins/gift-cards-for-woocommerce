<?php
/**
 * Gift Card Short Codes
 *
 * @package     Woocommerce
 * @subpackage  Giftcards
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function wpr_check_giftcard( $atts ) {
	global $wpdb, $woocommerce;


	if ( isset( $_POST['giftcard_code'] ) )
		$giftCardNumber = sanitize_text_field( $_POST['giftcard_code'] );

	$return = '';
	$return .= '<form class="check_giftcard_balance" method="post">';

	$return .= '<p class="form-row form-row-first">';
		$return .= '<input type="text" name="giftcard_code" class="input-text" placeholder="' . __( 'Gift card', 'rpgiftcards' ) . '" id="giftcard_code" value="" />';
	$return .= '</p>';

	$return .= '<p class="form-row form-row-last">';
		$return .= '<input type="submit" class="button" name="check_giftcard" value="' . __( 'Check Balance', 'rpgiftcards' ) . '" />';
	$return .= '</p>';

	$return .= '<div class="clear"></div>';
	$return .= '</form>';
	
	$return .= '<div id="theBalance"></div>';


	if ( isset( $_POST['giftcard_code'] ) ) {

		// Check for Giftcard
		$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_title = '%s'
		", $giftCardNumber ) );

		if ( $giftcard_found ) {
			$current_date = date("Y-m-d");
			$cardExperation = get_post_meta( $giftcard_found, 'rpgc_expiry_date', true );

			// Valid Gift Card Entered
			if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {

				$oldBalance = get_post_meta( $giftcard_found, 'rpgc_balance', true );
				$GiftcardBalance = (float) $oldBalance;

				$return .= '<h3>' . __('Remaining Balance', 'rpgiftcards' ) . ': ' . woocommerce_price( $GiftcardBalance ) . '</h3>';
			} else {
				$return .= '<h3>' . __('Gift Card Has Expired', 'rpgiftcards' ) . '</h3>';
			}
		} else {
			$return .= '<h3>' . __( 'Gift Card Does Not Exist', 'rpgiftcards' ) . '</h3>';

		}

		
	}

	return apply_filters( 'wpr_check_giftcard', $return) ;

}
add_shortcode( 'giftcardbalance', 'wpr_check_giftcard' );


function wpr_decrease_giftcard( $atts ) {
	global $wpdb, $woocommerce;

	if( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' )) {
		if ( isset( $_POST['giftcard_code'] ) )
			$giftCardNumber = sanitize_text_field( $_POST['giftcard_code'] );

		if ( isset( $_POST['giftcard_debt'] ) )
			$giftCardDebt = sanitize_text_field( $_POST['giftcard_debt'] );	

		$return = '';
		$return .= '<form class="check_giftcard_balance" method="post">';

		$return .= '<p class="form-row form-row-first">';
			$return .= '<input type="text" name="giftcard_code" class="input-text" placeholder="' . __( 'Gift card', 'rpgiftcards' ) . '" id="giftcard_code" value="" />';
		$return .= '</p>';

		$return .= '<p class="form-row form-row-first">';
			$return .= '<input type="text" name="giftcard_debt" class="input-text" placeholder="' . __( 'Amount Used', 'rpgiftcards' ) . '" id="giftcard_debt" value="" />';
		$return .= '</p>';

		$return .= '<p class="form-row form-row-last">';
			$return .= '<input type="submit" class="button" name="check_giftcard" value="' . __( 'Submit', 'rpgiftcards' ) . '" />';
		$return .= '</p>';

		$return .= '<div class="clear"></div>';
		$return .= '</form>';
		
		$return .= '<div id="theBalance"></div>';


		if ( isset( $_POST['giftcard_debt'] ) ) {

			$giftcard_found = wpr_get_giftcard_by_code( $giftCardNumber );

			if ( $giftcard_found ) {
				$current_date = date("Y-m-d");


				$giftcard = wpr_get_giftcard_info( $giftcard_found );
				$cardExperation = $giftcard['expiry_date'];

				// Valid Gift Card Entered
				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {

					$oldBalance = $giftcard['balance'];
					$GiftcardBalance = (float) $oldBalance;

					if ( $GiftcardBalance >= $giftCardDebt ) {
						$giftcard['balance'] = (float) $GiftcardBalance - (float) $giftCardDebt;
						$giftcardRemaining = 0;
					} else {
						$giftcard['balance'] = 0;
						$giftcardRemaining = (float) $giftCardDebt - (float) $GiftcardBalance;
						$return .= '<h3>' . __('Amount Remaining to Pay', 'rpgiftcards' ) . ': ' . woocommerce_price( $giftcardRemaining ) . '</h3>';
					}

					update_post_meta( $giftcard_found, '_wpr_giftcard', $giftcard );

					
					$return .= '<h3>' . __('Remaining Balance on Card', 'rpgiftcards' ) . ': ' . woocommerce_price( $giftcard['balance'] ) . '</h3>';

				} else {
					$return .= '<h3>' . __('Gift Card Has Expired', 'rpgiftcards' ) . '</h3>';
				}
			} else {
				$return .= '<h3>' . __( 'Gift Card Does Not Exist', 'rpgiftcards' ) . '</h3>';

			}
		}

		return apply_filters( 'wpr_check_giftcard', $return) ;
	}
}
add_shortcode( 'giftcarddebt', 'wpr_decrease_giftcard' );
