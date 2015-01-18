<?php 


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