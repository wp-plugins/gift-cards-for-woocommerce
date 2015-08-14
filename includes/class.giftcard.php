<?php
/**
 * Gift Card handler
 *
 * @package     Woo Gift Cards\GiftCardHandler
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Gift Card Handler Class
 *
 * @since       1.0.0
 */
class WPR_Giftcard {

    public $giftcard;

    /**
     * Setup the activation class
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function __construct(  ) {

    }


    // Function to create the gift card
    public function createCard( $giftInformation ) {
        global $wpdb;

        if ( isset( $giftInformation['rpgc_description'] ) ) {
            $giftCard['description']    = woocommerce_clean( $giftInformation['rpgc_description'] );
            
        }
        if ( isset( $giftInformation['rpgc_to'] ) ) {
            $giftCard['to'] = woocommerce_clean( $giftInformation['rpgc_to'] );
            
        }
        if ( isset( $giftInformation['rpgc_email_to'] ) ) {
            $giftCard['toEmail']        = woocommerce_clean( $giftInformation['rpgc_email_to'] );
            
        }
        if ( isset( $giftInformation['rpgc_from'] ) ) {
            $giftCard['from']           = woocommerce_clean( $giftInformation['rpgc_from'] );
        }
        if ( isset( $giftInformation['rpgc_email_from'] ) ) {
            $giftCard['fromEmail']      = woocommerce_clean( $giftInformation['rpgc_email_from'] );
        }
        if ( isset( $giftInformation['rpgc_amount'] ) ) {
            $giftCard['amount']         = woocommerce_clean( $giftInformation['rpgc_amount'] );

            if ( ! isset( $giftInformation['rpgc_balance'] ) ) {
                $giftCard['balance']    = woocommerce_clean( $giftInformation['rpgc_amount'] );
                $giftCard['sendTheEmail'] = 1;
            }
        }
        if ( isset( $giftInformation['rpgc_balance'] ) ) {
            $giftCard['balance']   = woocommerce_clean( $giftInformation['rpgc_balance'] );
            
        }
        if ( isset( $giftInformation['rpgc_note'] ) ) {
            $giftCard['note']   = woocommerce_clean( $giftInformation['rpgc_note'] );
            
        }
        if ( isset( $giftInformation['rpgc_expiry_date'] ) ) {
            $giftCard['expiry_date'] = woocommerce_clean( $giftInformation['rpgc_expiry_date'] );
            
        } else {
            $giftCard['expiry_date'] = '';
        }
        
        if ( ( $_POST['post_title'] == '' ) || isset( $giftInformation['rpgc_regen_number'] ) ){
            if ( ( $giftInformation['rpgc_regen_number'] == 'yes' ) || ( $_POST['post_title'] == '' ) ) {
                $newNumber = apply_filters( 'rpgc_regen_number', $this->generateNumber());

                $wpdb->update( $wpdb->posts, array( 'post_title' => $newNumber ), array( 'ID' => $_POST['ID'] ) );
                $wpdb->update( $wpdb->posts, array( 'post_name' => $newNumber ), array( 'ID' => $_POST['ID'] ) );
            }

        }

        if( ( ( $giftCard['sendTheEmail'] == 1 ) && ( $giftCard['balance'] <> 0 ) ) || isset( $giftInformation['rpgc_resend_email'] ) ) {            

            $email = new WPR_Giftcard_Email();
            $post = get_post( $_POST['ID'] );
            $email->sendEmail ( $post );
        

        }

        update_post_meta( $_POST['ID'], '_wpr_giftcard', $giftCard );

    }

    public function sendCard() {


    }

    // Function to generate the gift card number for the card
    public function generateNumber( ){

        $randomNumber = substr( number_format( time() * rand(), 0, '', '' ), 0, 15 );

        return apply_filters('rpgc_generate_number', $randomNumber);

    }

    // Function to check if a product is a gift card
    public static function wpr_is_giftcard( $giftcard_id ) {

        $giftcard = get_post_meta( $giftcard_id, '_giftcard', true );

        if ( $giftcard != 'yes' ) {
            return false;
        }

        return true;

    }


    public static function wpr_get_giftcard_by_code( $value = '' ) {
        global $wpdb;

        // Check for Giftcard
        $giftcard_found = $wpdb->get_var( $wpdb->prepare( "
            SELECT $wpdb->posts.ID
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
            AND $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_title = '%s'
        ", $value ) );

        return $giftcard_found;

    }

    public function wpr_get_payment_amount( ){
        $giftcard_id    = WC()->session->giftcard_post;
        $cart           = WC()->session->cart;

        if ( isset( $giftcard_id ) ) {
            $balance = wpr_get_giftcard_balance( $giftcard_id );

            $charge_shipping    = get_option('woocommerce_enable_giftcard_charge_shipping');
            $charge_tax         = get_option('woocommerce_enable_giftcard_charge_tax');
            $charge_fee         = get_option('woocommerce_enable_giftcard_charge_fee');
            $exclude_product    = array_filter( array_map( 'absint', explode( ',', get_option( 'wpr_giftcard_exclude_product_ids' ) ) ) );

            $giftcardPayment = 0;

            foreach( $cart as $key => $product ) {

                if( ! in_array( $product['product_id'], $exclude_product ) ) {

                    if( $charge_tax == 'yes' ){
                        $giftcardPayment += $product['line_total'];
                        $giftcardPayment += $product['line_tax'];
                    } else {
                        $giftcardPayment += $product['line_total'];
                    }
                }
            }
            
            if( $charge_shipping == 'yes' ) {
                $giftcardPayment += WC()->session->shipping_total;                
            }

            if( $charge_tax == "yes" ) {
                $giftcardPayment += WC()->session->tax_total;

                if( $charge_shipping == 'yes' ) {
                    $giftcardPayment += WC()->session->shipping_tax_total;
                }
            }

            if( $charge_fee == "yes" ) {
                $giftcardPayment += WC()->session->fee_total;
            }

            if ( $giftcardPayment <= $balance ) {
                $display = $giftcardPayment;
            } else {
                $display = $balance;
            }
            return $display;
        }
        
    }


    public function wpr_decrease_balance( $giftCard_id ) {

        $newBalance = wpr_get_giftcard_balance( $giftCard_id ) - $this->wpr_get_payment_amount();

        wpr_set_giftcard_balance( $giftCard_id, $newBalance );
        // Check if the gift card ballance is 0 and if it is change the post status to zerobalance
        if( wpr_get_giftcard_balance( $giftCard_id ) == 0 )
            wpr_update_giftcard_status( $giftCard_id, 'zerobalance' );



    }


    public static function wpr_discount_total( $total, $cart ) {

        $giftcard = new WPR_Giftcard();

        $discount = $giftcard->wpr_get_payment_amount();

        $total -= $discount;

        //WC()->session->discount_cart = $discount;

        return $total;
    }



}