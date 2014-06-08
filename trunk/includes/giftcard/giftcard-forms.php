<?php
/**
 * Checkout giftcard form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rpgc_cart_form() {
	global $woocommerce;
	
	if( get_option( 'woocommerce_enable_giftcard_cartpage' ) == 'yes' ){
		do_action( 'wpr_before_cart_form' );
	?>
		<div class="giftcard" style="float: left;">
			<label for="giftcard_code" style="display: none;"><?php _e( 'Giftcard', 'woocommerce' ); ?>:</label>
			<input type="text" name="giftcard_code" class="input-text" id="giftcard_code" value="" placeholder="<?php _e( 'Gift Card', 'woocommerce' ); ?>" style="    border: 1px solid #E0DADF;
			    box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.1) inset;
			    box-sizing: border-box;
			    float: left;
			    line-height: 1em;
			    margin: 0 4px 0 0;
			    outline: 0 none;
			    padding: 6px 6px 5px;"/>
			<input type="submit" class="button" name="update_cart" value="<?php _e( 'Apply Gift card', 'woocommerce' ); ?>" />
		</div>
<?php
		do_action( 'wpr_after_cart_form' );
	}

}
add_action( 'woocommerce_proceed_to_checkout', 'rpgc_cart_form', -10 );

if ( ! function_exists( 'rpgc_checkout_form' ) ) {

	/**
	 * Output the Giftcard form for the checkout.
	 * @access public
	 * @subpackage Checkout
	 * @return void
	 */
	function rpgc_checkout_form() {
		global $woocommerce;

		if( get_option( 'woocommerce_enable_giftcard_checkoutpage' ) == 'yes' ){

			$info_message = apply_filters( 'woocommerce_checkout_giftcaard_message', __( 'Have a giftcard?', RPWCGC_CORE_TEXT_DOMAIN ) );
			do_action( 'wpr_before_checkout_form' );

			?>

			<p class="woocommerce-info"><?php echo $info_message; ?> <a href="#" class="showgiftcard"><?php _e( 'Click here to enter your giftcard', RPWCGC_CORE_TEXT_DOMAIN ); ?></a></p>

			<form class="checkout_giftcard" method="post" style="display:none">

				<p class="form-row form-row-first">
					<input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift card', RPWCGC_CORE_TEXT_DOMAIN ); ?>" id="giftcard_code" value="" />
				</p>

				<p class="form-row form-row-last">
					<input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Gift card', RPWCGC_CORE_TEXT_DOMAIN ); ?>" />
				</p>

				<div class="clear"></div>
			</form>

			<?php do_action( 'wpr_after_checkout_form' ); ?>

			<script>
				jQuery(document).ready(function($) {
					$('a.showgiftcard').click(function(){
						$('.checkout_giftcard').slideToggle();
						$('#giftcard_code').focus();
							return false;
						});

						/* AJAX Coupon Form Submission */
						$('form.checkout_giftcard').submit( function() {
							var $form = $(this);

							if ( $form.is('.processing') ) return false;

							$form.addClass('processing').block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

							var data = {
								action: 			'woocommerce_apply_giftcard',
								security: 			'apply-giftcard',
								giftcard_code:		$form.find('input[name=giftcard_code]').val()
							};

							$.ajax({
								type: 		'POST',
								url: 		woocommerce_params.ajax_url,
								data:		data,
								success: 	function( code ) {
									$('.woocommerce-error, .woocommerce-message').remove();
									$form.removeClass('processing').unblock();

									if ( code ) {
										$form.before( code );
										$form.slideUp();

										$('body').trigger('update_checkout');
									}
								},
								dataType: 	"html"
							});
							return false;
						});

				});

			</script>

		<?php
		}
	}
}
add_action( 'woocommerce_before_checkout_form', 'rpgc_checkout_form', 10 );

function rpgc_cart_fields( ) {
	global $post;

	$is_giftcard = get_post_meta( $post->ID, '_giftcard', true );
	if ( $is_giftcard == 'yes' ) {

		do_action( 'rpgc_before_all_giftcard_fields' );

		$rpw_to 		= get_option( 'woocommerce_giftcard_to' );
		$rpw_toEmail 	= get_option( 'woocommerce_giftcard_toEmail' );
		$rpw_note 		= get_option( 'woocommerce_giftcard_note' );

		$rpw_to_check 		= ( $rpw_to <> NULL ? $rpw_to : __('To', RPWCGC_CORE_TEXT_DOMAIN ) );
		$rpw_toEmail_check 	= ( $rpw_toEmail <> NULL ? $rpw_toEmail : __('To Email', RPWCGC_CORE_TEXT_DOMAIN )  );
		$rpw_note_check		= ( $rpw_note <> NULL ? $rpw_note : __('Note', RPWCGC_CORE_TEXT_DOMAIN )  );
?>

		<div>
			<div class="rpw_product_message"><?php _e('All fields below are optional', RPWCGC_CORE_TEXT_DOMAIN ); ?></div>
			<?php  do_action( 'rpgc_before_product_fields' ); ?>
			<input type="hidden" id="rpgc_description" name="rpgc_description" value="<?php _e('Generated from the website.', RPWCGC_CORE_TEXT_DOMAIN ); ?>" />
			<input name="rpgc_to" id="rpgc_to" class="input-text" placeholder="<?php echo $rpw_to_check; ?>" style="margin-bottom:5px;">
			<input type="email" name="rpgc_to_email" id="rpgc_to_email" class="input-text" placeholder="<?php echo $rpw_toEmail_check; ?>" style="margin-bottom:5px;">
			<textarea class="input-text" id="rpgc_note" name="rpgc_note" rows="2" placeholder="<?php echo $rpw_note_check; ?>" style="margin-bottom:5px;"></textarea>
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

