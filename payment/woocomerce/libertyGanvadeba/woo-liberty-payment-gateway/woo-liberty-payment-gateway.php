<?php
/**
 * Plugin Name: WooCommerce Liberty Payment Gateway
 * Plugin URI:  http://intellect.ge/
 * Description: This plugin allows you to accept credit and debit card payments in your WooCommerce shop via Liberty bank gateway
 * Version:     1.0.1
 * Author:      Mixas
 * Author URI:  http://intellect.ge/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: woo-liberty
 */
/**
 * Don't open this file directly!
 */
if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Composer autoload magic
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * libertypay-php is namespaced
 */
use WeAreDe\TbcPay\TbcPayProcessor;

/**
 * Register the gateway for use
 */
function add_woo_gateway_liberty_class($methods)
{
    $methods[] = 'WC_Gateway_LIBERTY';
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_woo_gateway_liberty_class');

/**
 * Initialize class on plugins_loaded
 */
function init_woo_gateway_liberty()
{

    /**
     * Liberty credit card payment gateway class
     *
     * @class   WC_Gateway_LIBERTY
     * @extends WC_Payment_Gateway
     */
    class WC_Gateway_LIBERTY extends WC_Payment_Gateway
    {

        /**
         * @var boolean Enabled or disable logging
         * @static
         */
        public static $log_enabled = false;


        /**
         * @var boolean WC_Logger instance
         * @static
         */
        public static $log = false;

        /**
         * Constructor
         */
        function __construct()
        {
            // Woo required settings
            $this->id = 'liberty';
            $this->has_fields = false;
            $this->order_button_text = __('Proceed to Liberty', 'woo-liberty');
            $this->method_title = __('Liberty VISA/MASTERCARD', 'woo-liberty');
            $this->method_description = sprintf(__('Liberty sends customers Liberty to enter their payment information. Liberty IPN requires cURL support. Check the %ssystem status%s page for more details.', 'woo-liberty'), '<a href="' . admin_url('admin.php?page=wc-status') . '">', '</a>');
            $this->supports = array(
                'products'
            );

            // Gateway settings
            $this->payment_form_url = '/wc-api/redirect_to_lib_payment_form?transaction_id=%s';

            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables



            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->debug = 'yes' === $this->get_option('debug', 'no');
            $this->testmode = 'yes' === $this->get_option('testmode', 'no');
            $this->cert_path = $this->get_option('cert_path');
            $this->cert_pass = $this->get_option('cert_pass');
            $this->ok_slug = $this->get_option('ok_slug');
            $this->fail_slug = $this->get_option('fail_slug');
            $this->codename = $this->get_option('codename');
            $this->secretkey = $this->get_option('secretkey');

            self::$log_enabled = $this->debug;

            // Hooks
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
            add_action('admin_notices', array($this, 'admin_notices'));
            add_action('woocommerce_admin_order_data_after_order_details', array($this, 'order_details'));
            add_action('woocommerce_api_redirect_to_lib_payment_form', array($this, 'redirect_to_payment_form'));
            add_action('woocommerce_api_' . $this->ok_slug, array($this, 'return_from_payment_form_ok'));
            add_action('woocommerce_api_' . $this->fail_slug, array($this, 'return_from_payment_form_fail'));
            add_action('woocommerce_api_close_business_day', array($this, 'close_business_day'));
            add_action('woocommerce_api_is_wearede', array($this, 'is_wearede_plugin'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // wearede\libertypay-php
            $this->Liberty = new TbcPayProcessor($this->cert_path, $this->cert_pass, $_SERVER['REMOTE_ADDR']);
        }

        /**
         * Create a log entry
         *
         * @param string $message
         * @uses  WC_Gateway_LIBERTY::$log_enabled
         * @uses  WC_Gateway_LIBERTY::$log
         * @static
         */
        public static function log($message)
        {
            if (is_object($message) || is_array($message))
            {


                ob_start();
                echo '=================================';
                echo "\r\n";
                echo date('Y-m-d H:i:s');
                echo "\r\n";
                print_r($message);
                echo "\r\n";
                echo '=================================';
                echo "\r\n";
                $html = ob_get_clean();

                file_put_contents('liberty_dump.txt', $html, FILE_APPEND);
            } else
            {
                if (self::$log_enabled)
                {
                    if (empty(self::$log))
                    {
                        self::$log = new WC_Logger();
                    }
                    self::$log->add('liberty', $message);
                }
            }
        }

        /**
         * Initialise gateway settings
         */
        public function init_form_fields()
        {
            $this->form_fields = include( 'includes/gateway-settings.php' );
        }

        /**
         * Display notices in admin dashboard
         *
         * Check if required parameters: cert_path and cert_pass are set.
         * Display errors notice if they are missing,
         * both of these parameters are required for correct functioning of the plugin.
         * Check happens only when plugin is enabled not to clutter admin interface.
         *
         * @return null|void
         */
        public function admin_notices()
        {
            if ($this->enabled == 'no')
            {
                return;
            }

            // Check for required parameters
//			if ( ! $this->cert_path ) {
//				echo '<div class="error"><p>' . sprintf( __( 'Liberty error: Please enter certificate path <a href="%s">here</a>', 'woo-liberty' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_liberty') ) . '</p></div>';
//			}
//
//			if ( ! $this->cert_pass ) {
//				echo '<div class="error"><p>' . sprintf( __( 'Liberty error: Please enter certificate passphrase <a href="%s">here</a>', 'woo-liberty' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_liberty') ) . '</p></div>';
//			}
        }

        /**
         * Convert currency code to number
         *
         * e.g. USD -> 840
         *
         * @param  string $code currency code
         * @return string currency number
         */
        public function get_iso4217_number($code)
        {
            $iso4217 = new Alcohol\ISO4217();
            $currency = $iso4217->getByAlpha3($code);
            return $currency['numeric'];
        }

        /**
         * Process the payment
         *
         * This runs on ajax call from checkout page, when user clicks pay button
         *
         * @param  integer $order_id
         * @uses   WC_Gateway_LIBERTY::get_iso4217_number()
         * @uses   WC_Gateway_LIBERTY::get_payment_form_url()
         * @return array
         */
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $currency = $order->get_currency() ? $order->get_currency() : get_woocommerce_currency();
            $amount = $order->get_total();


            $items = $order->get_items();




            // Special data transformation for Liberty API
            $this->Liberty->amount = $amount * 100;
            $this->Liberty->currency = $this->get_iso4217_number($currency);
            $this->Liberty->description = sprintf(__('%s - Order %s', 'woo-liberty'), wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $order->get_id());
            $this->Liberty->language = strtoupper(substr(get_bloginfo('language'), 0, -3));

            // Log order details
            $this->log(sprintf(__('Info ~ Order id: %s - amount: %s (%s) %s (%s), language: %s.', 'woo-liberty'), $order->get_id(), $amount, $this->Liberty->amount, $currency, $this->Liberty->currency, $this->Liberty->language));

            // init contact with Liberty
//			try {
//				$start = $this->Liberty->sms_start_transaction();
//				if ( ! isset($start['error']) && isset($start['TRANSACTION_ID']) ) {
//					$trans_id = $start['TRANSACTION_ID'];
//				} else {
//					if ( isset($start['error']) ) {
//						// Log returned error
//						$this->log( sprintf( __( 'Error ~ Order id: %s - Error msg: %s.', 'woo-liberty' ), $order->get_id(), $start['error'] ) );
//					} else {
//						// Log generic error
//						$this->log( sprintf( __( 'Error ~ Order id: %s - no TRANSACTION_ID from Liberty.', 'woo-liberty' ), $order->get_id() ) );
//					}
//					throw new Exception( __( 'Liberty did not return TRANSACTION_ID.', 'woo-liberty' ) );
//				}
//			} catch ( Exception $e ) {
//				// Add private note to order details
//				$order_note = $e->getMessage();
//				$order->update_status( 'failed', $order_note );
//
//				return array(
//					'result' => 'failure',
//				);
//			}

            $this->log(sprintf(__('Success ~ Order id: %s -> transaction id: %s obtained successfully', 'woo-liberty'), $order->get_id(), $trans_id));

            // Save trans_id for reference
            update_post_meta($order->get_id(), '_transaction_id', $trans_id);

            $this->log(sprintf(__('Info ~ Order id: %s, redirecting user to Liberty gateway', 'woo-liberty'), $order->get_id()));

            return array(
                'result' => 'success',
                'messages' => __('Success! redirecting to Liberty now ...', 'woo-liberty'),
                // Redirect user to liberty payment form
                'redirect' => $this->get_payment_form_url($order->get_id()),
            );
        }

        /**
         * OK endpoint
         *
         * Landing page for customers returning from Liberty after payment
         * here we verify that transaction was indeed successful
         * and update order status accordingly
         *
         * @uses WC_Gateway_LIBERTY::get_order_id_by_transaction_id()
         */
        public function return_from_payment_form_ok()
        {
            $this->log($_REQUEST);
            try
            {

                $data = $this->validcallchek();
                if ($data['valid'] == 0)
                {
                    $this->returnResult('-3', 'Error in parametr', 'data valid = 0');
                }
                

                if (!$data['ordercode'])
                {
                    $this->returnResult('-2', 'Order not found', 'data ordercode = 0');
                }
                $order_id = $data['ordercode'];
                $order = wc_get_order($order_id);
                
                
                
                if (!$order)
                {
                    $this->returnResult('-2', 'Order not found', 'data ordercode = 0');
                }
            } catch (Exception $e)
            {
                $this->returnResult('-3', 'Error in parametr', 'data valid = 0');
                exit();
            }
            try
            {
                if (!isset($data['status']))
                {
                    $this->returnResult('-3', 'Error in parametr', 'data status = 0');
                }
                switch ($data['status'])
                {
                    case 'REVIEW':

                        $order_note = 'DISCARDED';
                        $order->update_status('pending', $order_note);
                        $this->returnResult('0', 'Ok', 'Ok');

                        break;
                    case 'DISCARDED':

                        $order_note = 'DISCARDED';
                        $order->update_status('failed', $order_note);
                        $this->returnResult('0', 'Ok', 'Ok');
                        break;
                    case 'APPROVED':
                        // Payment complete
                        $order->payment_complete();
                    
                        // Add order note
                        $complete_message = __('Liberty charge complete', 'woo-liberty');
                        $order->add_order_note($complete_message);
                        $this->log(sprintf(__('Success ~ %s, transaction id: %s, order id: %s', 'woo-liberty'), $complete_message, $trans_id, $order_id));

                        // Remove cart
                        WC()->cart->empty_cart();
                        $this->returnResult('0', 'Ok', 'Ok');
                        break;
                    default:
                        $this->returnResult('-3', 'Error in parametr', 'data status = 0');
                        break;
                }
            } catch (Exception $e)
            {
                $this->returnResult('-3', 'Error in parametr', 'exception 2');
            }
            // redirect to thank you

            exit();
        }

        /**
         * FAIL endpoint
         *
         * Landing page for customers returning from Liberty after technical failure
         * this can be improved by logging logged in user details
         */
        public function return_from_payment_form_fail()
        {
            $error = __('Technical faulure in ECOMM system', 'woo-liberty');
            $this->log(sprintf(__('Error ~ %s', 'woo-liberty'), $error));
            wp_die($error);
        }

        /**
         * Create payment form url
         *
         * Add transaction_id to payment_form_url
         *
         * @param  string $trans_id
         * @return string
         */
        public function get_payment_form_url($trans_id)
        {
            return sprintf($this->payment_form_url, rawurlencode($trans_id));
        }

        public function validcallchek()
        {





            $param = $_GET;
            if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'Debug')
            {
                $param["status"] = 'APPROVED';
                $param["installmentid"] = '333666'; // ganvadebis id
                $param["ordercode"] = '2012';
                $param["callid"] = '2015564654';
                $param['check'] = $check = strtoupper(hash('sha256', $param["status"] . $param["installmentid"] . $param["ordercode"] . $param["callid"] . 'wonge777'));
            }

            $validate = array();
            $validate['valid'] = 0;
            if (count($param) != 0)
            {
                $secretkey = $this->secretkey;
                $status = urldecode($param["status"]);
                $installmentid = urldecode($param['installmentid']);
                $ordercode = urldecode($param['ordercode']);
                $callid = urldecode($param['callid']);
                $check = urldecode($param['check']);


                $str = $status . $installmentid . $ordercode . $callid;
                $str .= $secretkey;
                $calculatedCheck = hash('sha256', $str);

                if (strcasecmp($check, $calculatedCheck) == 0)
                {
                    $validate['valid'] = 1;
                    $validate['status'] = $status;
                    $validate['installmentid'] = $installmentid;
                    $validate['ordercode'] = $ordercode;
                    $validate['callid'] = $callid;
                }
            }

            return $validate;
        }

        public function returnResult($resultcode, $resultdesc = '', $transactioncode = '')
        {

            $check = hash('sha256', $resultcode . $resultdesc . $transactioncode . $this->secretkey);

            $xmlstr = <<<XML
<result>
<resultcode>$resultcode</resultcode>
<resultdesc>$resultdesc</resultdesc>
<check>$check</check>
<data>$transactioncode</data>
</result>
XML;




            header('Content-type: text/xml');
            die($xmlstr);
        }

// returnResult

        /**
         * Redirect user to Liberty payment page
         */
        public function redirect_to_payment_form()
        {

            $merchant = $this->codename;
            $secretkey = $this->secretkey;


            $testmode = empty($this->testmode) ? '0' : '1';
            //   $testmode = 1;



            $order_id = $_GET['transaction_id'];
            $order = wc_get_order($order_id);
            $callid = $order_id . time();
            $products = $order->get_items();


            $shipping_address = $order->data['shipping']['address_1'];


            $str = $secretkey
                    . $merchant
                    . $order_id
                    . $callid
                    . htmlentities($shipping_address)
                    . $testmode;

            foreach ($products as $product)
            {
                $item = $product->get_product();
                $str .= $item->get_id() . htmlentities($item->get_title()) . $product->get_quantity() . $product->get_total() . '0' . '0';
            }



            $check = strtoupper(hash('sha256', $str));
            $action = 'http://onlineinstallment.lb.ge/installment/';
            ?><html>
                <head>
                    <title><?php $this->title; ?></title>
                                <script type="text/javascript" language="javascript">
                        function redirect() {
                            document.returnform.submit();
                        }
                    </script>
                </head>

                <body onLoad="javascript:redirect()">

                    <form name="returnform" method="post" action="<?php echo $action; ?>">
                        <input type="hidden" name="merchant"    value="<?php echo $merchant; ?>" />
                        <input type="hidden" name="ordercode"   value="<?php echo htmlentities($order_id); ?>" />
                        <input type="text"   name="callid"        value="<?php echo htmlentities($callid); ?>" />
                        <input type="hidden" name="shipping_address"   value="<?php echo htmlentities($shipping_address, ENT_QUOTES, 'UTF-8'); ?>" />
                        <input type="hidden" name="testmode"    value="<?php echo $testmode; ?>" />
                        <input type="hidden" name="check"       value="<?php echo $check; ?>" />
            <?php
            $key = 0;
            foreach ($products as $i => $value)
            {
                $item = $value->get_product();
                ?>
                            <input type="hidden" name="products[<?php echo $key; ?>][id]"      value="<?php echo $item->get_id(); ?>" />
                            <input type="hidden" name="products[<?php echo $key; ?>][title]"   value="<?php echo htmlentities($item->get_title(), ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" name="products[<?php echo $key; ?>][amount]"  value="<?php echo $value->get_quantity(); ?>" />
                            <input type="hidden" name="products[<?php echo $key; ?>][price]"   value="<?php echo $value->get_total(); ?>" />
                            <input type="hidden" name="products[<?php echo $key; ?>][cashprice]"   value="<?php echo $value->get_total(); ?>" />
                            <input type="hidden" name="products[<?php echo $key; ?>][type]"   value="<?php echo 0; ?>" />
                            <input type="hidden" name="products[<?php echo $key; ?>][installmenttype]"   value="0" />


                <?php
                $key++;
            }
            ?>        
                        <input type="submit" value="Continue" />
                    </form>
                </body>

            </html>

            <?php
            exit();
        }

        /**
         * Used for troubleshooting
         */
        public function is_wearede_plugin()
        {
            echo json_encode(array('status' => 'true', 'version' => '1.0.2'));
            exit();
        }

        /**
         * payment_scripts function.
         *
         * Outputs scripts used for Liberty payment
         *
         * @access public
         */
        public function payment_scripts()
        {
            if (!is_checkout() && !$this->is_available())
            {
                return;
            }

            wp_enqueue_script('woocommerce_liberty_checkout', plugins_url('js/liberty_checkout.js', __FILE__), array('jquery'), '0.1', true);
        }

        /**
         * Add gateway data to (edit) order page
         *
         * This is redundant because woo already displays _transaction_id in the order description
         * e.g. "Payment via {gateway} ({_transaction_id}). Customer IP: {ip}"
         * but we can use it for other things in the future.
         *
         * @param object $order
         */
        public function order_details($order)
        {
            ?>

            <div class="order_data_column">
                <h4><?php _e('Liberty'); ?></h4>
            <?php
            echo '<p><strong>' . __('Transaction id', 'woo-liberty') . ':</strong>' . get_post_meta($order->get_id(), '_transaction_id', true) . '</p>';
            ?>
            </div>

                <?php
            }

            /**
             * Close business day
             *
             * Required by gateway
             * must run every 24 hours via cron
             */
            public function close_business_day()
            {
                print_r($this->Liberty->close_day());
                exit();
            }

            /**
             * Get order id by transaction id
             *
             * @param  string $trans_id
             * @return string $order_id
             */
            public function get_order_id_by_transaction_id($trans_id)
            {
                global $wpdb;

                $meta = $wpdb->get_results(
                        $wpdb->prepare(
                                "
					SELECT * FROM $wpdb->postmeta
					 WHERE meta_key = '_transaction_id'
					   AND meta_value = %s
					 LIMIT 1
					", $trans_id
                        )
                );

                if (!empty($meta) && is_array($meta) && isset($meta[0]))
                {
                    $meta = $meta[0];
                }

                if (is_object($meta))
                {
                    return $meta->post_id;
                }

                return false;
            }

        }

    }

    add_action('plugins_loaded', 'init_woo_gateway_liberty');
    