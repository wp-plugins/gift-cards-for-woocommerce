<?php
/**
 * Gift Card Metabox Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




if ( is_admin() ) {
    add_action( 'load-post.php', 'call_WPR_Gift_Card_Meta' );
    add_action( 'load-post-new.php', 'call_WPR_Gift_Card_Meta' );
}

/** 
 * The Class.
 */
class WPR_Gift_Card_Meta {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'rpgc_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		global $post, $wpdb;
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['woocommerce_giftcard_nonce'] ) )
			return $post_id;


		$nonce = $_POST['woocommerce_giftcard_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'woocommerce_save_data' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

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

		//var_dump( $post );
		//die();
		// Ensure gift card code is correctly formatted
		//$wpdb->update( $wpdb->posts, array( 'post_title' => $post->post_title ), array( 'ID' => $post_id ) );

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
			if ( $_POST['rpgc_regen_number'] == 'yes' ){
				$newNumber = apply_filters( 'rpgc_regen_number', rpgc_generate_number());

				$wpdb->update( $wpdb->posts, array( 'post_title' => $newNumber ), array( 'ID' => $post_id ) );
				$wpdb->update( $wpdb->posts, array( 'post_name' => $newNumber ), array( 'ID' => $post_id ) );
			}
		}

		if( ( ( $sendTheEmail == 1 ) && ( $balance <> 0 ) ) || isset( $_POST['rpgc_resend_email'] ) ) {
			
			$email = new WPR_Giftcard_Email();
			$post = get_post( $post_id );
			$email->sendEmail ( $post );

			update_post_meta( $post_id, 'rpgc_email_sent', "true" );
		}

		/* Deprecated - same hook name as in the meta */
		do_action( 'woocommerce_rpgc_options' );
		do_action( 'woocommerce_rpgc_options_save' );

	}


	/**	
	 * Sets up the new meta box for the creation of a gift card.
	 * Removes the other three Meta Boxes that are not needed.
	 *
	 */
	public function rpgc_meta_boxes() {
		global $post;

		add_meta_box(
			'rpgc-woocommerce-data',
			__( 'Gift Card Data', 'rpgiftcards' ),
			array( $this, 'rpgc_meta_box'),
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
					array( $this, 'rpgc_info_meta_box'),
					'shop_order',
					'side',
					'default'
				);

		if ( ! isset( $_GET['action'] ) ) 
			remove_post_type_support( 'rp_shop_giftcard', 'title' );
		
		if ( isset ( $_GET['action'] ) ) {
			add_meta_box(
				'rpgc-more-options',
				__( 'Additional Card Options', 'rpgiftcards' ),
				array( $this, 'rpgc_options_meta_box'),
				'rp_shop_giftcard',
				'side',
				'low'
			);

			add_meta_box(
				'rpgc-usage-data',
				__( 'Card Usage Data', 'rpgiftcards' ),
				array( $this, 'wpr_giftcard_usage_data'),
				'rp_shop_giftcard',
				'side',
				'low'
			);
		}

		remove_meta_box( 'woothemes-settings', 'rp_shop_giftcard' , 'normal' );
		remove_meta_box( 'commentstatusdiv', 'rp_shop_giftcard' , 'normal' );
		remove_meta_box( 'commentsdiv', 'rp_shop_giftcard' , 'normal' );
		remove_meta_box( 'slugdiv', 'rp_shop_giftcard' , 'normal' );
	}
	//add_action( 'add_meta_boxes', 'rpgc_meta_boxes' );


	/**
	 * Creates the Giftcard Meta Box in the admin control panel when in the Giftcard Post Type.  Allows you to create a giftcard manually.
	 * @param  [type] $post
	 * @return [type]
	 */
	public function rpgc_meta_box( $post ) {
		global $woocommerce;

		wp_nonce_field( 'woocommerce_save_data', 'woocommerce_giftcard_nonce' );
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
				'id' 			=> 'rpgc_description',
				'label'			=> __( 'Gift Card description', 'rpgiftcards' ),
				'placeholder' 	=> '',
				'description' 	=> __( 'Optionally enter a description for this gift card for your reference.', 'rpgiftcards' ),
			)
		);
		
		do_action( 'rpgc_woocommerce_options_after_description' );

		echo '<h2>' . __('Who are you sending this to?',  'rpgiftcards' ) . '</h2>';
		// To
		woocommerce_wp_text_input(
			array(
				'id' 			=> 'rpgc_to',
				'label' 		=> __( 'To', 'rpgiftcards' ),
				'placeholder' 	=> '',
				'description' 	=> __( 'Who is getting this gift card.', 'rpgiftcards' ),
			)
		);
		// To Email
		woocommerce_wp_text_input(
			array(
				'id' 			=> 'rpgc_email_to',
				'type' 			=> 'email',
				'label' 		=> __( 'Email To', 'rpgiftcards' ),
				'placeholder' 	=> '',
				'description' 	=> __( 'What email should we send this gift card to.', 'rpgiftcards' ),
			)
		);

		// From
		woocommerce_wp_text_input(
			array(
				'id' 			=> 'rpgc_from',
				'label' 		=> __( 'From', 'rpgiftcards' ),
				'placeholder' 	=> '',
				'description' 	=> __( 'Who is sending this gift card.', 'rpgiftcards' ),
			)
		);
		// From Email
		woocommerce_wp_text_input(
			array(
				'id' 			=> 'rpgc_email_from',
				'type'	 		=> 'email',
				'label' 		=> __( 'Email From', 'rpgiftcards' ),
				'placeholder' 	=> '',
				'description' 	=> __( 'What email account is sending this gift card.', 'rpgiftcards' ),
			)
		);
		
		do_action( 'rpgc_woocommerce_options_after_sender' );

		echo '</div><div class="panel woocommerce_options_panel">';

		echo '<h2>' . __('Personalize it',  'rpgiftcards' ) . '</h2>';
		
		do_action( 'rpgc_woocommerce_options_before_personalize' );
		
		// Amount
		woocommerce_wp_text_input(
			array(
				'id'     					=> 'rpgc_amount',
				'label'   					=> __( 'Gift Card Amount', 'rpgiftcards' ),
				'placeholder'  				=> '0.00',
				'description'  				=> __( 'Value of the Gift Card.', 'rpgiftcards' ),
				'type'    					=> 'number',
				'custom_attributes' 		=> array( 'step' => 'any', 'min' => '0' )
			)
		);
		if ( isset( $_GET['action']  ) ) {
			if ( $_GET['action'] == 'edit' ) {
				// Remaining Balance
				woocommerce_wp_text_input(
					array(
						'id'    			=> 'rpgc_balance',
						'label'    			=> __( 'Gift Card Balance', 'rpgiftcards' ),
						'placeholder'  		=> '0.00',
						'description'  		=> __( 'Remaining Balance of the Gift Card.', 'rpgiftcards' ),
						'type'    			=> 'number',
						'custom_attributes' => array( 'step' => 'any', 'min' => '0' )
					)
				);
			}
		}
		// Notes
		woocommerce_wp_textarea_input(
			array(
				'id' 						=> 'rpgc_note',
				'label' 					=> __( 'Gift Card Note', 'rpgiftcards' ),
				'description' 				=> __( 'Enter a message to your customer.', 'rpgiftcards' ),
				'class' 					=> 'short'
				
			)
		);

		// Expiry date
		woocommerce_wp_text_input(
			array(
				'id' 						=> 'rpgc_expiry_date',
				'label' 					=> __( 'Expiry date', 'rpgiftcards' ),
				'placeholder' 				=> _x( 'Never expire', 'placeholder', 'rpgiftcards' ),
				'description' 				=> __( 'The date this Gift Card will expire, <code>YYYY-MM-DD</code>.', 'rpgiftcards' ),
				'class' 					=> 'date-picker short',
				'custom_attributes' 		=> array( 'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" )
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
	public function rpgc_options_meta_box( $post ) {
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



	public function rpgc_info_meta_box( $post ) {
		global $wpdb;
		
		$data = get_post_meta( $post->ID );

		$orderCardNumber 	= wpr_get_order_card_number( $post->ID );
		$orderCardBalance 	= wpr_get_order_card_balance( $post->ID );
		$orderCardPayment 	= wpr_get_order_card_payment( $post->ID );
		$isAlreadyRefunded	= wpr_get_order_refund_status( $post->ID );
		
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
					
					if( ! empty( $isAlreadyRefunded ) )
						echo  '<br /><span style="color: #dd0000;">' . __( 'Gift card refunded ', 'rpgiftcards' ) . ' ' . woocommerce_price( $orderCardPayment ) . '</span>';
				echo '</div>';
			
			}

		echo '    </div>';
		echo '</div>';
	}

	public function wpr_giftcard_usage_data( $post ) {

		$giftcardIDs = get_post_meta( $post->ID, 'wpr_existingOrders_id', true );

		if( ! empty($giftcardIDs) ) {
		?>
			<div id="giftcard_usage" class="panel woocommerce_options_panel">
				<div class="options_group">
			
					<?php 
					foreach ($giftcardIDs as $giftID ) { 

						$giftcardPayment = wpr_get_order_card_payment( $giftID );
						$giftcarBalance = wpr_get_order_card_balance( $giftID );
						//$giftcarBalance -= $giftcardPayment;
						$orederLink = admin_url( 'post.php?post=' . $giftID . '&action=edit' );

					?>

						<div class="box-inside">
							<p>
								<strong><?php _e( 'Order Number:', 'rpgiftcards' ); ?></strong>&nbsp;
								<span><a href="<?php echo $orederLink; ?>"><?php echo esc_attr( $giftID ); ?></a></span>
								<br />
								<strong><?php _e( 'Amount Used:', 'rpgiftcards' ); ?></strong>&nbsp;
								<span><?php echo woocommerce_price( $giftcardPayment ); ?></span>
								<br />
								<strong><?php _e( 'Card Balance After Order:', 'rpgiftcards' ); ?></strong>&nbsp;
								<span><?php echo woocommerce_price( $giftcarBalance ); ?></span>
							</p>
						</div>

					<?php } ?>

				</div>
			</div>
			<?php
		} else {
			?>
			<div id="giftcard_usage" class="panel woocommerce_options_panel">
				<div class="options_group">
				<strong><?php _e( 'Gift card has not been used.', 'rpgiftcards' ); ?></strong>

				</div>
			</div>
			<?php
		}
	}

}

/**
 * Calls the class on the post edit screen.
 */
function call_WPR_Gift_Card_Meta() {
    new WPR_Gift_Card_Meta();
}