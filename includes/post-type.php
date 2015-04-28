<?php
/**
 * Setup Post Type
 *
 * @package     Gift Cards For WooCommerce
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function rpgc_create_post_type() {
    $show_in_menu = current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true;

    register_post_type( 'rp_shop_giftcard',
        array(
            'labels' => array(
                'name'                  => __( 'Gift Cards', 'rpgiftcards' ),
                'singular_name'         => __( 'Gift Card', 'rpgiftcards' ),
                'menu_name'             => _x( 'Gift Cards', 'Admin menu name', 'rpgiftcards' ),
                'add_new'               => __( 'Add Gift Card', 'rpgiftcards' ),
                'add_new_item'          => __( 'Add New Gift Card', 'rpgiftcards' ),
                'edit'                  => __( 'Edit', 'rpgiftcards' ),
                'edit_item'             => __( 'Edit Gift Card', 'rpgiftcards' ),
                'new_item'              => __( 'New Gift Card', 'rpgiftcards' ),
                'view'                  => __( 'View Gift Cards', 'rpgiftcards' ),
                'view_item'             => __( 'View Gift Card', 'rpgiftcards' ),
                'search_items'          => __( 'Search Gift Cards', 'rpgiftcards' ),
                'not_found'             => __( 'No Gift Cards found', 'rpgiftcards' ),
                'not_found_in_trash'    => __( 'No Gift Cards found in trash', 'rpgiftcards' ),
                'parent'                => __( 'Parent Gift Card', 'rpgiftcards' )
                ),

            'public'                => true,
            'has_archive'           => true,
            'publicly_queryable'    => false,
            'exclude_from_search'   => false,
            'show_in_menu'          => $show_in_menu,
            'hierarchical'          => false,
            'supports'              => array( 'title', 'comments' )
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



