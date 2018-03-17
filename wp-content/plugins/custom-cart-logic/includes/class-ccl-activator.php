<?php
/**
 * Fired activation
 */
class Ccl_Actiavtor
{
    /**
    * Activation callback
    * @return void
    */
    public static function ccl_activate ()
    {
        wp_enqueue_script('pluginIsAactivated.js', '/wp-content/plugins/custom-cart-logic/public/js/pluginIsAactivated.js');
    }
}
