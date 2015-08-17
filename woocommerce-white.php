<?php
/*
Plugin Name: Payfort (Start)
Description: Payfort makes it really easy to start accepting online payments (credit &amp; debit cards) in the Middle East. Sign up is instant, at https://start.payfort.com/
Version: 0.0.1
Plugin URI: https://start.payfort.com
Author: Payfort
Author URI: https://start.payfort.com
License: Under GPL2
*/

require plugin_dir_path(__FILE__).'vendor/payfort/start/Payfort.php';

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Enable automatic updates to this plugin
   ----------------------------------------------------------- */
add_filter('auto_update_plugin', '__return_true');

/* Add a custom payment class to WC
  ------------------------------------------------------------ */
add_action('plugins_loaded', 'woocommerce_white', 0);

function woocommerce_white(){
  if (!class_exists('WC_Payment_Gateway'))
    return; // if the WC payment gateway class is not available, do nothing
  if(class_exists('WC_White'))
    return;

    class WC_Gateway_White extends WC_Payment_Gateway{
        public function __construct(){

            $plugin_dir = plugin_dir_url(__FILE__);

            global $woocommerce;

            $this->id = 'white';
            $this->icon = apply_filters('woocommerce_white_icon', ''.$plugin_dir.'white-cards.png');
            $this->has_fields = true;

            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = "Credit / Debit Card (powered by White)";
            $this->test_open_key = $this->get_option('test_open_key');
            $this->test_secret_key = $this->get_option('test_secret_key');
            $this->live_open_key = $this->get_option('live_open_key');
            $this->live_secret_key = $this->get_option('live_secret_key');
            $this->description = $this->get_option('description');
            $this->test_mode = $this->get_option('test_mode');

            // Logs
            if ($this->debug == 'yes'){
                $this->log = $woocommerce->logger();
            }

            // Actions
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_after_checkout_form', array($this, 'payfort_preload_checkout'));

            // Save options
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            if (!$this->is_valid_for_use()){
                $this->enabled = false;
            }
        }

        /**
         * Check if this gateway is enabled and available in the user's currency
         *
         * @access public
         * @return bool
         */
        function is_valid_for_use() {
            if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_white_supported_currencies', array( 'AED', 'USD' ) ) ) ) return false;

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
            <h3><?php _e( 'White Payments', 'woocommerce' ); ?></h3>
            <p><?php _e( 'Please fill in the below section to start accepting payments on your site! You can find all the required information in your <a href="https://dashboard.whitepayments.com/" target="_blank">White Dashboard</a>.', 'woocommerce' ); ?></p>

            <?php if ( $this->is_valid_for_use() ) : ?>

                <table class="form-table">
                    <?php
                    // Generate the HTML For the settings form.
                    $this->generate_settings_html();
                    ?>
                </table><!--/.form-table-->

            <?php else : ?>
                <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'White does not support your store currency.', 'woocommerce' ); ?></p></div>
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
                    'label' => __( 'Enable White', 'woocommerce' ),
                    'default' => 'yes'
                ),
                'description' => array(
                    'title' => __( 'Description', 'woocommerce' ),
                    'type' => 'text',
                    'description' => __( 'This is the description the user sees during checkout.', 'woocommerce' ),
                    'default' => __( 'Pay for your items with Credit Card', 'woocommerce' )
                ),
                'test_open_key' => array(
                    'title' => __( 'Test Open Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your test open key (you can get it from your White dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'test_secret_key' => array(
                    'title' => __( 'Test Secret Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your test secret key (you can get it from your White dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'live_open_key' => array(
                    'title' => __( 'Live Open Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your live open key (you can get it from your White dashboard).', 'woocommerce' ),
                    'default' => '',
                    'desc_tip'      => true,
                    'placeholder' => ''
                ),
                'live_secret_key' => array(
                    'title' => __( 'Live Secret Key', 'woocommerce' ),
                    'type'      => 'text',
                    'description' => __( 'Please enter your live secret key (you can get it from your White dashboard).', 'woocommerce' ),
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

          // Errors displayed above the form
          ?>
          <ul class="woocommerce-error" style="display:none" id="white_error_creditcard">
            <li>Credit Card details are incorrect, please try again.</li>
          </ul>
          <?php

          // Are we in test mode?
          if ($this->test_mode == 'yes') {
          ?>
            <div style="background-color:yellow;">
                You're in <strong>test mode</strong>. Make sure to use <a href="https://whitepayments.com/docs/testing" target="_blank">test cards to checkout</a> :)
                <br/>------<br/>
                <em>Tip: You can change this by going to WooCommerce -&gt; Settings -&gt; Checkout -&gt; White</em>
            </div>
          <?php
          }
          ?>

          <!-- Attach form submission handlers -->
          <script>
          jQuery(function(){

            // Bind to form submission
            jQuery('#place_order').unbind('click');
            jQuery('#place_order').click(function(e) {
              e.preventDefault();

              // Open the modal for collecting payment information
              StartCheckout.open({
                amount: <?php echo ($woocommerce->cart->total)*100; ?>,
                currency: "<?php echo get_woocommerce_currency() ?>",
                email: jQuery("#billing_email").val()
              });

              // Prevent resubmission
              return false;
            });
          });

          function submitFormAfterToken() {
            // Simulate successful click
            jQuery('#place_order').unbind('click');
            jQuery('#place_order').click(function(e) {
              return true;
            });
            jQuery('#place_order').click();
          }
          </script>
          <?php
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

            if ( 'yes' == $this->debug )
                $this->log->add( 'white', 'Generating payment form for order ' . $order->get_order_number() . '. Notify URL: ' . $this->notify_url );

            // White Args
            $white_args = array(
                'description' => "WooCommerce charge for ".$order->billing_email,
                'card' => $_POST['whiteToken'],
                'currency' => get_woocommerce_currency(),
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

            try {
                if ($this->test_mode == 'yes') {
                    Payfort::setApiKey($this->test_secret_key);
                } else {
                    Payfort::setApiKey($this->live_secret_key);
                }

                // Charge the token
                $charge = Payfort_Charge::create($white_args);

                // No exceptions? Yaay, all done!
                $order->payment_complete();
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url( $order )
                );

            } catch (Payfort_Error $e) {
                // TODO: Can we get the extra params (so the error is more apparent)?
                // e.g. Instead of "request params are invalid", we get
                // "extras":{"amount":["minimum amount (in the smallest currency unit) is 185 for AED"]
                $message = __('Error:', 'woothemes') . $e->getMessage();

                // If function should we use?
                if(function_exists("wc_add_notice")) {
                    // Use the new version of the add_error method
                    wc_add_notice($message);
                } else {
                    // Use the old version
                    $woocommerce->add_error($message);
                }

                return;
            }
        }


        /**
         * Preload the checkout.js script so that the iframe is properly loaded by
         * the time the user checks outs of the page.
         */
        function payfort_preload_checkout() {
          ?>
          <script src="https://beautiful.start.payfort.com/checkout.js"></script>
          <script>
          StartCheckout.config({
            key: "<?php echo $this->test_mode == 'yes'? $this->test_open_key : $this->live_open_key ?>",
            complete: function(params) {
              // whiteCallback()
              console.log('Here is our final params:', params.token.id, params.email);
            }
          });
          </script>
          <?php
        }
    }

    /**
     * Add the gateway to WooCommerce
     **/
    function add_white_gateway($methods){
        $methods[] = 'WC_Gateway_White';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_white_gateway');

}
