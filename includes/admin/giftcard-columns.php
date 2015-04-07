<?php
/**
 * Gift Card Admin Columns Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Admin Columns
		


function rpgc_add_columns( $columns ) {
	$new_columns = ( is_array( $columns ) ) ? $columns : array();
	unset( $new_columns['title'] );
	unset( $new_columns['date'] );
	unset( $new_columns['comments'] );

	//all of your columns will be added before the actions column on the Giftcard page

	$new_columns["title"]		= __( 'Giftcard Number', 'rpgiftcards' );
	$new_columns["amount"]		= __( 'Giftcard Amount', 'rpgiftcards' );
	$new_columns["balance"]		= __( 'Remaining Balance', 'rpgiftcards' );
	$new_columns["buyer"]		= __( 'Buyer', 'rpgiftcards' );
	$new_columns["recipient"]	= __( 'Recipient', 'rpgiftcards' );
	$new_columns["expiry_date"]	= __( 'Expiry date', 'rpgiftcards' );

	$new_columns['comments']	= $columns['comments'];
	$new_columns['date']		= __( 'Creation Date', 'rpgiftcards' );

	return  apply_filters( 'rpgc_giftcard_columns', $new_columns);
}
add_filter( 'manage_edit-rp_shop_giftcard_columns', 'rpgc_add_columns' );



/**
 * Define our custom columns shown in admin.
 * @param  string $column
 *
 */
function rpgc_custom_columns( $column ) {
	global $post, $woocommerce;

	switch ( $column ) {

		case "buyer" :
			echo '<div><strong>' . esc_html( get_post_meta( $post->ID, 'rpgc_from', true ) ) . '</strong><br />';
			echo '<span style="font-size: 0.9em">' . esc_html( get_post_meta( $post->ID, 'rpgc_email_from', true ) ) . '</div>';
			break;

		case "recipient" :
			echo '<div><strong>' . esc_html( get_post_meta( $post->ID, 'rpgc_to', true ) ) . '</strong><br />';
			echo '<span style="font-size: 0.9em">' . esc_html( get_post_meta( $post->ID, 'rpgc_email_to', true ) ) . '</span></div>';
		break;

		case "amount" :
			$price = get_post_meta( $post->ID, 'rpgc_amount', true );	
			echo woocommerce_price( $price );
		break;

		case "balance" :
			$price = get_post_meta( $post->ID, 'rpgc_balance', true );
			echo woocommerce_price( $price );
		break;

		case "expiry_date" :
			$expiry_date = get_post_meta( $post->ID, 'rpgc_expiry_date', true );

			if ( $expiry_date )
				echo esc_html( date_i18n( 'F j, Y', strtotime( $expiry_date ) ) );
			else
				echo '&ndash;';
		break;
	}
}
add_action( 'manage_rp_shop_giftcard_posts_custom_column', 'rpgc_custom_columns', 2 );



