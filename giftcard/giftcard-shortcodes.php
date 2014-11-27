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
			$return .= '<input type="text" name="giftcard_code" class="input-text" placeholder="' . __( 'Gift card', WPR_CORE_TEXT_DOMAIN ) . '" id="giftcard_code" value="" />';
		$return .= '</p>';

		$return .= '<p class="form-row form-row-last">';
			$return .= '<input type="submit" class="button" name="check_giftcard" value="' . __( 'Check Balance', WPR_CORE_TEXT_DOMAIN ) . '" />';
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

				$return .= '<h3>' . __('Remaining Balance', WPR_CORE_TEXT_DOMAIN ) . ': ' . woocommerce_price( $GiftcardBalance ) . '</h3>';
			} else {
				$return .= '<h3>' . __('Gift Card Has Expired', WPR_CORE_TEXT_DOMAIN ) . '</h3>';
			}
		} else {
			$return .= '<h3>' . __( 'Gift Card Does Not Exist', WPR_CORE_TEXT_DOMAIN ) . '</h3>';

		}

		
	}

	return apply_filters( 'wpr_check_giftcard', $return) ;

}
add_shortcode( 'giftcardbalance', 'wpr_check_giftcard' );
