<?php
/*
Plugin Name: Arqma Payments Woocommerce Gateway
Plugin URI: https://github.com/arqma/arqma-payments-woocommerce-gateway
Description: Extends WooCommerce by adding a Arqma-currency Gateway
Version: 1.0.0
Tested up to: 4.9.8
Author: mosu-forge, SerHack, ArqTras
Author URI: https://arqma.com/
*/
// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('ARQMA_GATEWAY_MAINNET_EXPLORER_URL', 'https://blocks.arqma.com');
define('ARQMA_GATEWAY_TESTNET_EXPLORER_URL', 'https://blocks.arqma.com');
define('ARQMA_GATEWAY_ADDRESS_PREFIX', 0x2cca);            // ar
define('ARQMA_GATEWAY_ADDRESS_PREFIX_INTEGRATED', 0x116bc7); // aR
define('ARQMA_GATEWAY_ATOMIC_UNITS', 9);
define('ARQMA_GATEWAY_ATOMIC_UNIT_THRESHOLD', 10); // Amount payment can be under in atomic units and still be valid
define('ARQMA_GATEWAY_DIFFICULTY_TARGET', 120);

// Do not edit these constants
define('ARQMA_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARQMA_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ARQMA_GATEWAY_ATOMIC_UNITS_POW', pow(10, ARQMA_GATEWAY_ATOMIC_UNITS));
define('ARQMA_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.ARQMA_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'arqma_init', 1);
function arqma_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-arqma-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new Arqma_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-arqma-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'arqma_gateway');
    function arqma_gateway($methods) {
        $methods[] = 'Arqma_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'arqma_payment');
    function arqma_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=arqma_gateway_settings').'">'.__('Settings', 'arqma_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'arqma_cron_add_one_minute');
    function arqma_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'arqma_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'arqma_activate_cron');
    function arqma_activate_cron() {
        if(!wp_next_scheduled('arqma_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'arqma_update_event');
        }
    }

    add_action('arqma_update_event', 'arqma_update_event');
    function arqma_update_event() {
        Arqma_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.Arqma_Gateway::get_id(), 'arqma_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'arqma_order_page');
    add_action('woocommerce_email_after_order_table', 'arqma_order_email');

    function arqma_order_confirm_page($order_id) {
        Arqma_Gateway::customer_order_page($order_id);
    }
    function arqma_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            Arqma_Gateway::customer_order_page($order);
    }
    function arqma_order_email($order) {
        Arqma_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_arqma_gateway_payment_details', 'arqma_get_payment_details_ajax');
    function arqma_get_payment_details_ajax() {
        Arqma_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'arqma_add_currency');
    function arqma_add_currency($currencies) {
        $currencies['Arqma'] = __('Arqma', 'arqma_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'arqma_add_currency_symbol', 10, 2);
    function arqma_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'Arqma':
            $currency_symbol = 'Arqma';
            break;
        }
        return $currency_symbol;
    }

    if(Arqma_Gateway::use_arqma_price()) {

        // This filter will replace all prices with amount in Arqma (live rates)
        add_filter('wc_price', 'arqma_live_price_format', 10, 3);
        function arqma_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return Arqma_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should not for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'arqma_order_item_price_format', 10, 3);
        function arqma_order_item_price_format($price_html, $item, $order) {
            return Arqma_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'arqma_order_total_price_format', 10, 2);
        function arqma_order_total_price_format($price_html, $order) {
            return Arqma_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'arqma_order_totals_price_format', 10, 3);
        function arqma_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = Arqma_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'arqma_enqueue_scripts');
    function arqma_enqueue_scripts() {
        if(Arqma_Gateway::use_arqma_price())
            wp_dequeue_script('wc-cart-fragments');
        if(Arqma_Gateway::use_qr_code())
            wp_enqueue_script('arqma-qr-code', ARQMA_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');
        if(Arqma_Gateway::use_identicons())
            wp_enqueue_script('arqma-identicon', ARQMA_GATEWAY_PLUGIN_URL.'assets/js/blockies.min.js');

        wp_enqueue_script('arqma-clipboard-js', ARQMA_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('arqma-gateway', ARQMA_GATEWAY_PLUGIN_URL.'assets/js/arqma-gateway-order-page.js');
        wp_enqueue_style('arqma-gateway', ARQMA_GATEWAY_PLUGIN_URL.'assets/css/arqma-gateway-order-page.css');
    }

    // [arqma-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function arqma_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = Arqma_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.5f', $rate / 1e8);

        return "<span class=\"arqma-price\">1 Arqma = $rate_formatted $currency</span>";
    }
    add_shortcode('arqma-price', 'arqma_price_func');


    // [arqma-accepted-here]
    function arqma_accepted_func() {
        return '<img src="'.ARQMA_GATEWAY_PLUGIN_URL.'assets/images/arqma-accepted-here.png" />';
    }
    add_shortcode('arqma-accepted-here', 'arqma_accepted_func');

}

register_deactivation_hook(__FILE__, 'arqma_deactivate');
function arqma_deactivate() {
    $timestamp = wp_next_scheduled('arqma_update_event');
    wp_unschedule_event($timestamp, 'arqma_update_event');
}

register_activation_hook(__FILE__, 'arqma_install');
function arqma_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "arqma_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(16) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "arqma_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(16) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "arqma_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
