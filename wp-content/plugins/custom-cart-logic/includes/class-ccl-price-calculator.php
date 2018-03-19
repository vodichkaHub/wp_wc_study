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
     * Sets discount
     *
     * @return array $cart
     */
    public static function set_discount ()
    {
        global $woocommerce;
        $cart = $woocommerce->cart->get_cart_contents();
        self::set_new_disc_products( $cart );
        $_SESSION['lastCartCount'] = $woocommerce->cart->get_cart_contents_count();

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
     * Finds names of cart items which should has a discount
     * @param array $cart
     * @return array $names
     */
    protected static function find_product_for_discount ( $cart )
    {
        $names = array(); // array for the items which should be replaced by discount product
        $temp = null;
        $i = 0;
        $j = 0;
        $props = array();
        foreach ( $cart as $product ) {
            while ( $j <= $_SESSION['lastCartCount'] ) {
                $j++;
                continue;
            }
            if ( $i % 2 == 0 ) {
                $temp = $product;
                $i++;
                continue;
            } else {
                $productSingleItem = $product['data']->get_data();
                $tempSingleItem = $temp['data']->get_data();
                if ( $productSingleItem['price'] > $tempSingleItem['price'] ) {
                    array_push( $props, $tempSingleItem['name'] );
                    array_push( $props, $temp['key'] );
                    array_push( $names, $props );
                    $props = array();
                } else {
                    array_push( $props, $productSingleItem['name'] );
                    array_push( $props, $product['key'] );
                    array_push( $names, $props );
                    $props = array();
                }
            }
            $i++;
        }

        return $names;
    }

    /**
     * Reurn array of names end on "DISC" (discount products)
     * @return array
     */
    protected static function get_disc_products () {
        global $wpdb;
        $query = "
            SELECT $wpdb->posts.ID, $wpdb->posts.post_title
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_name like '%DISC'
        ";

        return $wpdb->get_results( $query, ARRAY_N );
    }

    /**
     *
     * @param array $shouldHaveDisc
     * @param array $discProducts
     * @return void
     */
    protected static function swap_products ( $shouldHaveDisc, $discProducts )
    {
        global $woocommerce;

        foreach ( $shouldHaveDisc as $product ) {
            foreach ( $discProducts as $discProduct ) {
                $nameWithDiscPostfix = $product['0'] . 'DISC';
                if ( $nameWithDiscPostfix == $discProduct['1'] ) {
                    $woocommerce->cart->remove_cart_item( $product['1'] );
                    $item = self::select_by_id( $discProduct['0'] );
                    $t = $woocommerce->cart->add_to_cart( $item['0']->ID );
                }
            }
        }
    }

    /**
     * DB selection
     * @param int $id
     * @return OBJECT
     */
    protected static function select_by_id ($id) {
        global $wpdb;
        $query = "
            SELECT $wpdb->posts.*
            FROM $wpdb->posts
            WHERE $wpdb->posts.ID = $id
        ";

        return $wpdb->get_results( $query, OBJECT );
    }

    /**
     * Replace products which must have a discount by disc item
     * @param array $cart
     */
    protected static function set_new_disc_products ( $cart )
    {
        $shouldHaveDisc = self::find_product_for_discount( $cart );
        $discProducts = self::get_disc_products();
        self::swap_products( $shouldHaveDisc, $discProducts );
    }
}
