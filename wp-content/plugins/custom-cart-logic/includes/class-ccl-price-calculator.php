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

        return $cart;
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
        $j = 1;
        $props = array();
        foreach ( $cart as $product ) {
            if ( $j <= $_SESSION['lastCartCount'] ) { // loop skips all cart records which have already been calculated
                $j++;
                continue;
            } else {
                if ( $i % 2 == 0 ) { // every second product is written to the temprary var $temp for further comprasion with every second + 1
                    $temp = $product;
                    $i++;
                    continue;
                } else {
                    $productSingleItem = $product['data']->get_data(); // gets data from object WC_Product_Simple which is inside the array
                    $tempSingleItem = $temp['data']->get_data(); // gets data from object WC_Product_Simple which is inside the array
                    if ( $productSingleItem['price'] > $tempSingleItem['price'] ) {
                        $props['name'] = $tempSingleItem['name'];
                        $props['key'] = $temp['key'];
                        $props['quantity'] = $temp['quantity'];
                        array_push( $names, $props );
                        $props = array();
                    } else {
                        $props['name'] = $productSingleItem['name'];
                        $props['key'] = $product['key'];
                        $props['quantity'] = $product['quantity'];
                        array_push( $names, $props );
                        $props = array();
                    }
                    $i++;
                }
            }
        }

        return $names;
    }

    /**
     * Returns array of names ending in "DISC" (discount products)
     * @return array
     */
    protected static function get_disc_products () {
        global $wpdb;
        $query = "
            SELECT $wpdb->posts.ID, $wpdb->posts.post_title
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_name like '%DISC'
        ";

        return $wpdb->get_results( $query, ARRAY_A );
    }

    /**
     * Search names from $shouldHaveDisc array with "DISC" postfix inside the $discProducts array. Then swap it
     * @param array $shouldHaveDisc
     * @param array $discProducts
     * @return void
     */
    protected static function swap_products ( $shouldHaveDisc, $discProducts )
    {
        global $woocommerce;

        foreach ( $shouldHaveDisc as $product ) {
            foreach ( $discProducts as $discProduct ) {
                $nameWithDiscPostfix = $product['name'] . 'DISC'; // concatenates the name of product which should be discount for further searching inside the $discProducts array
                if ( $nameWithDiscPostfix == $discProduct['post_title'] ) {
                    if ( $product['quantity'] > 1 ) {
                        $woocommerce->cart->set_quantity($product['key'], (int)$product['quantity'] - 1 ); //decrement quantity of the product
                        $woocommerce->cart->add_to_cart( $discProduct['ID'] ); // adds one point DISC product to the cart
                    } else {
                        $woocommerce->cart->remove_cart_item( $product['key'] );
                        $woocommerce->cart->add_to_cart( $discProduct['ID'] );
                    }
                }
            }
        }
    }

    /**
     * Replace products which must have a discount by disc item
     * @param array $cart
     */
    protected static function set_new_disc_products ( $cart )
    {
        $shouldHaveDisc = self::find_product_for_discount( $cart );
        // var_dump($shouldHaveDisc);
        // exit;
        if ( isset( $shouldHaveDisc ) ) {
            $discProducts = self::get_disc_products(); // all DISC products from DB
            self::swap_products( $shouldHaveDisc, $discProducts );
        }
    }
}
