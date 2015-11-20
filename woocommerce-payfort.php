<?php
/*
Plugin Name: Payfort (Start)
Description: Payfort makes it really easy to start accepting online payments (credit &amp; debit cards) in the Middle East. Sign up is instant, at https://start.payfort.com/
Version: 0.1.4
Plugin URI: https://start.payfort.com
Author: Payfort
Author URI: https://start.payfort.com
License: Under GPL2
 */
require plugin_dir_path(__FILE__).'vendor/payfort/start/Start.php';
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/* Enable automatic updates to this plugin
----------------------------------------------------------- */
add_filter('auto_update_plugin', '__return_true');
/* Add a custom payment class to WC
------------------------------------------------------------ */
add_action('plugins_loaded', 'woocommerce_payfort', 0);
function woocommerce_payfort(){
    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class is not available, do nothing
    if(class_exists('WC_Gateway_Payfort'))
        return;
    class WC_Gateway_Payfort extends WC_Payment_Gateway{
        public function __construct(){
            $plugin_dir = plugin_dir_url(__FILE__);
            global $woocommerce;
            $this->id = 'payfort';
            $this->icon = apply_filters('woocommerce_white_icon', ''.$plugin_dir.'white-cards.png');
            $this->has_fields = true;
            // Load the settings
            $this->init_form_fields();
            $this->init_settings();
            // Define user set variables
            $this->title = "Credit / Debit Card";
            $this->test_open_key = $this->get_option('test_open_key');
            $this->test_secret_key = $this->get_option('test_secret_key');
            $this->live_open_key = $this->get_option('live_open_key');
            $this->live_secret_key = $this->get_option('live_secret_key');
            $this->description = $this->get_option('description');
            $this->test_mode = $this->get_option('test_mode');
            // Logs
            if (isset($this->debug) && $this->debug == 'yes') {
                $this->log = $woocommerce->logger();
            }
            // Actions
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
            // Save options
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            if (!$this->is_valid_for_use()){
                $this->enabled = false;
            }
        }

        function payment_scripts() {
            global $woocommerce;
            if ( ! is_checkout() ) {
                return;
            }
            wp_enqueue_script( 'beautifuljs', 'https://beautiful.start.payfort.com/checkout.js', array(), WC_VERSION, true );
            wp_enqueue_script( 'beautifuljs-config',  plugins_url('payfort/assets/js/config.js'), array( 'beautifuljs'), WC_VERSION, true );
            wp_enqueue_script( 'beautifuljs-checkout',  plugins_url('payfort/assets/js/checkout.js'), array( 'beautifuljs'), WC_VERSION, true );
            wp_localize_script( 'beautifuljs-config', 'WooCommerceStartParams', array(
                'key' => $this->test_mode == 'yes'? $this->test_open_key : $this->live_open_key,
                'currency' => get_woocommerce_currency()
            ));
        }
        /**
         * Check if this gateway is enabled and available in the user's currency
         *
         * @access public
         * @return bool
         */
        function is_valid_for_use() {
            // Skip currency check
            return true;
        }
        /**
         * Admin Panel Options
         * - Options for bits like 'api keys' and availability on a country-by-country basis
         *
         * @since 1.0.0
         */
        public function admin_options() {
?>
        <h3><?php _e( 'Payfort Start', 'woocommerce' ); ?></h3>
        <p><?php _e( 'Please fill in the below section to start accepting payments on your site! You can find all the required information in your <a href="https://dashboard.start.payfort.com/" target="_blank">Payfort Dashboard</a>.', 'woocommerce' ); ?></p>

        <?php if ( $this->is_valid_for_use() ) : ?>

        <table class="form-table">
<?php
            // Generate the HTML For the settings form.
            $this->generate_settings_html();
?>
        </table><!--/.form-table-->

        <?php else : ?>
        <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'Payfort Start does not support your store currency at this time.', 'woocommerce' ); ?></p></div>
<?php
endif;
        }
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable the Start gateway', 'woocommerce' ),
                    'default' => 'yes'
                ),
                'description' => array(
                    'title' => __( 'Description', 'woocommerce' ),
                    'type' => 'text',
                    'description' => __( 'This is the description the user sees during checkout.', 'woocommerce' ),
                    'default' => __( 'Pay for your items with any Credit or Debit Card', 'woocommerce' )
                ),
                'test_open_key' => array(
                    'title' => __( 'Test Open Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your test open key (you can get it from your Start dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'test_secret_key' => array(
                    'title' => __( 'Test Secret Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your test secret key (you can get it from your Start dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'live_open_key' => array(
                    'title' => __( 'Live Open Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your live open key (you can get it from your Start dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'live_secret_key' => array(
                    'title' => __( 'Live Secret Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your live secret key (you can get it from your Start dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'test_mode' => array(
                    'title' => __( 'Test mode', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Test mode', 'woocommerce' ),
                    'default' => 'no'
                )
            );
        }
        /**
         * Generate the credit card payment form
         *
         * @access public
         * @param none
         * @return string
         */
        function payment_fields() {
            // Access the global object
            global $woocommerce;
            $plugin_dir = plugin_dir_url(__FILE__);
            // Description of payment method from settings
            if ($this->description) {
                echo "<p>".$this->description."</p>";
            }
            // Are we in test mode?
            if ($this->test_mode == 'yes') {
?>
        <div style="background-color:yellow;">
        You're in <strong>test mode</strong>. Make sure to use <a href="https://docs.start.payfort.com/guides/testing/" target="_blank">test cards to checkout</a> :)
        <br/>------<br/>
        <em>Tip: You can change this by going to WooCommerce -&gt; Settings -&gt; Checkout -&gt; Payfort (Start)</em>
        </div>
<?php
            }

            $amount = $woocommerce->cart->total * 100;

            echo "<input name='start_amount' type='hidden' value='".$amount."'/>";
        }
        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order($order_id);
            $token = $_POST['payfortToken'];
            try {
                if ( empty( $token ) ) {
                    $error_msg = __( 'Please make sure your card details have been entered correctly.', 'woocommerce' );
                    throw new Start_Error( $error_msg );
                }

                $charge_description = $order->id . ": WooCommerce charge for " . $order->billing_email;

                // Charge Arguments
                $charge_args = array(
                    'description' => $charge_description,
                    'card' => $token,
                    'currency' => strtoupper(get_woocommerce_currency()),
                    'email' => $order->billing_email,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    /**
                     * TODO:
                     * Update the amount to consider currencies with varying
                     * minimum currency amounts .. I'm just using 100 here for
                     * USD and AED (both with 100 cents = 1 unit).
                     */
                    'amount' => $order->get_total() * 100
                );
                if ($this->test_mode == 'yes') {
                    Start::setApiKey($this->test_secret_key);
                } else {
                    Start::setApiKey($this->live_secret_key);
                }
                $woo_version = wpbo_get_woo_version_number();
                $plugin_data = get_plugin_data("wp-content/plugins/payfort/woocommerce-payfort.php", $markup = true, $translate = true);
                $userAgent = 'WooCommerce ' . $woo_version . ' / Start Plugin ' . $plugin_data['Version'];
                Start::setUserAgent($userAgent);
                // Charge the token
                $charge = Start_Charge::create($charge_args);
                // No exceptions? Yaay, all done!
                $order->payment_complete();
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url( $order )
                );
            } catch (Start_Error $e) {
                // TODO: Can we get the extra params (so the error is more apparent)?
                // e.g. Instead of "request params are invalid", we get
                // "extras":{"amount":["minimum amount (in the smallest currency unit) is 185 for AED"]
                $error_code = $e->getErrorCode();
                if ( $error_code === "card_declined" ) {
                    $message = __('Error: ', 'woothemes') . $e->getMessage() . " Please, try with another card";
                } else {
                    $message = __('Error: ', 'woothemes') . $e->getMessage();
                }
                // If function should we use?
                if(function_exists("wc_add_notice")) {
                    // Use the new version of the add_error method
                    wc_add_notice($message, 'error');
                } else {
                    // Use the old version
                    $woocommerce->add_error($message);
                }
                // we raise 'update_checkout' event for javscript
                // to remove card token
                WC()->session->set( 'refresh_totals', true );
                return array(
                    'result'   => 'fail',
                    'redirect' => ''
                );
            }
        }
    }
    /**
     * Add the gateway to WooCommerce
     **/
    function add_payfort_gateway($methods){
        $methods[] = 'WC_Gateway_Payfort';
        return $methods;
    }
    
    /**
     * Get woocommerce version
     * @return type
     */
    function wpbo_get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it 
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            return $plugin_folder[$plugin_file]['Version'];
        } else {
            // Otherwise return null
            return NULL;
        }
    }
    add_filter('woocommerce_payment_gateways', 'add_payfort_gateway');
}