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

	

function rpgc_extra_check( $product_type_options ) {

	$giftcard = array(
		'giftcard' => array(
			'id' => '_giftcard',
			'wrapper_class' => 'show_if_simple',
			'label' => __( 'Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
			'description' => __( 'Make product a gift card.', RPWCGC_CORE_TEXT_DOMAIN )
		),
	);

	// combine the two arrays
	$product_type_options = array_merge( $giftcard, $product_type_options );

	return $product_type_options;
}
add_filter( 'product_type_options', 'rpgc_extra_check' );


function rpgc_process_meta( $post_id, $post ) {
	global $wpdb, $woocommerce, $woocommerce_errors;

	$is_giftcard  = isset( $_POST['_giftcard'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_giftcard', $is_giftcard );

	if ( $is_giftcard == "yes" ) {
		update_post_meta( $post_id, '_virtual', $is_giftcard );
	}

}
add_action( 'save_post', 'rpgc_process_meta', 10, 2 );


function rpgc_cart_fields( ) {
	global $post;

	$is_giftcard = get_post_meta( $post->ID, '_giftcard', true );

	if ( $is_giftcard == "yes" ) {

		do_action( 'rpgc_before_all_giftcard_fields' );
?>

		<div>
			<div><?php _e('All fields below are optional', RPWCGC_CORE_TEXT_DOMAIN ); ?></div>
			<?php  do_action( 'rpgc_before_product_fields' ); ?>
			<input type="hidden" id="rpgc_description" name="rpgc_description" value="<?php _e('Generated from the website.', RPWCGC_CORE_TEXT_DOMAIN ); ?>" />
			<input name="rpgc_to" id="rpgc_to" placeholder="<?php _e('To', RPWCGC_CORE_TEXT_DOMAIN ); ?>" class="input-text" style="margin-bottom:5px;">
			<input type="email" name="rpgc_to_email" id="rpgc_to_email" placeholder="<?php _e('Send To', RPWCGC_CORE_TEXT_DOMAIN ); ?>" class="input-text" style="margin-bottom:5px;">
			<textarea class="input-text" id="rpgc_note" name="rpgc_note" placeholder="<?php _e('Enter your note here.', RPWCGC_CORE_TEXT_DOMAIN ); ?>" rows="2" style="margin-bottom:5px;"></textarea>
			<?php  do_action( 'rpgc_after_product_fields' ); ?>
		</div>
		<?php

		echo '
	          <script>
	          	jQuery( document ).ready( function( $ ){ $( ".quantity" ).hide( ); });
	          </script>
	    ';
	}
}
add_action( 'woocommerce_before_add_to_cart_button', 'rpgc_cart_fields' );