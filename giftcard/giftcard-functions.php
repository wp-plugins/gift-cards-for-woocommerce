<?php
/**
 * Giftcard Functions
 *
 * @package     Woocommerce - Gift Cards
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

		$myNumber = rpgc_generate_number( );		
		
		$data['post_title'] = $myNumber;
		$data['post_name'] = $myNumber;
	}

	return apply_filters('rpgc_create_number', $data);
}
add_filter( 'wp_insert_post_data' , 'rpgc_create_number' , '99', 2 );


function rpgc_generate_number( ) {
	$randomNumber = substr( number_format( time() * rand(), 0, '', '' ), 0, 15 );

	return apply_filters('rpgc_generate_number', $randomNumber);
}
