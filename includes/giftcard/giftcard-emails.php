<?php

/**
 * Gift Card Email Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * WP-Invoice AJAX Handler
 */
class WPR_Giftcard_Email {

	public function sendEmail ( $post ) {
		$blogname 		= wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$subject 		= apply_filters( 'woocommerce_email_subject_gift_card', sprintf( '[%s] %s', $blogname, __( 'Gift Card Information', 'rpgiftcards' ) ), $post->post_title );
		$sendEmail 		= get_bloginfo( 'admin_email' );

		ob_start();

		$mailer 		= WC()->mailer();
		$email 			= new WPR_Giftcard_Email();


		$theMessage 	= $email->sendGiftcardEmail ( $post );

		$theMessage 	= apply_filters( 'rpgc_emailContents', $theMessage );

	  	$email_heading 	= __( 'New gift card from ', 'rpgiftcards' ) . $blogname;
	  	$email_heading 	= apply_filters( 'rpgc_emailSubject', $email_heading );
	  	$toEmail		= wpr_get_giftcard_to_email( $post->ID );

	  	echo $mailer->wrap_message( $email_heading, $theMessage );

		$message 		= ob_get_clean();
		//	CC, BCC, additional headers
		$headers 		= "From: " . $sendEmail . "\r\n" . " Reply-To: " . $sendEmail . "\r\n" . " Content-Type: text/html\r\n";
		// Attachments
		$attachments 	= apply_filters('woocommerce_email_attachments', '', 'gift_card', $post->post_title);

		// Send the mail
		add_filter('wp_mail_from', array( $this, 'rpgc_res_fromemail') );
		add_filter('wp_mail_from_name', array( $this, 'rpgc_res_fromname') );

		wp_mail( $toEmail, $subject, $message, $headers, $attachments );
		
		remove_filter('wp_mail_from', array( $this, 'rpgc_res_fromemail') );
		remove_filter('wp_mail_from_name', array( $this, 'rpgc_res_fromname') );

	}
 
    public function sendGiftcardEmail ( $giftCard ) {
		$expiry_date = wpr_get_giftcard_expiration( $giftCard->ID );
		$date_format = get_option('date_format');
		ob_start();
		?>

		<div class="message">


			<?php _e( 'Dear', 'rpgiftcards' ); ?> <?php echo wpr_get_giftcard_to( $giftCard->ID ); ?>,<br /><br />
				
			<?php echo wpr_get_giftcard_from( $giftCard->ID ); ?> <?php _e('has selected a', 'rpgiftcards' ); ?> <strong><a href="<?php bloginfo( 'url' ); ?>"><?php bloginfo( 'name' ); ?></a></strong> <?php _e( 'Gift Card for you! This card can be used for online purchases at', 'rpgiftcards' ); ?> <?php bloginfo( 'name' ); ?>. <br />

			<h4><?php _e( 'Gift Card Amount', 'rpgiftcards' ); ?>: <?php echo woocommerce_price( wpr_get_giftcard_balance( $giftCard->ID ) ); ?></h4>
			<h4><?php _e( 'Gift Card Number', 'rpgiftcards' ); ?>: <?php echo $giftCard->post_title; ?></h4>

			<?php
			if ( $expiry_date != "" ) {
				echo __( 'Expiration Date', 'rpgiftcards' ) . ': ' . date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );
			}
			?>
		</div>

		<div style="padding-top: 10px; padding-bottom: 10px; border-top: 1px solid #ccc;">
			<?php echo get_post_meta( $giftCard->ID, 'rpgc_note', true); ?>
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

	public function rpgc_res_fromemail($email) {
	    $wpfrom = get_option('admin_email');
	    return apply_filters( 'rpgc_res_fromemail', $wpfrom);
	}
	 
	public function rpgc_res_fromname($email){
	    $wpfrom = get_option('blogname');
	    return apply_filters( 'rpgc_res_fromname', $wpfrom);
	}
}