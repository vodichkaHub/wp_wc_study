<?php
/*
  Plugin Name: Product CSV Import Export (BASIC)
  Plugin URI: https://www.xadapter.com/product/product-import-export-plugin-for-woocommerce/
  Description: Import and Export Products From and To your WooCommerce Store.
  Author: XAdapter
  Author URI: https://www.xadapter.com/shop
  Version: 1.4.3
  WC tested up to: 3.3.3
  Text Domain: wf_csv_import_export
 */

if (!defined('ABSPATH') || !is_admin()) {
    return;
}


if (!defined('WF_PIPE_CURRENT_VERSION')) {
    define("WF_PIPE_CURRENT_VERSION", "1.4.3");
}
if (!defined('WF_PROD_IMP_EXP_ID')) {
    define("WF_PROD_IMP_EXP_ID", "wf_prod_imp_exp");
}
if (!defined('WF_WOOCOMMERCE_CSV_IM_EX')) {
    define("WF_WOOCOMMERCE_CSV_IM_EX", "wf_woocommerce_csv_im_ex");
}
/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!class_exists('WF_Product_Import_Export_CSV')) :

        /**
         * Main CSV Import class
         */
        class WF_Product_Import_Export_CSV {

            /**
             * Constructor
             */
            public function __construct() {
                if (!defined('WF_ProdImpExpCsv_FILE')) {
                    define('WF_ProdImpExpCsv_FILE', __FILE__);
                }

                if (!defined('WF_ProdImpExpCsv_BASE')) {
                    define('WF_ProdImpExpCsv_BASE', plugin_dir_path(__FILE__));
                }

                add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wf_plugin_action_links'));
                add_action('init', array($this, 'load_plugin_textdomain'));
                add_action('init', array($this, 'catch_export_request'), 20);
                add_action('admin_init', array($this, 'register_importers'));

                add_action('admin_footer', array($this, 'deactivate_scripts'));
                add_action('wp_ajax_pipe_submit_uninstall_reason', array($this, "send_uninstall_reason"));

                include_once( 'includes/class-wf-prodimpexpcsv-system-status-tools.php' );
                include_once( 'includes/class-wf-prodimpexpcsv-admin-screen.php' );
                include_once( 'includes/importer/class-wf-prodimpexpcsv-importer.php' );

                if (defined('DOING_AJAX')) {
                    include_once( 'includes/class-wf-prodimpexpcsv-ajax-handler.php' );
                }
            }

            public function wf_plugin_action_links($links) {
                $plugin_links = array(
                    '<a href="' . admin_url('admin.php?page=wf_woocommerce_csv_im_ex&tab=export') . '">' . __('Import Export', 'wf_csv_import_export') . '</a>',
                    '<a target="_blank" href="https://www.xadapter.com/product/product-import-export-plugin-for-woocommerce/" target="_blank">' . __('Premium Upgrade', 'wf_csv_import_export') . '</a>',
                    '<a target="_blank" href="https://wordpress.org/support/plugin/product-import-export-for-woo/">' . __('Support', 'wf_csv_import_export') . '</a>',
                    '<a target="_blank" href="https://wordpress.org/support/plugin/product-import-export-for-woo/reviews/">' . __('Review', 'wf_csv_import_export') . '</a>',
                );
                if (array_key_exists('deactivate', $links)) {
                    $links['deactivate'] = str_replace('<a', '<a class="pipe-deactivate-link"', $links['deactivate']);
                }
                return array_merge($plugin_links, $links);
            }

            /**
             * Add screen ID
             */
            public function woocommerce_screen_ids($ids) {
                $ids[] = 'admin'; // For import screen
                return $ids;
            }

            /**
             * Handle localization
             */
            public function load_plugin_textdomain() {
                load_plugin_textdomain('wf_csv_import_export', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            }

            /**
             * Catches an export request and exports the data. This class is only loaded in admin.
             */
            public function catch_export_request() {

                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_csv_im_ex') {
                    switch ($_GET['action']) {
                        case "export" :
                            $user_ok = $this->hf_user_permission();
                            if ($user_ok) {
                                include_once( 'includes/exporter/class-wf-prodimpexpcsv-exporter.php' );
                                WF_ProdImpExpCsv_Exporter::do_export('product');
                            } else {
                                wp_redirect(wp_login_url());
                            }
                            break;
                    }
                }
            }

            /**
             * Register importers for use
             */
            public function register_importers() {
                register_importer('xa_woocommerce_csv', 'XAdapter WooCommerce Product Import (CSV)', __('Import <strong>products</strong> to your store via a csv file.', 'wf_csv_import_export'), 'WF_ProdImpExpCsv_Importer::product_importer');
            }

            private function hf_user_permission() {
                // Check if user has rights to export
                $current_user = wp_get_current_user();
                $user_ok = false;
                $wf_roles = apply_filters('hf_user_permission_roles', array('administrator', 'shop_manager'));
                if ($current_user instanceof WP_User) {
                    $can_users = array_intersect($wf_roles, $current_user->roles);
                    if (!empty($can_users)) {
                        $user_ok = true;
                    }
                }
                return $user_ok;
            }

            private function get_uninstall_reasons() {

                $reasons = array(
                    array(
                        'id' => 'could-not-understand',
                        'text' => __('I couldn\'t understand how to make it work', 'wf_csv_import_export'),
                        'type' => 'textarea',
                        'placeholder' => __('Would you like us to assist you?', 'wf_csv_import_export')
                    ),
                    array(
                        'id' => 'found-better-plugin',
                        'text' => __('I found a better plugin', 'wf_csv_import_export'),
                        'type' => 'text',
                        'placeholder' => __('Which plugin?', 'wf_csv_import_export')
                    ),
                    array(
                        'id' => 'not-have-that-feature',
                        'text' => __('The plugin is great, but I need specific feature that you don\'t support', 'wf_csv_import_export'),
                        'type' => 'textarea',
                        'placeholder' => __('Could you tell us more about that feature?', 'wf_csv_import_export')
                    ),
                    array(
                        'id' => 'is-not-working',
                        'text' => __('The plugin is not working', 'wf_csv_import_export'),
                        'type' => 'textarea',
                        'placeholder' => __('Could you tell us a bit more whats not working?', 'wf_csv_import_export')
                    ),
                    array(
                        'id' => 'looking-for-other',
                        'text' => __('It\'s not what I was looking for', 'wf_csv_import_export'),
                        'type' => 'textarea',
                        'placeholder' =>  __('Could you tell us a bit more?', 'wf_csv_import_export')
                    ),
                    array(
                        'id' => 'did-not-work-as-expected',
                        'text' => __('The plugin didn\'t work as expected', 'wf_csv_import_export'),
                        'type' => 'textarea',
                        'placeholder' => __('What did you expect?', 'wf_csv_import_export')
                    ),
                    array(
                        'id' => 'other',
                        'text' => __('Other', 'wf_csv_import_export'),
                        'type' => 'textarea',
                        'placeholder' => __('Could you tell us a bit more?', 'wf_csv_import_export')
                    ),
                );

                return $reasons;
            }

            public function deactivate_scripts() {

                global $pagenow;
                if ('plugins.php' != $pagenow) {
                    return;
                }
                $reasons = $this->get_uninstall_reasons();
                ?>
                <div class="pipe-modal" id="pipe-pipe-modal">
                    <div class="pipe-modal-wrap">
                        <div class="pipe-modal-header">
                            <h3><?php _e('If you have a moment, please let us know why you are deactivating:', 'wf_csv_import_export'); ?></h3>
                        </div>
                        <div class="pipe-modal-body">
                            <ul class="reasons">
                                <?php foreach ($reasons as $reason) { ?>
                                    <li data-type="<?php echo esc_attr($reason['type']); ?>" data-placeholder="<?php echo esc_attr($reason['placeholder']); ?>">
                                        <label><input type="radio" name="selected-reason" value="<?php echo $reason['id']; ?>"> <?php echo $reason['text']; ?></label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="pipe-modal-footer">
                            <a href="#" class="dont-bother-me"><?php _e('I rather wouldn\'t say', 'wf_csv_import_export'); ?></a>
                            <button class="button-primary pipe-model-submit"><?php _e('Submit & Deactivate', 'wf_csv_import_export'); ?></button>
                            <button class="button-secondary pipe-model-cancel"><?php _e('Cancel', 'wf_csv_import_export'); ?></button>
                        </div>
                    </div>
                </div>

                <style type="text/css">
                    .pipe-modal {
                        position: fixed;
                        z-index: 99999;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        left: 0;
                        background: rgba(0,0,0,0.5);
                        display: none;
                    }
                    .pipe-modal.modal-active {display: block;}
                    .pipe-modal-wrap {
                        width: 50%;
                        position: relative;
                        margin: 10% auto;
                        background: #fff;
                    }
                    .pipe-modal-header {
                        border-bottom: 1px solid #eee;
                        padding: 8px 20px;
                    }
                    .pipe-modal-header h3 {
                        line-height: 150%;
                        margin: 0;
                    }
                    .pipe-modal-body {padding: 5px 20px 20px 20px;}
                    .pipe-modal-body .input-text,.pipe-modal-body textarea {width:75%;}
                    .pipe-modal-body .reason-input {
                        margin-top: 5px;
                        margin-left: 20px;
                    }
                    .pipe-modal-footer {
                        border-top: 1px solid #eee;
                        padding: 12px 20px;
                        text-align: right;
                    }
                </style>
                <script type="text/javascript">
                    (function ($) {
                        $(function () {
                            var modal = $('#pipe-pipe-modal');
                            var deactivateLink = '';
                            $('#the-list').on('click', 'a.pipe-deactivate-link', function (e) {
                                e.preventDefault();
                                modal.addClass('modal-active');
                                deactivateLink = $(this).attr('href');
                                modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                            });
                            modal.on('click', 'button.pipe-model-cancel', function (e) {
                                e.preventDefault();
                                modal.removeClass('modal-active');
                            });
                            modal.on('click', 'input[type="radio"]', function () {
                                var parent = $(this).parents('li:first');
                                modal.find('.reason-input').remove();
                                var inputType = parent.data('type'),
                                        inputPlaceholder = parent.data('placeholder'),
                                        reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';

                                if (inputType !== '') {
                                    parent.append($(reasonInputHtml));
                                    parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                                }
                            });

                            modal.on('click', 'button.pipe-model-submit', function (e) {
                                e.preventDefault();
                                var button = $(this);
                                if (button.hasClass('disabled')) {
                                    return;
                                }
                                var $radio = $('input[type="radio"]:checked', modal);
                                var $selected_reason = $radio.parents('li:first'),
                                        $input = $selected_reason.find('textarea, input[type="text"]');

                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'pipe_submit_uninstall_reason',
                                        reason_id: (0 === $radio.length) ? 'none' : $radio.val(),
                                        reason_info: (0 !== $input.length) ? $input.val().trim() : ''
                                    },
                                    beforeSend: function () {
                                        button.addClass('disabled');
                                        button.text('Processing...');
                                    },
                                    complete: function () {
                                        window.location.href = deactivateLink;
                                    }
                                });
                            });
                        });
                    }(jQuery));
                </script>
                <?php
            }

            public function send_uninstall_reason() {

                global $wpdb;

                if (!isset($_POST['reason_id'])) {
                    wp_send_json_error();
                }

                //$current_user = wp_get_current_user();

                $data = array(
                    'reason_id' => sanitize_text_field($_POST['reason_id']),
                    'plugin' => "productimportexort",
                    'auth' => 'wfpipe_uninstall_1234#',
                    'date' => gmdate("M d, Y h:i:s A"),
                    'url' => home_url(),
                    'user_email' => '',//$current_user->user_email,
                    'reason_info' => isset($_REQUEST['reason_info']) ? trim(stripslashes($_REQUEST['reason_info'])) : '',
                    'software' => $_SERVER['SERVER_SOFTWARE'],
                    'php_version' => phpversion(),
                    'mysql_version' => $wpdb->db_version(),
                    'wp_version' => get_bloginfo('version'),
                    'wc_version' => (!defined('WC_VERSION')) ? '' : WC_VERSION,
                    'locale' => get_locale(),
                    'multisite' => is_multisite() ? 'Yes' : 'No',
                    'wfpipe_version' => WF_PIPE_CURRENT_VERSION
                );
                // Write an action/hook here in webtoffe to recieve the data
                $resp = wp_remote_post('http://feedback.webtoffee.com/wp-json/wfpipe/v1/uninstall', array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => false,
                    'headers' => array('user-agent' => 'pipe/' . md5(esc_url(home_url())) . ';'),
                    'body' => $data,
                    'cookies' => array()
                    )
                );

                wp_send_json_success();
            }

        }

        endif;

    new WF_Product_Import_Export_CSV();
}



// Welcome screen tutorial video --> Move this function to inside the class
add_action('admin_init', 'impexp_welcome');

register_activation_hook(__FILE__, 'hf_welcome_screen_activate_basic');

function hf_welcome_screen_activate_basic() {
    if (is_plugin_active('product-csv-import-export-for-woocommerce/product-csv-import-export.php')) {
        deactivate_plugins(basename(__FILE__));
        wp_die(__("Is everything fine? You already have the Premium version installed in your website. For any issues, kindly raise a ticket via <a target='_blank' href='//support.xadapter.com/'>support.xadapter.com</a>", "wf_csv_import_export"), "", array('back_link' => 1));
    }
    update_option('xa_pipe_plugin_installed_date', date('Y-m-d H:i:s'));
    set_transient('_welcome_screen_activation_redirect', true, 30);
}

if (!function_exists('impexp_welcome')) {

    function impexp_welcome() {
        if (!get_transient('_welcome_screen_activation_redirect')) {
            return;
        }
        delete_transient('_welcome_screen_activation_redirect');
        wp_safe_redirect(add_query_arg(array('page' => 'wf_woocommerce_csv_im_ex'), admin_url('admin.php')));
    }

}