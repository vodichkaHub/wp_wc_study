<?php
/**
 * Plugin Name: CUSTOM CART LOGIC
 */

 /**
  * Activation plugin callback
  * @return void
  */
function ccl_on_activate ()
{
    echo "<script type='text/javascipt'>";
    echo "console_log('activated!')";
    echo "</script>";
}
register_activation_hook( __FILE__, 'ccl_on_activate');

/**
 * Deactivation callback
 * @return void
 */
function ccl_on_deactivation ()
{
    echo "<script type='text/javascipt'>";
    echo "console_log('deactivated!')";
    echo "</script>";
}
register_deactivation_hook( __FILE__, 'ccl_on_deactivation');
