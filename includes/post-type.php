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
add_action( 'init', 'rpgc_create_post_type' );


/**
 * Define our custom columns shown in admin.
 * @param  string $column
 *
 */
function rpgc_add_columns( $columns ) {
    $new_columns = ( is_array( $columns ) ) ? $columns : array();
    unset( $new_columns['title'] );
    unset( $new_columns['date'] );
    unset( $new_columns['comments'] );

    //all of your columns will be added before the actions column on the Giftcard page

    $new_columns["title"]       = __( 'Giftcard Number', 'rpgiftcards' );
    $new_columns["amount"]      = __( 'Giftcard Amount', 'rpgiftcards' );
    $new_columns["balance"]     = __( 'Remaining Balance', 'rpgiftcards' );
    $new_columns["buyer"]       = __( 'Buyer', 'rpgiftcards' );
    $new_columns["recipient"]   = __( 'Recipient', 'rpgiftcards' );
    $new_columns["expiry_date"] = __( 'Expiry date', 'rpgiftcards' );

    $new_columns['comments']    = $columns['comments'];
    $new_columns['date']        = __( 'Creation Date', 'rpgiftcards' );

    return  apply_filters( 'rpgc_giftcard_columns', $new_columns);
}
add_filter( 'manage_edit-rp_shop_giftcard_columns', 'rpgc_add_columns' );



/**
 * Define our custom columns contents shown in admin.
 * @param  string $column
 *
 */
function rpgc_custom_columns( $column ) {
    global $post;

    $giftcardInfo = get_post_meta( $post->ID, '_wpr_giftcard', true );


    switch ( $column ) {

        case "buyer" :
            echo '<div><strong>' . esc_html( isset( $giftcardInfo[ 'from' ] ) ? $giftcardInfo[ 'from' ] : '' ) . '</strong><br />';
            echo '<span style="font-size: 0.9em">' . esc_html( isset( $giftcardInfo[ 'fromEmail' ] ) ? $giftcardInfo[ 'fromEmail' ] : '' ) . '</div>';
            break;

        case "recipient" :
            echo '<div><strong>' . esc_html( isset( $giftcardInfo[ 'to' ] ) ? $giftcardInfo[ 'to' ] : '' ) . '</strong><br />';
            echo '<span style="font-size: 0.9em">' . esc_html( isset( $giftcardInfo[ 'toEmail' ] ) ? $giftcardInfo[ 'toEmail' ] : '' ) . '</span></div>';
        break;

        case "amount" :
            $price = isset( $giftcardInfo[ 'amount' ] ) ? $giftcardInfo[ 'amount' ] : '';
            echo woocommerce_price( $price );
        break;

        case "balance" :
            $price = isset( $giftcardInfo[ 'balance' ] ) ? $giftcardInfo[ 'balance' ] : '';
            echo woocommerce_price( $price );
        break;

        case "expiry_date" :
            $expiry_date = isset( $giftcardInfo[ 'expiry_date' ] ) ? $giftcardInfo[ 'expiry_date' ] : '';

            if ( $expiry_date )
                echo esc_html( date_i18n( 'F j, Y', strtotime( $expiry_date ) ) );
            else
                echo '&ndash;';
        break;
    }
}
add_action( 'manage_rp_shop_giftcard_posts_custom_column', 'rpgc_custom_columns', 2 );



function wpfstop_change_default_title( $title ){

    $screen = get_current_screen();

    if ( 'rp_shop_giftcard' == $screen->post_type ){
        $title = 'Enter Gift Card Number Here';
    }

    return $title;
}

add_filter( 'enter_title_here', 'wpfstop_change_default_title' );

