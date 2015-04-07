<?php
/**
 * Scripts
 *
 * @package     Gift-Cards-for-Woocommerce
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function wpr_gift__admin_scripts( $hook ) {
    global $post_type;

    // Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    /**
     * @todo		This block loads styles or scripts explicitly on the
     *				EDD settings page.
     */
    
    if( $post_type == "rp_shop_giftcard" ) {
        wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );
        $jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

        wp_enqueue_script( 'woocommerce_writepanel' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-datepicker' );

        wp_enqueue_style( 'thickbox' ); // call to media files in wp
        wp_enqueue_script( 'thickbox' );

        wp_enqueue_script( 'wpr_gift__admin_js', RPWCGC_URL . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ) );
        wp_enqueue_style( 'wpr_gift__admin_css', RPWCGC_URL . '/assets/css/admin' . $suffix . '.css' );
    }
    
}
add_action( 'admin_enqueue_scripts', 'wpr_gift__admin_scripts', 100 );


/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function wpr_gift__scripts( $hook ) {
    // Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_enqueue_script( 'wpr_gift__js', RPWCGC_URL . '/assets/js/scripts' . $suffix . '.js', array( 'jquery' ) );
    wp_enqueue_style( 'wpr_gift__css', RPWCGC_URL . '/assets/css/styles' . $suffix . '.css' );
}
add_action( 'wp_enqueue_scripts', 'wpr_gift__scripts' );
