<?php
/**
 * Plugin Name: WooCommerce - Gift Cards
 * Plugin URI: http://wp-ronin.com
 * Description: WooCommerce - Gift Cards allows you to offer gift cards to your customer and allow them to place orders using them.
 * Version: 1.7.1
 * Author: WP Ronin
 * Author URI: http://wp-ronin.com
 * License: GPL2
 *
 * 
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
            // Plugin version
        define( 'RPWCGC_VERSION', '1.7.1' );

        // Plugin Folder Path
        define( 'RPWCGC_DIR', plugin_dir_path( __FILE__ ) );

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
            require_once RPWCGC_DIR . 'includes/scripts.php';
            require_once RPWCGC_DIR . 'includes/functions.php';
            require_once RPWCGC_DIR . 'includes/post-type.php';

            // Include scripts
            if( is_admin() ) {
                // Create all admin functions and pages
                require_once RPWCGC_DIR . 'includes/admin/giftcard-columns.php';  
                require_once RPWCGC_DIR . 'includes/admin/giftcard-metabox.php';  
                require_once RPWCGC_DIR . 'includes/admin/giftcard-save.php';
                
            }

            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-product.php';
            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-forms.php';
            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-checkout.php';
            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-paypal.php';
            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-shortcodes.php';

            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-functions.php';
            require_once RPWCGC_DIR . 'includes/giftcard/giftcard-meta.php';
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
            
            if ( ! class_exists( 'WooCommerce' ) )
                add_action( 'admin_notices', array( $this, 'no_woo_nag' ) );
            
            $wpr_woo_giftcard_settings = get_option( 'wpr_wg_options' );

            add_action( 'init', 'rpgc_create_post_type' );
            add_filter( 'woocommerce_get_settings_pages', array( $this, 'rpgc_add_settings_page'), 10, 1);
            add_action( 'enqueue_scripts', array( $this, 'load_styes' ) );

            

            if( is_admin() ) {
                //add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_scripts' ), 99 );
                    
            }

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
            $lang_dir = RPWCGC_DIR . '/languages/';
            $lang_dir = apply_filters( 'giftcards_for_woocommerce_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'gift-cards-for-woocommerce' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'gift-cards-for-woocommerce', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;

            if( file_exists( $mofile_local ) ) {
                
                load_textdomain( 'gift-cards-for-woocommerce', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'gift-cards-for-woocommerce', false, $lang_dir );
            }
        }

        public function rpgc_add_settings_page( $settings ) {
            $settings[] = include( RPWCGC_DIR . 'includes/admin/giftcard-settings.php' );

            return apply_filters( 'rpgc_setting_classes', $settings );
        }


        /**
         * If no license key is saved, show a notice
         * @return void
         */
        public function no_woo_nag() {
             // We need plugin.php!
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            
            $plugins = get_plugins();
            
            // Set plugin directory
            $plugin_path = array_filter( explode( '/', $plugin_path ) );
            $this->plugin_path = end( $plugin_path );
            
            // Set plugin file
            $this->plugin_file = $plugin_file;
            
            // Set plugin name
            $this->plugin_name = 'WooCommerce - Gift Cards';
            
            // Is EDD installed?
            foreach( $plugins as $plugin_path => $plugin ) {
                
                if( $plugin['Name'] == 'WooCommerce' ) {
                    $this->has_woo = true;
                    $this->wpr_base = $plugin_path;
                    break;
                }
            }

            if( $this->has_woo ) {
                $url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $this->wpr_base ), 'activate-plugin_' . $this->wpr_base ) );
                $link = '<a href="' . $url . '">' . __( 'activate it', 'rpgiftcards' ) . '</a>';
            } else {
                $url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' ) );
                $link = '<a href="' . $url . '">' . __( 'install it', 'rpgiftcards' ) . '</a>';
            }
            
            echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires WooCommerce! Please %s to continue!', 'rpgiftcards' ), $link ) . '</p></div>';
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

    return WPRWooGiftcards::instance();
    
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
