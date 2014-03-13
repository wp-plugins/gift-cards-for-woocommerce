<?php
/**
 * Giftcard Actions
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
 * Creates a random 15 digit giftcard number
 *
 */
function rpgc_create_number( $data , $postarr ) {
	if ( ( $data['post_type'] == 'rp_shop_giftcard' ) && ( ( $data['post_title'] == "" ) || ( $data['post_title'] == "Auto Draft" ) ) ) {

		$randomNumber = substr( number_format( time() * rand(), 0, '', '' ), 0, 15 );
		$data['post_title'] = $randomNumber;
		$data['post_name'] = $randomNumber;
	}

	return apply_filter('rpgc_create_number', $data);
}
add_filter( 'wp_insert_post_data' , 'rpgc_create_number' , '99', 2 );


