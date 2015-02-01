<?php



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
function wpr_get_giftcard_number( $code_id = null ) {
	$number = get_post_meta( $code_id, '_wpr_giftcard_number', true );

	return apply_filters( 'wpr_get_giftcard_number', $number, $code_id );
}

/**
 * Retrieve the giftcard to name
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard To Name
 */
function wpr_get_giftcard_to( $code_id = null ) {
	$to = get_post_meta( $code_id, 'rpgc_to', true );

	return apply_filters( 'wpr_get_giftcard_to', $to, $code_id );
}

/**
 * Retrieve the giftcard to email
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard To Email
 */
function wpr_get_giftcard_to_email( $code_id = null ) {
	$toEmail = get_post_meta( $code_id, 'rpgc_email_to', true );

	return apply_filters( 'wpr_get_giftcard_toEmail', $toEmail, $code_id );
}

/**
 * Retrieve the giftcard from
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard From Name
 */
function wpr_get_giftcard_from( $code_id = null ) {
	$from = get_post_meta( $code_id, 'rpgc_from', true );

	return apply_filters( 'wpr_get_giftcard_from', $from, $code_id );
}

/**
 * Retrieve the giftcard from email
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard From Email
 */
function wpr_get_giftcard_from_email( $code_id = null ) {
	$fromEmail = get_post_meta( $code_id, 'rpgc_email_from', true );

	return apply_filters( 'wpr_get_giftcard_fromEmail', $fromEmail, $code_id );
}

/**
 * Retrieve the giftcard note
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Giftcard Note
 */
function wpr_get_giftcard_note( $code_id = null ) {
	$note = get_post_meta( $code_id, 'rpgc_note', true );

	return apply_filters( 'wpr_get_giftcard_note', $note, $code_id );
}

/**
 * Retrieve the giftcard code expiration date
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return string $expiration Giftcard expiration
 */
function wpr_get_giftcard_expiration( $code_id = null ) {
	$expiration = get_post_meta( $code_id, 'rpgc_expiry_date', true );

	return apply_filters( 'wpr_get_giftcard_expiration', $expiration, $code_id );
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
	$amount = get_post_meta( $code_id, 'rpgc_amount', true );

	return (float) apply_filters( 'wpr_get_giftcard_amount', $amount, $code_id );
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
	$balance = get_post_meta( $code_id, 'rpgc_balance', true );

	return (float) apply_filters( 'wpr_get_giftcard_balance', $balance, $code_id );
}


/**
 * Sets the giftcard balance
 *
 * @since 1.4
 * @param int $code_id Giftcard ID
 * @return int $amount Giftcard code amounts
 * @return float
 */
function wpr_set_giftcard_balance( $code_id = null, $newBalance = null ) {
	update_post_meta( $code_id, 'rpgc_balance', $newBalance );
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
	



function wpr_is_giftcard ( $giftcard_id ) {
	$giftcard = get_post_meta( $giftcard_id, '_giftcard', true );

	if ( $giftcard != 'yes' ) {
		return false;
	}

	return true;
}