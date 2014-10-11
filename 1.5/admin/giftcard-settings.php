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

			$options = apply_filters( 'woocommerce_giftcard_settings', array(

				array( 'title' 		=> __( 'Processing Options',  RPWCGC_CORE_TEXT_DOMAIN  ), 'type' => 'title', 'id' => 'giftcard_processing_options_title' ),

				array(
					'title'         => __( 'Display on Cart?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Display the giftcard form on the cart page.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_giftcard_cartpage',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array(
					'title'         => __( 'Display on Checkout?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Display the giftcard form on the checkout page.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_giftcard_checkoutpage',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array(
					'title'         => __( 'Shipping Charge?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Allow customers to pay for shipping with their gift card.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_giftcard_process',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),
				array(
					'title'         => __( 'Customize Add to Cart?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Change Add to cart label and disable add to cart from product list.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_addtocart',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),
				array(
					'title'         => __( 'Physical Card?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Select this if you would like to offer physical gift cards.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_physical',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

				array( 'title' 		=> __( 'Product Options',  RPWCGC_CORE_TEXT_DOMAIN  ), 'type' => 'title', 'id' => 'giftcard_products_options_title' ),

				array(
					'name'     => __( 'To', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This is the value that will display before a gift card number.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_to',
					'std'      => 'To', // WooCommerce < 2.0
					'default'  => 'To', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'To Email', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This is the value that will display before a gift card number.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_toEmail',
					'std'      => 'Send To', // WooCommerce < 2.0
					'default'  => 'Send To', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'Note Option', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This will change the placeholder field for the gift card note.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_note',
					'std'      => 'Enter your note here.', // WooCommerce < 2.0
					'default'  => 'Enter your note here.', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'Gift Card Button Test', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This is the text that will be displayed on the button to customize the information.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_button',
					'std'      => 'Customize', // WooCommerce < 2.0
					'default'  => 'Customize', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

			));


			$extensions = array( 
				array (
					'title' 	=> __( 'Premium features available', RPWCGC_CORE_TEXT_DOMAIN),
					'type' 		=> 'title', 
					'desc' 		=> sprintf(__( 'You can now add additional functionallity to the gift card plugin using some of my premium plugins offered through %s.  If you are looking for some functionality that I have not created let me know and I would be happy to look into offering it in the future.  I also have a support forum for my premium plugins and the ones offered on Wordpress.org.  Please let me know if I can help with anything.', RPWCGC_CORE_TEXT_DOMAIN ), '<a href="wp-ronin.com">wp-ronin.com</a>'), 
					'id' 		=> 'rpgc_extra_features' 
				),

				array( 'type' 	=> 'sectionend', 'id' => 'giftcard_extensions' ),

			); // End pages settings

			$options = array_merge ($options, $extensions);

			return $options;

		}
	}

endif;

return new RPGC_Settings();
