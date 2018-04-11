<?php
/**
 * Plugin Name: CUSTOM CART LOGIC
 * Decription: Adds custom price calculating logic
 * Version: 20180905
 * Author: v.adinyaev@inostudio.com
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ccl-price-calculator.php';

    add_action( 'woocommerce_before_cart', 'ccl_calculate_discount' );
    add_action( 'woocommerce_after_cart', 'ccl_set_cart_count_to_the_session' );

    /**
     * Calculate discount
     * @param [type] $total
     * @return void
     */
    function ccl_calculate_discount ()
    {
        $cart = Ccl_Price_Calculator::set_discount ();
    }

    /**
     * Puts count of cart items to the session. this is necessary in order to skip all items which have already been calculated with discount
     * @return void
     */
    function ccl_put_cart_items_count_to_the_session () {
        global $woocommerce;
        $count = 0;
        $cart = $woocommerce->cart->get_cart_contents();
        foreach ( $cart as $product ) {
            $count++;
        }
        if ( $count > 2 ) {
            $_SESSION['lastCartCount'] = $count;
        } else {
            $_SESSION['lastCartCount'] = 0;
        }
    }
}
