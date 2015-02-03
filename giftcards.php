<?php
/*
Plugin Name: WooCommerce - Gift Cards
Plugin URI: http://wp-ronin.com
Description: WooCommerce - Gift Cards allows you to offer gift cards to your customer and allow them to place orders using them.
Version: 1.6.6
Author: WP Ronin
Author URI: http://wp-ronin.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Plugin version

class WPRWooGiftcards {
	private static $wpr_wg_instance;

	/**
	 * Get the singleton instance of our plugin
	 * @return class The Instance
	 * @access public
	 */
	public static function getInstance() {

		if ( !self::$wpr_wg_instance ) {
			self::$wpr_wg_instance = new WPRWooGiftcards();
			self::$wpr_wg_instance->setup_constants();
            self::$wpr_wg_instance->includes();
            self::$wpr_wg_instance->rpwcgc_loaddomain();
            self::$wpr_wg_instance->hooks();
		}

		return self::$wpr_wg_instance;
	}

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function setup_constants() {

		define( 'RPWCGC_VERSION', '1.6.6' );

		// Plugin Folder Path
		define( 'RPWCGC_PATH', plugin_dir_path( __FILE__ ) );

		// Plugin Folder URL
		define( 'RPWCGC_URL', plugins_url( 'gift-cards-for-woocommerce', 'giftcards.php' ) );

		// Plugin Root File
		define( 'RPWCGC_FILE', plugin_basename( __FILE__ )  );
		
		if ( ! defined( 'WPR_STORE_URL' ) )
			// Premium Plugin Store
			define( 'WPR_STORE_URL', 'https://wp-ronin.com' );

	}


    /**
     * Include necessary files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {
        // Include scripts
		if( is_admin() ) {
			// Create all admin functions and pages
			require_once RPWCGC_PATH . 'admin/giftcard-columns.php';  
			require_once RPWCGC_PATH . 'admin/giftcard-metabox.php';  
			require_once RPWCGC_PATH . 'admin/giftcard-functions.php';
			
		}

		require_once RPWCGC_PATH . 'giftcard/giftcard-product.php';
		require_once RPWCGC_PATH . 'giftcard/giftcard-forms.php';
		require_once RPWCGC_PATH . 'giftcard/giftcard-checkout.php';
		require_once RPWCGC_PATH . 'giftcard/giftcard-paypal.php';
		require_once RPWCGC_PATH . 'giftcard/giftcard-shortcodes.php';

		require_once RPWCGC_PATH . 'giftcard/giftcard-functions.php';
		require_once RPWCGC_PATH . 'giftcard/giftcard-meta.php';

    }


    /**
     * Run action and filter hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     *
     */
    private function hooks() {

    	global $wpr_woo_giftcard_settings;
		$wpr_woo_giftcard_settings = get_option( 'wpr_wg_options' );

		add_action( 'init', array( $this, 'rpgc_create_post_type' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'rpgc_add_settings_page'), 10, 1);
		add_action( 'enqueue_scripts', array( $this, 'load_styes' ) );

		if ( ! class_exists( 'WooCommerce' ) )
			add_action( 'admin_notices', array( $this, 'no_woo_nag' ) );

		if( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_scripts' ), 99 );
				
		}


    }

	/**
	 * Queue up the JavaScript file for the admin page, only on our admin page
	 * @param  string $hook The current page in the admin
	 * @return void
	 * @access public
	 */
	public function load_custom_scripts( $hook ) {
		global $woocommerce;

		if ( 'rp_shop_giftcard' != $hook && 'post-new.php' != $hook && 'post.php' != $hook )
			return;

		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );
		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

		wp_enqueue_script( 'woocommerce_writepanel' );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		
	}
	
	public function load_styes() {
		wp_register_style( 'wpr_style', RPWCGC_PATH . 'style/style.css', false, RPWCGC_VERSION );
		wp_enqueue_style( 'wpr_style' );
	}

	public function rpgc_add_settings_page( $settings ) {
		$settings[] = include( RPWCGC_PATH . 'admin/giftcard-settings.php' );

		return apply_filters( 'rpgc_setting_classes', $settings );
	}
	
	public function rpgc_create_post_type() {
		$show_in_menu = current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true;

		register_post_type( 'rp_shop_giftcard',
			array(
				'labels' => array(
					'name'      			=> __( 'Gift Cards', 'rpgiftcards' ),
					'singular_name'			=> __( 'Gift Card', 'rpgiftcards' ),
					'menu_name'    			=> _x( 'Gift Cards', 'Admin menu name', 'rpgiftcards' ),
					'add_new'     			=> __( 'Add Gift Card', 'rpgiftcards' ),
					'add_new_item'    		=> __( 'Add New Gift Card', 'rpgiftcards' ),
					'edit'      			=> __( 'Edit', 'rpgiftcards' ),
					'edit_item'    			=> __( 'Edit Gift Card', 'rpgiftcards' ),
					'new_item'     			=> __( 'New Gift Card', 'rpgiftcards' ),
					'view'      			=> __( 'View Gift Cards', 'rpgiftcards' ),
					'view_item'    			=> __( 'View Gift Card', 'rpgiftcards' ),
					'search_items'    		=> __( 'Search Gift Cards', 'rpgiftcards' ),
					'not_found'    			=> __( 'No Gift Cards found', 'rpgiftcards' ),
					'not_found_in_trash'	=> __( 'No Gift Cards found in trash', 'rpgiftcards' ),
					'parent'     			=> __( 'Parent Gift Card', 'rpgiftcards' )
					),

				'public'  				=> true,
				'has_archive' 			=> true,
				'publicly_queryable'	=> false,
				'exclude_from_search'	=> false,
				'show_in_menu' 	 		=> $show_in_menu,
				'hierarchical' 			=> false,
				'supports'   			=> array( 'title', 'comments' )
			)
		);
	
		register_post_status( 'zerobalance', array(
			'label'                     => __( 'Zero Balance', 'rpgiftcards' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Zero Balance <span class="count">(%s)</span>', 'Zero Balance <span class="count">(%s)</span>' )
		) );
		
	}
	
	/**
	 * Load the Text Domain for i18n
	 * @return void
	 * @access public
	 */
	public function rpwcgc_loaddomain() {
		load_plugin_textdomain( 'rpgiftcards', false, 'gift-cards-for-woocommerce/languages/' );
	}

	/**
	 * If no license key is saved, show a notice
	 * @return void
	 */
	public function no_woo_nag() {
		?>
		<div class="updated">
			<p><?php printf( __( 'WooCommerce - Gift Cards requires that you have WooCommerce Installed and Activated. <a href="%s">Activate Now</a>.', 'rpgiftcards' ), admin_url( 'plugins.php' ) ); ?></p>
		</div>
		<?php
	}

}




function createGiftcard () {


	$wpr_woo_gift_loaded = WPRWooGiftcards::getInstance();


}
add_action( 'plugins_loaded', 'createGiftcard', 12 );

