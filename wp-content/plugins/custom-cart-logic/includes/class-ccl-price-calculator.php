<?php

defined( 'ABSPATH' ) || exit;



class Ccl_Price_Calculator
{
    /**
     * Constructor
     */
    public function __construct ()
    {

    }
    /**
     * Calculate a price with discont
     */
    public static function calculate_discount ( $discount )
    {
        global $woocommerce;
        $cart = $woocommerce->cart->get_cart_contents();
        $pricesArray = self::get_prices_array( $cart );
        $pricesArray = self::get_prices_array_with_discount( $pricesArray, $discount );
        $cart = self::set_cart_with_discount( $cart, $pricesArray );
        $woocommerce->cart->set_total( (string)array_sum( $pricesArray ) );

        return $cart;
    }

    /**
     * Gets prices all of products
     * @param array $cart
     * @return array $pricesArray || false
     */
    protected static function get_prices_array ( array $cart )
    {
        global $woocommerce;
        $pricesArray = array();

        if ( isset( $cart ) ) {
            foreach ( $cart as $product ) {
                array_push( $pricesArray, $product['line_subtotal'] );
            }
            return $pricesArray;
        } else {
            return false;
        }
    }

    /**
     * Calculate discount
     * @param array $pricesArray
     * @param string $discount
     * @return array $pricesArray
     */
    protected static function get_prices_array_with_discount ( array $pricesArray, $discount )
    {
        $size = count( $pricesArray );
        for ( $i = 0; $i < $size - 1; $i+=2 ) {
            if ( $pricesArray[$i] > $pricesArray[$i + 1] ) {
                $pricesArray[$i + 1] = (float)$pricesArray[$i + 1] * ( 1 - (float)$discount / 100 );
            } else {
                $pricesArray[$i] = (float)$pricesArray[$i] * ( 1 - (float)$discount / 100 );
            }
        }

        return $pricesArray;
    }

    /**
     * Set new prices
     * @param array $cart
     * @param array $pricesArray
     * @return array $pricesArray
     */
    protected static function set_cart_with_discount ( $cart, $pricesArray )
    {
        foreach ($cart as $product) {
            $i = 0;
            $product['line_subtotal'] = $pricesArray[$i];
            $i++;
        }

        return $cart;
    }
}
