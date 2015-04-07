<?php
/**
 * Gift Card Save Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly










/**
 */
function rpgc_process_giftcard_meta( $post_id, $post ) {
	global $wpdb, $woocommerce_errors;

	$description  		= '';
	$to     			= '';
	$toEmail   			= '';
	$from     			= '';
	$fromEmail   		= '';
	$sendto_from   		= '';
	$sendautomaticly 	= '';
	$amount    			= '';
	$balance   			= '';
	$note    			= '';
	$expiry_date   		= '';
	$sendTheEmail  		= 0;

	// Ensure gift card code is correctly formatted
	$wpdb->update( $wpdb->posts, array( 'post_title' => $post->post_title ), array( 'ID' => $post_id ) );

	/*if ( wpr_get_giftcard_by_code( $post->post_title ) ) {
		$newNumber = apply_filters( 'rpgc_regen_number', rpgc_generate_number());

		$wpdb->update( $wpdb->posts, array( 'post_title' => $newNumber ), array( 'ID' => $post_id ) );
		$wpdb->update( $wpdb->posts, array( 'post_name' => $newNumber ), array( 'ID' => $post_id ) );
	}*/

	if ( isset( $_POST['rpgc_description'] ) ) {
		$description 	= woocommerce_clean( $_POST['rpgc_description'] );
		update_post_meta( $post_id, 'rpgc_description', $description );
	}
	if ( isset( $_POST['rpgc_to'] ) ) {
		$to    			= woocommerce_clean( $_POST['rpgc_to'] );
		update_post_meta( $post_id, 'rpgc_to', $to );
	}
	if ( isset( $_POST['rpgc_email_to'] ) ) {
		$toEmail  		= woocommerce_clean( $_POST['rpgc_email_to'] );
		update_post_meta( $post_id, 'rpgc_email_to', $toEmail );
	}
	if ( isset( $_POST['rpgc_from'] ) ) {
		$from 			= woocommerce_clean( $_POST['rpgc_from'] );
		update_post_meta( $post_id, 'rpgc_from', $from );
	}
	if ( isset( $_POST['rpgc_email_from'] ) ) {
		$fromEmail 		= woocommerce_clean( $_POST['rpgc_email_from'] );
		update_post_meta( $post_id, 'rpgc_email_from', $fromEmail );
	}
	if ( isset( $_POST['rpgc_amount'] ) ) {
		$amount 		= woocommerce_clean( $_POST['rpgc_amount'] );
		update_post_meta( $post_id, 'rpgc_amount', $amount );

		if ( ! isset( $_POST['rpgc_balance'] ) ) {
			$balance 	= woocommerce_clean( $_POST['rpgc_amount'] );
			update_post_meta( $post_id, 'rpgc_balance', $balance );
			$sendTheEmail = 1;
		}
	}
	if ( isset( $_POST['rpgc_balance'] ) ) {
		$balance   = woocommerce_clean( $_POST['rpgc_balance'] );
		update_post_meta( $post_id, 'rpgc_balance', $balance );
	}
	if ( isset( $_POST['rpgc_note'] ) ) {
		$note   = woocommerce_clean( $_POST['rpgc_note'] );
		update_post_meta( $post_id, 'rpgc_note', $note );
	}
	if ( isset( $_POST['rpgc_expiry_date'] ) ) {
		$expiry_date = woocommerce_clean( $_POST['rpgc_expiry_date'] );
		update_post_meta( $post_id, 'rpgc_expiry_date', $expiry_date );
	} else {
		$expiry_date = '';
	}

	if ( isset( $_POST['rpgc_regen_number'] ) ) {
		$newNumber = apply_filters( 'rpgc_regen_number', rpgc_generate_number());

		$wpdb->update( $wpdb->posts, array( 'post_title' => $newNumber ), array( 'ID' => $post_id ) );
		$wpdb->update( $wpdb->posts, array( 'post_name' => $newNumber ), array( 'ID' => $post_id ) );

	}

	if( ( ( $sendTheEmail == 1 ) && ( $balance <> 0 ) ) || isset( $_POST['rpgc_resend_email'] ) ) {
		$blogname 		= wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$subject 		= apply_filters( 'woocommerce_email_subject_gift_card', sprintf( '[%s] %s', $blogname, __( 'Gift Card Information', 'rpgiftcards' ) ), $post->post_title );
		$sendEmail 		= get_bloginfo( 'admin_email' );

		ob_start();

		$mailer 		= WC()->mailer();
		$theMessage 	= sendGiftcardEmail ( $post );

		$theMessage 	= apply_filters( 'rpgc_emailContents', $theMessage );

	  	$email_heading 	= __( 'New gift card from ', 'rpgiftcards' ) . $blogname;
	  	$email_heading 	= apply_filters( 'rpgc_emailSubject', $email_heading );

	  	echo $mailer->wrap_message( $email_heading, $theMessage );

		$message 		= ob_get_clean();
		//	CC, BCC, additional headers
		$headers 		= "From: " . $sendEmail . "\r\n" . " Reply-To: " . $sendEmail . "\r\n" . " Content-Type: text/html\r\n";
		// Attachments
		$attachments 	= apply_filters('woocommerce_email_attachments', '', 'gift_card', $post->post_title);

		// Send the mail
		add_filter('wp_mail_from', 'rpgc_res_fromemail');
		add_filter('wp_mail_from_name', 'rpgc_res_fromname');

		wp_mail( $toEmail, $subject, $message, $headers, $attachments );
		
		remove_filter('wp_mail_from', 'rpgc_res_fromemail');
		remove_filter('wp_mail_from_name', 'rpgc_res_fromname');
	}

	/* Deprecated - same hook name as in the meta */
	do_action( 'woocommerce_rpgc_options' );
	do_action( 'woocommerce_rpgc_options_save' );

}
add_action( 'save_post', 'rpgc_process_giftcard_meta', 20, 2 );

function sendGiftcardEmail ( $giftCard ) {
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

function rpgc_res_fromemail($email) {
    $wpfrom = get_option('admin_email');
    return apply_filters( 'rpgc_res_fromemail', $wpfrom);
}
 
function rpgc_res_fromname($email){
    $wpfrom = get_option('blogname');
    return apply_filters( 'rpgc_res_fromname', $wpfrom);
}

/**
 * Creates a random 15 digit giftcard number
 *
 */
function rpgc_create_number( $data , $postarr ) {
	
	if( isset ( $_POST['original_publish'] ) ) {
		if ( ( $data['post_type'] == 'rp_shop_giftcard' ) && ( $_POST['original_publish'] == "Publish" ) ) {

			$myNumber = rpgc_generate_number( );		
			
			$data['post_title'] = $myNumber;
			$data['post_name'] = $myNumber;
		}
	}

	return apply_filters('rpgc_create_number', $data);
}
add_filter( 'wp_insert_post_data' , 'rpgc_create_number' , 10, 2 );


function rpgc_email_content_return( $email ) {
	$customEmail = get_option( 'woocommerce_enable_giftcard_custom_message' );

	if ($customEmail <> '' ) {
		$customEmail .= '<div>';
		$customEmial .= '<h4>' . __( 'Gift Card Amount', 'rpgiftcards' ) . ': ' . woocommerce_price( wpr_get_giftcard_balance( $giftCard->ID ) ) . '</h4>';
		$customEmail .= '<h4>' . __( 'Gift Card Number', 'rpgiftcards' ) . ': ' . $giftCard->post_title . '</h4>';
		$customEmail .= '</div>';
		
		return $customEmail;

	}
	
	return $email;
}




function rpgc_generate_number( ) {
	$randomNumber = substr( number_format( time() * rand(), 0, '', '' ), 0, 15 );

	return apply_filters('rpgc_generate_number', $randomNumber);
}

/**
 * Function to refund the amount paid by Giftcard back to the Card when the entire order is refunded
 *
 */
function rpgc_refund_order( $order_id ) {
	
	$giftCard_id = get_post_meta( $order_id, 'rpgc_id', true );
	$giftCard_refunded = get_post_meta( $order_id, 'rpgc_refunded', true );

	if ( $giftCard_id  && ! ( $giftCard_refunded == 'yes' ) ) {

		$oldBalance = wpr_get_giftcard_balance( $giftCard_id );
		$refundAmount = get_post_meta( $order_id, 'rpgc_payment', true );

		$giftcard_balance = (float) $oldBalance + (float) $refundAmount;

		update_post_meta( $giftCard_id, 'rpgc_balance', $giftcard_balance ); // Update balance of Giftcard
		update_post_meta( $order_id, 'rpgc_refunded', 'yes' ); // prevents multiple refunds of Giftcard
	}
}
add_action( 'woocommerce_order_status_refunded', 'rpgc_refund_order' );
add_action( 'woocommerce_order_status_pending_to_cancelled', 'rpgc_refund_order' );
add_action( 'woocommerce_order_status_on-hold_to_cancelled', 'rpgc_refund_order' );

function wpr_display_giftcard_on_order ( $order_id ) {
	
	$giftPayment = wpr_get_order_card_payment( $order_id );

	if( $giftPayment > 0 ) {
		?>
		<tr>
			<td class="label"><?php _e( 'Gift Card Payment', 'woocommerce' ); ?>:</td>
			<td class="giftcardTotal">
				<div class="view"><?php echo wc_price( $giftPayment ); ?></div>
			</td>
		</tr>
		<?php
	}

}
add_action ( 'woocommerce_admin_order_totals_after_discount', 'wpr_display_giftcard_on_order' );
