<?php
/**
 * Gift Card Pull Meta Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Updates a giftcard's status from one status to another.
 *
 * @since 1.0
 * @param int $code_id Giftcard ID (default: 0)
 * @param string $new_status New status (default: active)
 * @return bool
 */
function wpr_update_giftcard_status( $code_id = 0, $new_status = 'active' ) {
	$giftcard = wpr_get_giftcard( $code_id );

	if ( $giftcard ) {
		do_action( 'wpr_pre_update_giftcard_status', $code_id, $new_status, $giftcard->post_status );

		wp_update_post( array( 'ID' => $code_id, 'post_status' => $new_status ) );

		do_action( 'wpr_post_update_giftcard_status', $code_id, $new_status, $giftcard->post_status );

		return true;
	}

	return false;
}

/**
 * Retrieve the giftcard number
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return string $expiration Giftcard expiration
 */
function wpr_get_giftcard_info( $code_id = null ) {
	$giftcard = get_post_meta( $code_id, '_wpr_giftcard', true );

	return apply_filters( 'wpr_get_giftcard_info', $giftcard, $code_id );
}

/**
 * Retrieve the giftcard number
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return string $expiration Giftcard expiration
 */
function wpr_set_giftcard_info( $code_id = null, $giftInfo ) {

	update_post_meta( $code_id, '_wpr_giftcard', $giftInfo );
}

/**
 * Get Giftcard
 *
 * Retrieves a complete giftcard code by giftcard ID.
 *
 * @since 1.0
 * @param string $giftcard_id Giftcard ID
 * @return array
 */
function wpr_get_giftcard( $giftcard_id ) {
	$giftcard = get_post( $giftcard_id );

	if ( get_post_type( $giftcard_id ) != 'rp_shop_giftcard' ) {
		return false;
	}

	return $giftcard;
}

/**
 * Retrieve the giftcard ID from the gift card number
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return string $expiration Giftcard expiration
 */
function wpr_get_giftcard_by_code( $value = '' ) {
	global $wpdb;

	// Check for Giftcard
	$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
		SELECT $wpdb->posts.ID
		FROM $wpdb->posts
		WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_title = '%s'
	", $value ) );

	return $giftcard_found;

}

/**
 * Retrieve the giftcard number
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return string $expiration Giftcard expiration
 */
function wpr_get_giftcard_number( $code_id = null ) {
	$giftcardNumber = get_the_title( $code_id );

	return apply_filters( 'wpr_get_giftcard_number', $giftcardNumber, $code_id );
}

/**
 * Retrieve the giftcard to name
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard To Name
 */
function wpr_get_giftcard_to( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return apply_filters( 'wpr_get_giftcard_to', $giftcard['to'], $code_id );
}

/**
 * Retrieve the giftcard to email
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard To Email
 */
function wpr_get_giftcard_to_email( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return apply_filters( 'wpr_get_giftcard_toEmail', $giftcard['toEmail'], $code_id );
}

/**
 * Retrieve the giftcard from
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard From Name
 */
function wpr_get_giftcard_from( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return apply_filters( 'wpr_get_giftcard_from', $giftcard['from'], $code_id );
}

/**
 * Retrieve the giftcard from email
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard From Email
 */
function wpr_get_giftcard_from_email( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return apply_filters( 'wpr_get_giftcard_fromEmail', $giftcard['fromEmail'], $code_id );
}

/**
 * Retrieve the giftcard note
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard Note
 */
function wpr_get_giftcard_note( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return apply_filters( 'wpr_get_giftcard_note', $giftcard['note'], $code_id );
}

/**
 * Retrieve the giftcard code expiration date
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return string $expiration Giftcard expiration
 */
function wpr_get_giftcard_expiration( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return apply_filters( 'wpr_get_giftcard_expiration', $giftcard['expiry_date'], $code_id );
}

/**
 * Retrieve the giftcard amount
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return int $amount Giftcard code amount
 * @return float
 */
function wpr_get_giftcard_amount( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return (float) apply_filters( 'wpr_get_giftcard_amount', $giftcard['amount'], $code_id );
}

/**
 * Retrieve the giftcard balance
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return int $amount Giftcard code balance
 * @return float
 */
function wpr_get_giftcard_balance( $code_id = null ) {
	$giftcard = wpr_get_giftcard_info( $code_id );

	return (float) apply_filters( 'wpr_get_giftcard_balance', $giftcard['balance'], $code_id );
}

/**
 * Set the giftcard balance
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return int $amount Giftcard code balance
 * @return float
 */
function wpr_set_giftcard_balance( $code_id = null, $balance ) {
	$giftcard = wpr_get_giftcard_info( $code_id );
	
	$giftcard['balance'] = (string) $balance;

	wpr_set_giftcard_info( $code_id, $giftcard );
}



// Order Gift Card Functions
// ******************************************************************************************

function wpr_get_order_card_number ( $order_id = null ) {
	$id = get_post_meta( $order_id, 'rpgc_id', true );
	$number = get_the_title( $id );

	return apply_filters( 'wpr_get_order_card_number', $number, $order_id );
}

function wpr_get_order_card_balance ( $order_id = null ) {
	$balance = get_post_meta( $order_id, 'rpgc_balance', true );

	return apply_filters( 'wpr_get_order_card_balance', $balance, $order_id );
}

function wpr_get_order_card_payment ( $order_id = null ) {
	$payment = get_post_meta( $order_id, 'rpgc_payment', true );

	return apply_filters( 'wpr_get_order_card_payment', $payment, $order_id );
}

function wpr_get_order_refund_status ( $order_id = null ) {
	$refunded = get_post_meta( $order_id, 'rpgc_refunded', true );

	return apply_filters( 'wpr_get_order_refund_status', $refunded, $order_id );
}