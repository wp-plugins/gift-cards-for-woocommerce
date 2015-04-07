<?php
/**
 * Gift Card Admin Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Settings_Accounts
 */
class RPGC_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'giftcard';
		$this->label = __( 'Gift Cards',  'rpgiftcards'  );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

		add_action( 'woocommerce_admin_field_addon_settings', array( $this, 'addon_setting' ) );
		add_action( 'woocommerce_admin_field_excludeProduct', array( $this, 'excludeProducts' ) );
	}


	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = apply_filters( 'woocommerce_add_section_giftcard', array( '' => __( 'Gift Card Options', 'rpgiftcards' ) ) );

		$premium = array( 'extensions' => __( 'Premium Extensions', 'rpgiftcards' ) );

		$sections = array_merge($sections, $premium);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

 		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}


	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$options = '';
		if( $current_section == '' ) {

			$options = apply_filters( 'woocommerce_giftcard_settings', array(

				array( 'title' 		=> __( 'Processing Options',  'rpgiftcards'  ), 'type' => 'title', 'id' => 'giftcard_processing_options_title' ),

				array(
					'title'         => __( 'Display on Cart?',  'rpgiftcards'  ),
					'desc'          => __( 'Display the giftcard form on the cart page.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_cartpage',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array(
					'title'         => __( 'Display on Checkout?',  'rpgiftcards'  ),
					'desc'          => __( 'Display the giftcard form on the checkout page.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_checkoutpage',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'autoload'      => false
				),


				array(
					'title'         => __( 'Require Recipient Information?',  'rpgiftcards'  ),
					'desc'          => __( 'Requires that your customers enter a name and email when purchasing a Gift Card.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_info_requirements',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),
				array(
					'title'         => __( 'Customize Add to Cart?',  'rpgiftcards'  ),
					'desc'          => __( 'Change Add to cart label and disable add to cart from product list.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_addtocart',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),
				array(
					'title'         => __( 'Physical Card?',  'rpgiftcards'  ),
					'desc'          => __( 'Select this if you would like to offer physical gift cards.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_physical',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),


				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

				array( 'title' 		=> __( 'Gift Card Uses',  'rpgiftcards'  ), 'type' => 'title', 'id' => 'giftcard_products_title' ),

				array(
					'title'         => __( 'Shipping',  'rpgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for shipping with their gift card.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_shipping',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),

				array(
					'title'         => __( 'Tax',  'rpgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for tax with their gift card.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_tax',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),

				array(
					'title'         => __( 'Fee',  'rpgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for fees with their gift card.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_fee',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),
				/*array(
					'title'         => __( 'Other Gift Cards',  'rpgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for gift cards with their existing gift card.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_giftcard',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'autoload'      => true
				),*/

				//array( 'type' => 'excludeProduct' ),

				/*array( 'type' => 'sectionend', 'id' => 'uses_giftcard_options'),

				array( 'title' 		=> __( 'Gift Card Email',  'rpgiftcards'  ), 'type' => 'title', 'id' => 'giftcard_email_title' ),

				array(
					'title'         => __( 'Email Message',  'rpgiftcards'  ),
					'desc'          => __( 'Change the email message that gets sent withyour gift card.',  'rpgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_custom_message',
					'default'       => '',
					'css'     		=> 'width:100%; height: 65px;',
					'type'          => 'textarea',
					'autoload'      => true
				),*/

				array( 'type' => 'sectionend', 'id' => 'email_giftcard_options'),

				array( 'title' 		=> __( 'Product Options',  'rpgiftcards'  ), 'type' => 'title', 'id' => 'giftcard_products_options_title' ),

				array(
					'name'     => __( 'To', 'rpgiftcards' ),
					'desc'     => __( 'This is the value that will display before a gift card number.', 'rpgiftcards' ),
					'id'       => 'woocommerce_giftcard_to',
					'std'      => 'To', // WooCommerce < 2.0
					'default'  => 'To', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'To Email', 'rpgiftcards' ),
					'desc'     => __( 'This is the value that will display before a gift card number.', 'rpgiftcards' ),
					'id'       => 'woocommerce_giftcard_toEmail',
					'std'      => 'Send To', // WooCommerce < 2.0
					'default'  => 'Send To', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'Note Option', 'rpgiftcards' ),
					'desc'     => __( 'This will change the placeholder field for the gift card note.', 'rpgiftcards' ),
					'id'       => 'woocommerce_giftcard_note',
					'std'      => 'Enter your note here.', // WooCommerce < 2.0
					'default'  => 'Enter your note here.', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'Gift Card Button Test', 'rpgiftcards' ),
					'desc'     => __( 'This is the text that will be displayed on the button to customize the information.', 'rpgiftcards' ),
					'id'       => 'woocommerce_giftcard_button',
					'std'      => 'Customize', // WooCommerce < 2.0
					'default'  => 'Customize', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

			));
		} else if( $current_section == 'extensions') {

			$options = array( 
				array( 'type' 	=> 'sectionend', 'id' => 'giftcard_extensions' ),

				array( 'type' => 'addon_settings' ),

			); // End pages settings
		}
		return apply_filters ('get_giftcard_settings', $options, $current_section );
	}





	/**
	 * Output the frontend styles settings.
	 */
	public function addon_setting() {
		
		if( $this->activatedPlugins() ) {
			register_setting( 'wpr-options', 'wpr_options' );
			?>
			<h3><?php _e('Activate Extensions', 'rpgiftcards' ); ?></h3> 
			<table>
			<?php do_action( 'wpr_add_license_field' ); ?>
			</table>
			<br class="clear" />
		
		<?php } ?>
		
		<h3><?php _e(' Premium features available', 'rpgiftcards' ); ?></h3>
		<p>
		<?php _e( 'You can now add additional functionallity to the gift card plugin using some of my premium plugins offered through', 'rpgiftcards' ); ?> <a href="wp-ronin.com">wp-ronin.com</a>.
		</p>
		<br class="clear" />
		<div class='wc_addons_wrap' style="margin-top:10px;">
		<ul class="products" style="overflow:hidden;">
		<?php

			$i = 0;
			$addons = array();

			if( ! class_exists( 'WPRWG_GiftCards_Pro' ) ) {
				$addons[$i]["title"] = __('Woocommerce Giftcards Pro', 'rpgiftcards' );
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = __( 'Get all the added features of the Pro gift card addon in this one package.', 'rpgiftcards' );
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/";
				$i++;
			}

			if( ! class_exists( 'WPRWG_Custom_Price' ) ) {
				$addons[$i]["title"] = __('Custom Price', 'rpgiftcards' );
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = __( 'Dont want to have to create multiple products to offer Gift Cards on your site.  Use this plugin to create a single product that allows your customers to put in the price.  Select 10 – 10000000 it wont matter.', 'rpgiftcards' );
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/woocommerce-gift-cards-custom-price/";
				$i++;
			}

			if( ! class_exists( 'WPRWG_Custom_Number' ) ) {
				$addons[$i]["title"] = __( 'Customize Card Number', 'rpgiftcards' );
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = __( 'Want to be able to customize the gift card number when it is created, this plugin will do it.', 'rpgiftcards' );
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/woocommerce-gift-cards-customize-gift-card/";
				$i++;
			}

			if( ! class_exists( 'WPRWG_Auto_Send' ) ) {
				$addons[$i]["title"] = __( 'Auto Send Card', 'rpgiftcards' );
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = __( 'Save time creating gift cards by using this plugin.  Enable it and customers will have their gift card sent out directly upon purchase or payment.', 'rpgiftcards' );
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/auto-send-email-woocommerce-gift-cards/";
				$i++;
			}
		
			if( ! class_exists( 'WPRWG_CSV_Importer' ) ) {
				$addons[$i]["title"] = __( 'CSV Importer', 'rpgiftcards' );
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = __( 'Import large number of gift cards with this extention. Use our supplied .', 'rpgiftcards' );
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/csvimporter/";
				$i++;
			}
			
			foreach ( $addons as $addon ) {
				echo '<li class="product" style="float:left; margin:0 1em 1em 0 !important; padding:0; vertical-align:top; width:300px;">';
				echo '<a href="' . $addon['link'] . '">';
				if ( ! empty( $addon['image'] ) ) {
					echo '<img src="' . $addon['image'] . '"/>';
				} else {
					echo '<h3>' . $addon['title'] . '</h3>';
				}
				echo '<p>' . $addon['excerpt'] . '</p>';
				echo '</a>';
				echo '</li>';
			}
		?>
		</ul>
		</div>
		<?php
	}

	public function activatedPlugins() {

		if( defined( 'WPR_GC_PRO_TEXT' ) || defined( 'RPWCGC_AUTO_CORE_TEXT_DOMAIN' ) || defined( 'WPR_CP_CORE_TEXT_DOMAIN' ) || defined( 'RPWCGC_CN_CORE_TEXT_DOMAIN' ) )
			return true;

		if( defined( 'WPR_GC_ACTIVE_PLUGIN' ) )
			return true;

		return false;

	}


	public function excludeProducts() {
		?>
			<tr valign="top" class="">
				<th class="titledesc" scope="row">
					<?php _e( 'Exclude products', 'rpgiftcards' ); ?>
					<img class="help_tip" data-tip='<?php _e( 'Products which gift cards can not be used on', 'rpgiftcards' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
				</th>
					<td class="forminp forminp-checkbox">
					<fieldset>
						<input type="hidden" class="wc-product-search" data-multiple="true" style="width: 50%;" name="exclude_product_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', 'rpgiftcards' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php
							$product_ids = array_filter( array_map( 'absint', explode( ',', get_option( 'exclude_product_ids' ) ) ) );
							$json_ids    = array();

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
							}

							echo esc_attr( json_encode( $json_ids ) );
						?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
					</fieldset>
				</td>
			</tr>
		<?php

	}

}


return new RPGC_Settings();
