<?php

	
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

	// Check for duplicate giftcards
	$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
		SELECT $wpdb->posts.ID
		FROM $wpdb->posts
		WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_title = '%s'
	", $post->post_title ) );

	if ( isset( $_POST['rpgc_description'] ) ) {
		$description = woocommerce_clean( $_POST['rpgc_description'] );
		update_post_meta( $post_id, 'rpgc_description', $description );
	}
	if ( isset( $_POST['rpgc_to'] ) ) {
		$to    = woocommerce_clean( $_POST['rpgc_to'] );
		update_post_meta( $post_id, 'rpgc_to', $to );
	}
	if ( isset( $_POST['rpgc_email_to'] ) ) {
		$toEmail  = woocommerce_clean( $_POST['rpgc_email_to'] );
		update_post_meta( $post_id, 'rpgc_email_to', $toEmail );
	}
	if ( isset( $_POST['rpgc_from'] ) ) {
		$from    = woocommerce_clean( $_POST['rpgc_from'] );
		update_post_meta( $post_id, 'rpgc_from', $from );
	}
	if ( isset( $_POST['rpgc_email_from'] ) ) {
		$fromEmail  = woocommerce_clean( $_POST['rpgc_email_from'] );
		update_post_meta( $post_id, 'rpgc_email_from', $fromEmail );
	}
	if ( isset( $_POST['rpgc_amount'] ) ) {
		$amount   = woocommerce_clean( $_POST['rpgc_amount'] );
		update_post_meta( $post_id, 'rpgc_amount', $amount );

		if ( ! isset( $_POST['rpgc_balance'] ) ) {
			$balance   = woocommerce_clean( $_POST['rpgc_amount'] );
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
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$subject = apply_filters( 'woocommerce_email_subject_gift_card', sprintf( '[%s] %s', $blogname, __( 'Gift Card Information', 'woocommerce' ) ), $post->post_title );
		$sendEmail = get_bloginfo( 'admin_email' );

		ob_start();

		$mailer        = WC()->mailer();
		$theMessage 	= sendGiftcardEmail ( $post );

		$theMessage 	= apply_filters( 'rpgc_emailContents', $theMessage );

	  	$email_heading = __( 'New gift card from ', 'woocommerce' ) . $blogname;
	  	$email_heading = apply_filters( 'rpgc_emailSubject', $email_heading );

	  	echo $mailer->wrap_message( $email_heading, $theMessage );

		$message = ob_get_clean();
		//	CC, BCC, additional headers
		$headers = "From: " . $sendEmail . "\r\n" . " Reply-To: " . $sendEmail . "\r\n" . " Content-Type: text/html\r\n";
		// Attachments
		$attachments = apply_filters('woocommerce_email_attachments', '', 'gift_card', $post->post_title);

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

	ob_start();
	?>

	<div class="message">
		<?php _e( 'Dear', RPWCGC_CORE_TEXT_DOMAIN ); ?> <?php echo get_post_meta( $giftCard->ID, 'rpgc_to', true); ?>,<br /><br />
			
		<?php echo get_post_meta( $giftCard->ID, 'rpgc_from', true); ?> has selected a <strong><a href="<?php bloginfo( 'url' ); ?>"><?php bloginfo( 'name' ); ?></a></strong> <?php _e( 'Gift Card for you! This card can be used for online purchases at', RPWCGC_CORE_TEXT_DOMAIN ); ?> <?php bloginfo( 'name' ); ?>. <br />

		<h4><?php _e( 'Gift Card Amount', RPWCGC_CORE_TEXT_DOMAIN ); ?>: <?php echo woocommerce_price( get_post_meta( $giftCard->ID, 'rpgc_balance', true) ); ?></h4>
		<h4><?php _e( 'Gift Card Number', RPWCGC_CORE_TEXT_DOMAIN ); ?>: <?php echo $giftCard->post_title; ?></h4>

		<?php
		if ( $expiry_date != "" ) {
			echo __( 'Expiration Date', RPWCGC_CORE_TEXT_DOMAIN ) . ': ' . get_post_meta( $giftCard->ID, 'rpgc_expiry_date', true);
		}
		?>
	</div>

	<div style="padding-top: 10px; padding-bottom: 10px; border-top: 1px solid #ccc;">
		<?php echo get_post_meta( $giftCard->ID, 'rpgc_note', true); ?>
	</div>

	<div style="padding-top: 10px; border-top: 1px solid #ccc;">
		<?php _e( 'Using your Gift Card is easy', RPWCGC_CORE_TEXT_DOMAIN ); ?>:

		<ol>
			<li><?php _e( 'Shop at', RPWCGC_CORE_TEXT_DOMAIN ); ?> <?php bloginfo( 'name' ); ?></li>
			<li><?php _e( 'Select "Pay with a Gift Card" during checkout.', RPWCGC_CORE_TEXT_DOMAIN ); ?></li>
			<li><?php _e( 'Enter your card number.', RPWCGC_CORE_TEXT_DOMAIN ); ?></li>
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
	if ( ( $data['post_type'] == 'rp_shop_giftcard' ) && ( ( $data['post_title'] == "" ) || ( $data['post_title'] == "Auto Draft" ) ) ) {

		$myNumber = rpgc_generate_number( );		
		
		$data['post_title'] = $myNumber;
		$data['post_name'] = $myNumber;
	}

	return apply_filters('rpgc_create_number', $data);
}
add_filter( 'wp_insert_post_data' , 'rpgc_create_number' , 10, 2 );


function rpgc_generate_number( ) {
	$randomNumber = substr( number_format( time() * rand(), 0, '', '' ), 0, 15 );

	return apply_filters('rpgc_generate_number', $randomNumber);
}

/**
 * Function to refund the amount paid by Giftcard back to the Card when the entire order is refunded
 *
 */
function rpgc_refund_order( $order_id ) {
	global $woocommerce, $wpdb;

	$order = new WC_Order( $order_id );

	$total = $order->get_order_total();
	$giftCardNumber = get_post_meta( $order_id, 'rpgc_id' );

	// Check for Giftcard
	$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
		SELECT $wpdb->posts.ID
		FROM $wpdb->posts
		WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_title = '%s'
	", $giftCardNumber ) );

	if ( $giftcard_found ) {

		$oldBalance = get_post_meta( $giftcard_found, 'rpgc_balance' );
		$refundAmount = get_post_meta( $order_id, 'rpgc_payment' );

		$giftcard_balance = (float) $oldBalance[0] + (float) $refundAmount[0];

		update_post_meta( $giftcard_found, 'rpgc_balance', $giftcard_balance ); // Update balance of Giftcard
	}
}
add_action( 'woocommerce_order_status_refunded', 'rpgc_refund_order' );

