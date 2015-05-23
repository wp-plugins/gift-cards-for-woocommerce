<?php

/**
 * Gift Card Email Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly





class WPR_Giftcard_Email {

	public function sendEmail ( $post ) {
		$blogname 		= wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$subject 		= apply_filters( 'woocommerce_email_subject_gift_card', sprintf( '[%s] %s', $blogname, __( 'Gift Card Information', 'rpgiftcards' ) ), $post->post_title );
		$sendEmail 		= get_bloginfo( 'admin_email' );
		$headers 		= array('Content-Type: text/html; charset=UTF-8');

		ob_start();

		$mailer 		= WC()->mailer();
		$email 			= new WPR_Giftcard_Email();

		echo '<style >';
		wc_get_template( 'emails/email-styles.php' );
		echo '</style>';

	  	$email_heading 	= __( 'New gift card from ', 'rpgiftcards' ) . $blogname;
	  	$email_heading 	= apply_filters( 'rpgc_emailSubject', $email_heading );
	  	$toEmail		= wpr_get_giftcard_to_email( $post->ID );

	  	$theMessage 	= $email->sendGiftcardEmail ( $post->ID );
		$theMessage 	= apply_filters( 'rpgc_emailContents', $theMessage );

	  	echo $mailer->wrap_message( $email_heading, $theMessage );

		$message 		= ob_get_clean();

		$attachment = '';

		$email = new WC_Email();
		$email->send( $toEmail, $subject, $message, $headers, $attachment );

	}
 
    public function sendGiftcardEmail ( $giftCard ) {


		$expiry_date = wpr_get_giftcard_expiration( $giftCard );
		$date_format = get_option('date_format');
		ob_start();
		
		?>

		<div class="message">


			<?php _e( 'Dear', 'rpgiftcards' ); ?> <?php echo wpr_get_giftcard_to( $giftCard ); ?>,<br /><br />
				
			<?php echo wpr_get_giftcard_from( $giftCard ); ?> <?php _e('has selected a', 'rpgiftcards' ); ?> <strong><a href="<?php bloginfo( 'url' ); ?>"><?php bloginfo( 'name' ); ?></a></strong> <?php _e( 'Gift Card for you! This card can be used for online purchases at', 'rpgiftcards' ); ?> <?php bloginfo( 'name' ); ?>. <br />

			<h4><?php _e( 'Gift Card Amount', 'rpgiftcards' ); ?>: <?php echo woocommerce_price( wpr_get_giftcard_balance( $giftCard ) ); ?></h4>
			<h4><?php _e( 'Gift Card Number', 'rpgiftcards' ); ?>: <?php echo get_the_title( $giftCard ); ?></h4>

			<?php
			if ( $expiry_date != "" ) {
				echo __( 'Expiration Date', 'rpgiftcards' ) . ': ' . date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );
			}
			?>
		</div>

		<div style="padding-top: 10px; padding-bottom: 10px; border-top: 1px solid #ccc;">
			<?php echo get_post_meta( $giftCard, 'rpgc_note', true); ?>
		</div>

		<div style="padding-top: 10px; border-top: 1px solid #ccc;">
			<?php _e( 'Using your Gift Card is easy', 'rpgiftcards' ); ?>:

			<ol>
				<li><?php _e( 'Shop at', 'rpgiftcards' ); ?> <?php bloginfo( 'name' ); ?></li>
				<li><?php _e( 'Select "Pay with a Gift Card" during checkout.', 'rpgiftcards' ); ?></li>
				<li><?php _e( 'Enter your card number.', 'rpgiftcards' ); ?></li>
			</ol>
		</div>

		<?php
		

		$return = ob_get_clean();
		return apply_filters( 'rpgc_email_content_return', $return, $giftCard );

	}
}


