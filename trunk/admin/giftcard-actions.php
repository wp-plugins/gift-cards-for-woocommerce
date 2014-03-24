<?php
/**
 * Giftcard Admin Actions
 *
 * @package     Woocommerce
 * @subpackage  Admin/Giftcards
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

	
function rpgc_admin_enqueue() {
	global $woocommerce, $typenow, $post, $wp_scripts;

	if ( $typenow == 'post' && ! empty( $_GET['post'] ) ) {
		$typenow = $post->post_type;
	} elseif ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
		$post = get_post( $_GET['post'] );
		$typenow = $post->post_type;
	}

	if ( $typenow == 'rp_shop_giftcard' ) {

		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
		
		wp_enqueue_style( 'jquery-ui-style', RPWCGC_URL . '/style/jquery-ui.css' );
		wp_enqueue_style( 'font-awesome_styles', RPWCGC_URL . '/style/font-awesome/css/font-awesome.min.css' ); // Adds the custom icon style
		wp_enqueue_style( 'farbtastic' );

		wp_enqueue_script( 'woocommerce_writepanel' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'ajax-chosen' );
		wp_enqueue_script( 'chosen' );
		wp_enqueue_script( 'plupload-all' );

	}

	do_action( 'rpgc_admin_css' );

}
add_action( 'admin_enqueue_scripts', 'rpgc_admin_enqueue' );

/**	
 * Sets up the new meta box for the creation of a gift card.
 * Removes the other three Meta Boxes that are not needed.
 *
 */
function rpgc_meta_boxes() {
	global $post;

	add_meta_box(
		'rpgc-woocommerce-data',
		__( 'Gift Card Data', RPWCGC_CORE_TEXT_DOMAIN ),
		'rpgc_meta_box',
		'rp_shop_giftcard',
		'normal',
		'high'
	);


	if ( ! isset( $_GET['action'] ) )
		remove_post_type_support( 'rp_shop_giftcard', 'title' );

	remove_meta_box( 'woothemes-settings', 'rp_shop_giftcard' , 'normal' );
	remove_meta_box( 'commentstatusdiv', 'rp_shop_giftcard' , 'normal' );
	remove_meta_box( 'slugdiv', 'rp_shop_giftcard' , 'normal' );
}
add_action( 'add_meta_boxes', 'rpgc_meta_boxes' );
	
/**
 * Creates the Giftcard Meta Box in the admin control panel when in the Giftcard Post Type.  Allows you to create a giftcard manually.
 * @param  [type] $post
 * @return [type]
 */
function rpgc_meta_box( $post ) {
	global $woocommerce;

	wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );
	?>
	<style type="text/css">
		#edit-slug-box, #minor-publishing-actions { display:none }
	</style>

	<div id="giftcard_options" class="panel woocommerce_options_panel">
	<?php

	echo '<div class="options_group">';
	// Description
	woocommerce_wp_textarea_input(
		array(
			'id' => 'rpgc_description',
			'label' => __( 'Gift Card description', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder' => '',
			'description' => __( 'Optionally enter a description for this gift card for your reference.', RPWCGC_CORE_TEXT_DOMAIN ),
		)
	);

	echo '<h2>' . __('Who are you sending this to?',  RPWCGC_CORE_TEXT_DOMAIN ) . '</h2>';
	// To
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_to',
			'label' => __( 'To', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder' => '',
			'description' => __( 'Who is getting this gift card.', RPWCGC_CORE_TEXT_DOMAIN ),
		)
	);
	// To Email
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_email_to',
			'label' => __( 'Email To', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder' => '',
			'description' => __( 'What email should we send this gift card to.', RPWCGC_CORE_TEXT_DOMAIN ),
		)
	);

	// From
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_from',
			'label' => __( 'From', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder' => '',
			'description' => __( 'Who is sending this gift card.', RPWCGC_CORE_TEXT_DOMAIN ),
		)
	);
	// From Email
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_email_from',
			'label' => __( 'Email From', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder' => '',
			'description' => __( 'What email account is sending this gift card.', RPWCGC_CORE_TEXT_DOMAIN ),
		)
	);

	echo '</div><div class="options_group">';

	echo '<h2>' . __('Personalize it',  RPWCGC_CORE_TEXT_DOMAIN ) . '</h2>';
	// Amount
	woocommerce_wp_text_input(
		array(
			'id'     => 'rpgc_amount',
			'label'    => __( 'Gift Card Amount', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder'  => '0.00',
			'description'  => __( 'Value of the Gift Card.', RPWCGC_CORE_TEXT_DOMAIN ),
			'type'    => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' )
		)
	);
	if ( isset( $_GET['action']  ) ) {
		if ( $_GET['action'] == 'edit' ) {
			// Remaining Balance
			woocommerce_wp_text_input(
				array(
					'id'    => 'rpgc_balance',
					'label'    => __( 'Gift Card Balance', RPWCGC_CORE_TEXT_DOMAIN ),
					'placeholder'  => '0.00',
					'description'  => __( 'Remaining Balance of the Gift Card.', RPWCGC_CORE_TEXT_DOMAIN ),
					'type'    => 'number',
					'custom_attributes' => array( 'step' => 'any', 'min' => '0' )
				)
			);
		}
	}
	// Notes
	woocommerce_wp_textarea_input(
		array(
			'id' => 'rpgc_note',
			'label' => __( 'Gift Card Note', RPWCGC_CORE_TEXT_DOMAIN ),
			'description' => __( 'Enter a message to your customer.', RPWCGC_CORE_TEXT_DOMAIN ),
			'class' => 'wide'
		)
	);

	// Expiry date
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_expiry_date',
			'label' => __( 'Expiry date', RPWCGC_CORE_TEXT_DOMAIN ),
			'placeholder' => _x( 'Never expire', 'placeholder', RPWCGC_CORE_TEXT_DOMAIN ),
			'description' => __( 'The date this Gift Card will expire, <code>YYYY-MM-DD</code>. (Currently not available)', RPWCGC_CORE_TEXT_DOMAIN ),
			'class' => 'short date-picker',
			'custom_attributes' => array( 'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" )
		)
	);

	do_action( 'rpgc_woocommerce_options' );

	echo '</div>';

?>
</div>
<?php
}

	add_filter( 'manage_edit-rp_shop_giftcard_columns', 'rpgc_add_columns' );

	function rpgc_add_columns( $columns ) {
		$new_columns = ( is_array( $columns ) ) ? $columns : array();
		unset( $new_columns['date'] );
		unset( $new_columns['comments'] );

		//all of your columns will be added before the actions column on the Giftcard page

		$new_columns["amount"]    = __( 'Giftcard Amount', RPWCGC_CORE_TEXT_DOMAIN );
		$new_columns["balance"]   = __( 'Remaining Balance', RPWCGC_CORE_TEXT_DOMAIN );
		$new_columns["buyer"]    = __( 'Buyer', RPWCGC_CORE_TEXT_DOMAIN );
		$new_columns["recipient"]   = __( 'Recipient', RPWCGC_CORE_TEXT_DOMAIN );
		$new_columns["expiry_date"]  = __( 'Expiry date', RPWCGC_CORE_TEXT_DOMAIN );

		$new_columns['comments']   = $columns['comments'];
		$new_columns['date']    = $columns['date'];

		return $new_columns;
	}


/**
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

function rpgc_add_order_giftcard( $total_rows ) {
	global $woocommerce;

	$order_id = $woocommerce->session->idForEmail;

	$order = new WC_Order( $order_id );

	$giftCardPayment = get_post_meta( $order_id, 'rpgc_payment');

	$total_rows['rpgc_data'] = array(
		'label' => __( 'Gift Card Payment:', 'woocommerce' ),
		'value'	=> woocommerce_price( $giftCardPayment[0] )
	);

	return $total_rows;
}
add_filter( 'woocommerce_get_order_item_totals', 'rpgc_add_order_giftcard');

/**
 * Updates the Gift Card and the order information when the order is processed
 *
 */
function rpgc_update_card( $order_id ) {
	global $woocommerce;

	if ( $woocommerce->session->giftcard_post <> '' ) {
		update_post_meta( $woocommerce->session->giftcard_post, 'rpgc_balance', $woocommerce->session->giftcard_balance ); // Update balance of Giftcard
		update_post_meta( $order_id, 'rpgc_id', $woocommerce->session->giftcard_id );
		update_post_meta( $order_id, 'rpgc_payment', $woocommerce->session->giftcard_payment );
		update_post_meta( $order_id, 'rpgc_balance', $woocommerce->session->giftcard_balance );

		$woocommerce->session->idForEmail = $order_id;
		unset( $woocommerce->session->giftcard_id, $woocommerce->session->giftcard_payment, $woocommerce->session->giftcard_post, $woocommerce->session->giftcard_balance );
	}

	if ( isset ( $woocommerce->session->giftcard_data ) ) {
		update_post_meta( $order_id, 'rpgc_data', $woocommerce->session->giftcard_data );

		unset( $woocommerce->session->giftcard_data );
	}

}
add_action( 'woocommerce_order_status_pending', 'rpgc_update_card' );
add_action( 'woocommerce_order_status_on-hold', 'rpgc_update_card' );
add_action( 'woocommerce_order_status_completed', 'rpgc_update_card' );
add_action( 'woocommerce_order_status_processing', 'rpgc_update_card' );


	
/**
 */
function rpgc_process_giftcard_meta( $post_id, $post ) {
	global $wpdb, $woocommerce_errors;

	$description  = '';
	$to     = '';
	$toEmail   = '';
	$from     = '';
	$fromEmail   = '';
	$sendto_from   = '';
	$sendautomaticly = '';
	$amount    = '';
	$balance   = '';
	$note    = '';
	$expiry_date   = '';
	$sendTheEmail  = 0;

	// Ensure coupon code is correctly formatted
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
	}

	if( $sendTheEmail == 1 ) {
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$subject = apply_filters( 'woocommerce_email_subject_gift_card', sprintf( '[%s] %s', $blogname, __( 'Gift card information', 'woocommerce' ) ), $post->post_title );
		$sendEmail = get_bloginfo( 'admin_email' );

		ob_start();

		$mailer        = WC()->mailer();
		$theMessage 	= sendGiftcardEmail ( $post );
	  	$email_heading = __( 'New gift card from ', 'woocommerce' ) . $blogname;
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
		Dear <?php echo get_post_meta( $giftCard->ID, 'rpgc_to', true); ?>,<br /><br />
			
		<?php echo get_post_meta( $giftCard->ID, 'rpgc_from', true); ?> has selected a <strong><a href="<?php bloginfo( 'url' ); ?>"><?php bloginfo( 'name' ); ?></a></strong> Gift Card for you! This card can be used for online purchases at <?php bloginfo( 'name' ); ?>. <br />

		<h4>Gift Card Amount: <?php echo woocommerce_price( get_post_meta( $giftCard->ID, 'rpgc_amount', true) ); ?></h4>
		<h4>Gift Card Number: <?php echo $giftCard->post_title; ?></h4>

		<?php
		if ( $expiry_date != "" ) {
			echo 'Expiration Date: ' . get_post_meta( $giftCard->ID, 'rpgc_expiry_date', true);
		}
		?>
	</div>

	<div style="padding-top: 10px; padding-bottom: 10px; border-top: 1px solid #ccc;">
		<?php echo get_post_meta( $giftCard->ID, 'rpgc_note', true); ?>
	</div>

	<div style="padding-top: 10px; border-top: 1px solid #ccc;">
		Using your Gift Card is easy:

		<ol>
			<li>Shop at <?php bloginfo( 'name' ); ?></li>
			<li>Select "Pay with a Gift Card" during checkout.</li>
			<li>Enter your card number.</li>
		</ol>
	</div>

	<?php

	return ob_get_clean();

}

function rpgc_res_fromemail($email) {
    $wpfrom = get_option('admin_email');
    return $wpfrom;
}
 
function rpgc_res_fromname($email){
    $wpfrom = get_option('blogname');
    return $wpfrom;
}

function rpgc_add_settings_page( $settings ) {
	$settings[] = include( RPWCGC_PATH . 'admin/giftcard-settings.php' );

	return apply_filters( 'rpgc_setting_classes', $settings );
}
add_filter('woocommerce_get_settings_pages','rpgc_add_settings_page', 10, 1);