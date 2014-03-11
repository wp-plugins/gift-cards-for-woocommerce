<?php
/**
 * WooCommerce Gift Card Settings
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'RPGC_Settings' ) ) :

/**
 * WC_Settings_Accounts
 */
class RPGC_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'giftcard';
		$this->label = __( 'Gift Cards',  RPWCGC_CORE_TEXT_DOMAIN  );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		return apply_filters( 'woocommerce_' . $this->id . '_settings', array(

			array( 'type' 		=> 'sectionend', 'id' => 'giftcard_experation_options' ),

			array( 'title' 		=> __( 'Processesing Options',  RPWCGC_CORE_TEXT_DOMAIN  ), 'type' => 'title', 'id' => 'giftcard_processing_options_title' ),

			array(
				'title'         => __( 'Shipping Charge?',  RPWCGC_CORE_TEXT_DOMAIN  ),
				'desc'          => __( 'Allow customers to pay for shipping with their gift card.',  RPWCGC_CORE_TEXT_DOMAIN  ),
				'id'            => 'woocommerce_enable_giftcard_process',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false
			),

			array( 'type' => 'sectionend', 'id' => 'account_registration_options'),


		)); // End pages settings
	}
}

endif;

return new RPGC_Settings();