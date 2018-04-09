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

    add_filter( 'woocommerce_before_cart', 'ccl_calculate_discont' );

    /**
     * Calculate discount
     * @param [type] $total
     * @return void
     */
    function ccl_calculate_discont ( $cart )
    {
        $cart = Ccl_Price_Calculator::set_discount ();

        return $cart;
    }
}
