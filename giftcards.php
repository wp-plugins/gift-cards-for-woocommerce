<?php
/*
Plugin Name: Gift Cards for WooCommerce
Plugin URI: http://ryanpletcher.com
Description: Gift Cards for WooCommerce allows you to offer gift cards to your customer and allow them to place orders using them.
Version: 1.3.2
Author: Ryan Pletcher
Author URI: http://ryanpletcher.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Plugin version
if ( ! defined( 'RPWCGC_VERSION' ) )
	define( 'RPWCGC_VERSION', '1.3.1' );

// Plugin Folder Path
if ( ! defined( 'RPWCGC_PATH' ) )
	define( 'RPWCGC_PATH', plugin_dir_path( __FILE__ ) );

// Plugin Folder URL
if ( ! defined( 'RPWCGC_URL' ) )
	define( 'RPWCGC_URL', plugins_url( 'gift-cards-for-woocommerce', 'giftcards.php' ) );

// Plugin Root File
if ( ! defined( 'RPWCGC_FILE' ) )
	define( 'RPWCGC_FILE', plugin_basename( __FILE__ )  );

// Plugin Text Domian
if ( ! defined( 'RPWCGC_CORE_TEXT_DOMAIN' ) )
	define( 'RPWCGC_CORE_TEXT_DOMAIN', 'rpgiftcards');


function rpgc_woocommerce() {

	if ( !class_exists( 'woocommerce' ) )
		return;

	if ( is_admin() ) {
		require_once RPWCGC_PATH . 'admin/giftcard-actions.php';
		require_once RPWCGC_PATH . 'admin/order-functions.php';
	}

	require_once RPWCGC_PATH . 'giftcard/giftcard-functions.php';
	require_once RPWCGC_PATH . 'giftcard/product-functions.php';
	require_once RPWCGC_PATH . 'giftcard/checkout-functions.php';

	function rpgc_create_post_type() {
		$show_in_menu = current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true;

		register_post_type( 'rp_shop_giftcard',
			array(
				'labels' => array(
					'name'      			=> __( 'Gift Cards', RPWCGC_CORE_TEXT_DOMAIN ),
					'singular_name'			=> __( 'Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'menu_name'    			=> _x( 'Gift Cards', 'Admin menu name', RPWCGC_CORE_TEXT_DOMAIN ),
					'add_new'     			=> __( 'Add Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'add_new_item'    		=> __( 'Add New Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'edit'      			=> __( 'Edit', RPWCGC_CORE_TEXT_DOMAIN ),
					'edit_item'    			=> __( 'Edit Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'new_item'     			=> __( 'New Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'view'      			=> __( 'View Gift Cards', RPWCGC_CORE_TEXT_DOMAIN ),
					'view_item'    			=> __( 'View Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'search_items'    		=> __( 'Search Gift Cards', RPWCGC_CORE_TEXT_DOMAIN ),
					'not_found'    			=> __( 'No Gift Cards found', RPWCGC_CORE_TEXT_DOMAIN ),
					'not_found_in_trash'	=> __( 'No Gift Cards found in trash', RPWCGC_CORE_TEXT_DOMAIN ),
					'parent'     			=> __( 'Parent Gift Card', RPWCGC_CORE_TEXT_DOMAIN )
				),
				'public'  		=> true,
				'has_archive' 	=> true,
				'show_in_menu'  => $show_in_menu,
				'hierarchical' 	=> false,
				'supports'   	=> array( 'title', 'comments' )
			)
		);
	}
	add_action( 'init', 'rpgc_create_post_type' );

	/**	
	 * Add the required scripts to the plugin.
	 *
	 */
	function rpgc_enqueue() {
		global $woocommerce, $post;
		$rpgc_url = plugins_url() . '/gift-cards-for-woocommerce';
		wp_enqueue_style( 'rpgc_style', RPWCGC_URL . '/style/style.css' );
	}
	add_action( 'wp_enqueue_scripts', 'rpgc_enqueue' );


}
add_action( 'plugins_loaded', 'rpgc_woocommerce', 0 );