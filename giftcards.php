<?php
/*
Plugin Name: WooCommerce - Gift Cards
Plugin URI: http://wp-ronin.com
Description: WooCommerce - Gift Cards allows you to offer gift cards to your customer and allow them to place orders using them.
Version: 1.3.3
Author: Ryan Pletcher
Author URI: http://ryanpletcher.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Plugin version
if ( ! defined( 'RPWCGC_VERSION' ) )
	define( 'RPWCGC_VERSION', '1.3.3' );

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
	
		register_post_status( 'zerobalance', array(
			'label'                     => __( 'Zero Balance', RPWCGC_CORE_TEXT_DOMAIN ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Zero Balance <span class="count">(%s)</span>', 'Zero Balance <span class="count">(%s)</span>' )
		) );
		
	}
	add_action( 'init', 'rpgc_create_post_type' );


	function rp_append_post_status_list(){
	     global $post;
	     $complete = '';
	     $label = '';
	     if($post->post_type == 'rp_shop_giftcard'){
	          if($post->post_status == 'zerobalance'){
	               $complete = ' selected=\"selected\"';
	               $label = '<span id=\"post-status-display\">' . _e( 'Zero Balanace', RPWCGC_CORE_TEXT_DOMAIN ) . '</span>';
	          }

	          echo '
	          <script>
	          jQuery(document).ready(function($){
	               $("select#post_status").append("<option value=\"zerobalance\" '.$complete.'>';
	               	_e( 'Zero Balanace', RPWCGC_CORE_TEXT_DOMAIN );
	           echo '</option>");
	               $(".misc-pub-section label").append("'.$label.'");
	          });
	          </script>
	          ';
	     }
	}
	add_action('admin_footer-post.php', 'rp_append_post_status_list');

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

	/**
	 * Load the Text Domain for i18n
	 * @return void
	 * @access public
	 */
	function rpwcgc_loaddomain() {
		load_plugin_textdomain( RPWCGC_CORE_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'rpwcgc_loaddomain' );

}
add_action( 'plugins_loaded', 'rpgc_woocommerce', 0 );