<?php
/**
 * Plugin Name: WooCommerce - Gift Cards
 * Plugin URI: http://wp-ronin.com
 * Description: WooCommerce - Gift Cards allows you to offer gift cards to your customer and allow them to place orders using them.
 * Version: 2.1.1
 * Author: WP Ronin
 * Author URI: http://wp-ronin.com
 * License: GPL2
 * 
 * Text Domain:     rpgiftcards
 *
 * @package         Gift-Cards-for-Woocommerce
 * @author          Ryan Pletcher
 * @copyright       Copyright (c) 2015
 *
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'WPRWooGiftcards' ) ) {

    /**
     * Main WPRWooGiftcards class
     *
     * @since       1.0.0
     */
    class WPRWooGiftcards {

        /**
         * @var         WPRWooGiftcards $instance The one true WPRWooGiftcards
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true WPRWooGiftcards
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new WPRWooGiftcards();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            
            define( 'RPWCGC_VERSION',   '2.1.1' ); // Plugin version
            define( 'RPWCGC_DIR',       plugin_dir_path( __FILE__ ) ); // Plugin Folder Path
            define( 'RPWCGC_URL',       plugins_url( 'gift-cards-for-woocommerce', 'giftcards.php' ) ); // Plugin Folder URL
            define( 'RPWCGC_FILE',      plugin_basename( __FILE__ )  ); // Plugin Root File
            
            if ( ! defined( 'WPR_STORE_URL' ) )
                define( 'WPR_STORE_URL', 'https://wp-ronin.com' ); // Premium Plugin Store
        
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
            require_once RPWCGC_DIR . 'includes/scripts.php';
            require_once RPWCGC_DIR . 'includes/functions.php';
            require_once RPWCGC_DIR . 'includes/post-type.php';

            require_once RPWCGC_DIR . 'includes/admin/metabox.php';

            if( ! class_exists( 'WPR_Giftcard' ) ) {
                require_once RPWCGC_DIR . 'includes/class.giftcard.php';
            }

            if( ! class_exists( 'WPR_Giftcard_Email' ) ) {
                require_once RPWCGC_DIR . 'includes/class.giftcardemail.php';
            }
            
            require_once RPWCGC_DIR . 'includes/giftcard-product.php';
            require_once RPWCGC_DIR . 'includes/giftcard-checkout.php';

            require_once RPWCGC_DIR . 'includes/giftcard-meta.php';
            
            require_once RPWCGC_DIR . 'includes/shortcodes.php';

            // require_once RPWCGC_DIR . 'includes/widgets.php';
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
            // Register settings
            $wpr_woo_giftcard_settings = get_option( 'wpr_wg_options' );

            add_filter( 'woocommerce_get_settings_pages', array( $this, 'rpgc_add_settings_page'), 10, 1);
            add_filter( 'woocommerce_calculated_total', array( 'WPR_Giftcard', 'wpr_discount_total'), 10, 2 );

        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = RPWCGC_DIR . 'languages/';
            $lang_dir = apply_filters( 'giftcards_for_woocommerce_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'rpgiftcards' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'rpgiftcards', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;

            if( file_exists( $mofile_local ) ) {
                load_textdomain( 'rpgiftcards', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'rpgiftcards', false, $lang_dir );
            }
        }

        public function rpgc_add_settings_page( $settings ) {

            require_once RPWCGC_DIR . 'includes/class.settings.php';

            $settings[] = new RPGC_Settings();

            return apply_filters( 'rpgc_setting_classes', $settings );
        }

    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true WPRWooGiftcards
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \WPRWooGiftcards The one true WPRWooGiftcards
 *
 */
function WPRWooGiftcards_load() {
    if( ! class_exists( 'WooCommerce' ) ) {
        if( ! class_exists( 'WPR_Giftcard_Activation' ) ) {
            require_once 'includes/class.activation.php';
        }

        $activation = new WPR_Giftcard_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
        
        //return WPRWooGiftcards::instance();
    } else {
        return WPRWooGiftcards::instance();
    }

}
add_action( 'plugins_loaded', 'WPRWooGiftcards_load' );


/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0.0
 * @return      void
 */
function wpr_giftcard_activation() {
    /* Activation functions here */
}
register_activation_hook( __FILE__, 'wpr_giftcard_activation' );
