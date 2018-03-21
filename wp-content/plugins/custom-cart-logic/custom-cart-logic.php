<?php
/**
 * Plugin Name: CUSTOM CART LOGIC
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ccl-price-calculator.php';
        /**
     * Activation/Deactivation
     */
    register_activation_hook( __FILE__, 'ccl_on_activation');
    register_deactivation_hook( __FILE__, 'ccl_on_deactivation');
    add_filter( 'woocommerce_before_cart', 'ccl_calculate_discont' );


    /**
     * Activation callback
     * @return void
     */
    function ccl_on_activation ()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/class-ccl-activator.php';
        Ccl_Actiavtor::ccl_activate();
    }

    /**
     * Deactivation callback
     * @return void
     */
    function ccl_on_deactivation ()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/class-ccl-deactivator.php';
        Ccl_Deactivator::ccl_deactivate();
    }

    /**
     * Calculate discount
     * @param [type] $total
     * @return void
     */
    function ccl_calculate_discont ( $cart )
    {
        $discount = 50;
        $cart = Ccl_Price_Calculator::set_discount ();
        // var_dump($cart);
        // exit();

        return $cart;
    }
}
