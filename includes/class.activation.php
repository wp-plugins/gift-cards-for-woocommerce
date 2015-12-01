<?php
/**
 * Activation handler
 *
 * @package     Woo Gift Cards\ActivationHandler
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * EDD Extension Activation Handler Class
 *
 * @since       1.0.0
 */
class WPR_Giftcard_Activation {

    public $plugin_name, $plugin_path, $plugin_file, $has_woo, $wpr_base;

    /**
     * Setup the activation class
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function __construct( $plugin_path, $plugin_file ) {
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
    }


    /**
     * Process plugin deactivation
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function run() {
        // Display notice
        add_action( 'admin_notices', array( $this, 'no_woo_nag' ) );
    }


    /**
     * If no license key is saved, show a notice
     * @return void
     */
    public function no_woo_nag() {

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
