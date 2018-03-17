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
    add_filter( 'woocommerce_before_cart', 'ccl_get_total_with_discont' );


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


    function ccl_get_total_with_discont ( $total )
    {
        $discount = 50;
        $total = Ccl_Price_Calculator::calculate_discount ( $discount );
        // var_dump($total);
        // exit();

        return $total;
    }
}
