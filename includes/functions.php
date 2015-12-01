<?php
/**
 * Helper Functions
 *
 * @package     WPR\PluginName\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;





function wpr_upgrade_notice() {
	$wpr_gift_version = get_option( 'wpr_gift_version' );

	if ( ! $wpr_gift_version ) {
		// 2.0.0 is the first version to use this option so we must add it
		$wpr_gift_version = RPWCGC_VERSION;
	}

	$wpr_gift_version = preg_replace( '/[^0-9.].*/', '', $wpr_gift_version );

	if ( version_compare( $wpr_gift_version, '2.0.0', '<' ) ) {
    	printf(
			'<div class="error"><p>' . esc_html__( 'Woocommerce - Gift Cards has been updated please backup your database and run the database updater %shere%s. Gift cards will not work until updated.', 'rpgiftcards' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=giftcard&section=upgrades' ) ) . '">',
			'</a>'
		);
	    
	}
}
add_action( 'admin_notices', 'wpr_upgrade_notice' );


