<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**	
 * Sets up the new meta box for the creation of a gift card.
 * Removes the other three Meta Boxes that are not needed.
 *
 */
function rpgc_meta_boxes() {
	global $post;

	add_meta_box(
		'rpgc-woocommerce-data',
		__( 'Gift Card Data', 'rpgiftcards' ),
		'rpgc_meta_box',
		'rp_shop_giftcard',
		'normal',
		'high'
	);

	$data = get_post_meta( $post->ID );

	if ( isset( $data['rpgc_id'] ) ) 
		if ( $data['rpgc_id'][0] <> '' )
			add_meta_box(
				'rpgc-order-data',
				__( 'Gift Card Informaiton', 'rpgiftcards' ),
				'rpgc_info_meta_box',
				'shop_order',
				'side',
				'default'
			);

	if ( ! isset( $_GET['action'] ) ) 
		remove_post_type_support( 'rp_shop_giftcard', 'title' );
	
	if ( isset ( $_GET['action'] ) )
		add_meta_box(
			'rpgc-more-options',
			__( 'Additional Card Options', 'rpgiftcards' ),
			'rpgc_options_meta_box',
			'rp_shop_giftcard',
			'side',
			'low'
		);		

	remove_meta_box( 'woothemes-settings', 'rp_shop_giftcard' , 'normal' );
	remove_meta_box( 'commentstatusdiv', 'rp_shop_giftcard' , 'normal' );
	remove_meta_box( 'commentsdiv', 'rp_shop_giftcard' , 'normal' );
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

		.form-field input, .form-field textarea { width:100%;}

		input[type="checkbox"], input[type="radio"] { float: left; width:16px;}
	</style>

	<div id="giftcard_options" class="panel woocommerce_options_panel">
	<?php
	
	do_action( 'rpgc_woocommerce_options_before_sender' );

	// Description
	woocommerce_wp_textarea_input(
		array(
			'id' => 'rpgc_description',
			'label' => __( 'Gift Card description', 'rpgiftcards' ),
			'placeholder' => '',
			'description' => __( 'Optionally enter a description for this gift card for your reference.', 'rpgiftcards' ),
		)
	);
	
	do_action( 'rpgc_woocommerce_options_after_description' );

	echo '<h2>' . __('Who are you sending this to?',  'rpgiftcards' ) . '</h2>';
	// To
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_to',
			'label' => __( 'To', 'rpgiftcards' ),
			'placeholder' => '',
			'description' => __( 'Who is getting this gift card.', 'rpgiftcards' ),
		)
	);
	// To Email
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_email_to',
			'label' => __( 'Email To', 'rpgiftcards' ),
			'placeholder' => '',
			'description' => __( 'What email should we send this gift card to.', 'rpgiftcards' ),
		)
	);

	// From
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_from',
			'label' => __( 'From', 'rpgiftcards' ),
			'placeholder' => '',
			'description' => __( 'Who is sending this gift card.', 'rpgiftcards' ),
		)
	);
	// From Email
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_email_from',
			'label' => __( 'Email From', 'rpgiftcards' ),
			'placeholder' => '',
			'description' => __( 'What email account is sending this gift card.', 'rpgiftcards' ),
		)
	);
	
	do_action( 'rpgc_woocommerce_options_after_sender' );

	echo '</div><div class="panel woocommerce_options_panel">';

	echo '<h2>' . __('Personalize it',  'rpgiftcards' ) . '</h2>';
	
	do_action( 'rpgc_woocommerce_options_before_personalize' );
	
	// Amount
	woocommerce_wp_text_input(
		array(
			'id'     => 'rpgc_amount',
			'label'    => __( 'Gift Card Amount', 'rpgiftcards' ),
			'placeholder'  => '0.00',
			'description'  => __( 'Value of the Gift Card.', 'rpgiftcards' ),
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
					'label'    => __( 'Gift Card Balance', 'rpgiftcards' ),
					'placeholder'  => '0.00',
					'description'  => __( 'Remaining Balance of the Gift Card.', 'rpgiftcards' ),
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
			'label' => __( 'Gift Card Note', 'rpgiftcards' ),
			'description' => __( 'Enter a message to your customer.', 'rpgiftcards' ),
			'class' => 'short'
			
		)
	);

	// Expiry date
	woocommerce_wp_text_input(
		array(
			'id' => 'rpgc_expiry_date',
			'label' => __( 'Expiry date', 'rpgiftcards' ),
			'placeholder' => _x( 'Never expire', 'placeholder', 'rpgiftcards' ),
			'description' => __( 'The date this Gift Card will expire, <code>YYYY-MM-DD</code>.', 'rpgiftcards' ),
			'class' => 'date-picker, short',
			'custom_attributes' => array( 'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" )
		)
	);

	do_action( 'rpgc_woocommerce_options' );
	do_action( 'rpgc_woocommerce_options_after_personalize' );


	echo '</div>';
}



/**
 * Creates the Giftcard Regenerate Meta Box in the admin control panel when in the Giftcard Post Type.  Allows you to click a button regenerate the number.
 * @param  [type] $post
 * @return [type]
 */
function rpgc_options_meta_box( $post ) {
	global $woocommerce;

	wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );	
	
	echo '<div id="giftcard_regenerate" class="panel woocommerce_options_panel">';
	echo '    <div class="options_group">';

	if( $post->post_status <> 'zerobalance' ) {
		// Regenerate the Card Number
		woocommerce_wp_checkbox( array( 'id' => 'rpgc_resend_email', 'label' => __( 'Send Gift Card Email', 'rpgiftcards' ) ) );

		// Regenerate the Card Number
		woocommerce_wp_checkbox( array( 'id' => 'rpgc_regen_number', 'label' => __( 'Regenerate Card Number', 'rpgiftcards' ) ) );

		do_action( 'rpgc_add_more_options' );

	} else {
		_e( 'No additional options available. Zero balance', 'rpgiftcards' );

		
	}

	echo '    </div>';
	echo '</div>';

}



function rpgc_info_meta_box( $post ) {
	global $wpdb;
	
	$data = get_post_meta( $post->ID );

	$orderCardNumber 	= wpr_get_order_card_number( $post->ID );
	$orderCardBalance 	= wpr_get_order_card_balance( $post->ID );
	$orderCardPayment 	= wpr_get_order_card_payment( $post->ID );
	
	echo '<div id="giftcard_regenerate" class="panel woocommerce_options_panel">';
	echo '    <div class="options_group">';
		echo '<ul>';
			if ( isset( $orderCardNumber ) )
				echo '<li>' . __( 'Gift Card #:', 'rpgiftcards' ) . ' ' . esc_attr( $orderCardNumber ) . '</li>';

			if ( isset( $orderCardPayment ) )
				echo '<li>' . __( 'Payment:', 'rpgiftcards' ) . ' ' . woocommerce_price( $orderCardPayment ) . '</li>';

			if ( isset( $orderCardBalance ) )
				echo '<li>' . __( 'Balance remaining:', 'rpgiftcards' ) . ' ' . woocommerce_price( $orderCardBalance ) . '</li>';

		echo '</ul>';

		$giftcard_found = wpr_get_giftcard_by_code( $orderCardNumber );

		if ( $giftcard_found ) {
			echo '<div>';
				$link = 'post.php?post=' . $giftcard_found . '&action=edit';
				echo '<a href="' . admin_url( $link ) . '">' . __('Access Gift Card', 'rpgiftcards') . '</a>';
			echo '</div>';
		
		}

	echo '    </div>';
	echo '</div>';
}
