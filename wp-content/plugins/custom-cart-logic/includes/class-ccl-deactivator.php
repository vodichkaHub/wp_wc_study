<?php
/**
 * Fired deactivation
 */
class Ccl_Deactivator{

    /**
     * Deactivation callback
     * @return void
     */
    public static function ccl_deactivate ()
    {
        wp_enqueue_script('pluginIsDeactivated.js', '/wp-content/plugins/custom-cart-logic/public/js/pluginIsDeactivated.js');
    }
}
