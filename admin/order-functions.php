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
	
/**
 * Function to add the giftcard data to the order summary page
 *
 */
function rpgc_show_giftcard_in_order() {
	global $woocommerce, $post;

	$data = get_post_meta( $post->ID );

	if ( isset( $data['rpgc_id'] ) ) {
		if ( $data['rpgc_id'][0] <> '' ) {
		?>
			</div>
			<div class="totals_group giftcard_rows_group" style="background-color: #FCFCFC;">

			<h4><?php _e( 'Giftcard Information', RPWCGC_CORE_TEXT_DOMAIN ); ?></h4>

			<ul>
			<?php if ( isset( $data['rpgc_id'][0] ) ) { ?>
			<li><?php _e( 'Gift Card #:', RPWCGC_CORE_TEXT_DOMAIN ); ?>
			<?php
				echo esc_attr( $data['rpgc_id'][0] );
				?></li>
			<?php } if ( isset( $data['rpgc_payment'][0] ) ) { ?>
			<li><?php _e( 'Payment:', RPWCGC_CORE_TEXT_DOMAIN ); ?>
			<?php
				echo woocommerce_price( $data['rpgc_payment'][0] );
				?></li>
			<?php } if ( isset( $data['rpgc_balance'][0] ) ) { ?>
			<li><?php _e( 'Balance remaining:', RPWCGC_CORE_TEXT_DOMAIN ); ?>
			<?php
				echo woocommerce_price( $data['rpgc_balance'][0] );
				?></li>
			<?php } ?>
			</ul>
		<?php
		}
	}
}
add_action( 'woocommerce_admin_order_totals_after_shipping', 'rpgc_show_giftcard_in_order' );