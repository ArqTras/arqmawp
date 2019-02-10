<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'arqma_gateway'),
        'label' => __('Enable this payment gateway', 'arqma_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'arqma_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'arqma_gateway'),
        'default' => __('Arqma Payments', 'arqma_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'arqma_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'arqma_gateway'),
        'default' => __('Pay securely using Arqma-currency. You will be provided payment details after checkout.', 'arqma_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using Arqma', 'arqma_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with Arqma', 'arqma_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'arqma_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'arqma_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'arqma_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'arqma_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'arqma_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'arqma_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately four minutes', 'arqma_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'confirm_type' => array(
        'title' => __('Confirmation Type', 'arqma_gateway'),
        'desc_tip' => __('Select the method for confirming transactions', 'arqma_gateway'),
        'description' => __('Select the method for confirming transactions', 'arqma_gateway'),
        'type' => 'select',
        'options' => array(
            'viewkey'        => __('viewkey', 'arqma_gateway'),
            'arqma-wallet-rpc' => __('arqma-wallet-rpc', 'arqma_gateway')
        ),
        'default' => 'viewkey'
    ),
    'arqma_address' => array(
        'title' => __('Arqma Address', 'arqma_gateway'),
        'label' => __('Useful for people that have not a daemon online'),
        'type' => 'text',
        'desc_tip' => __('Arqma Wallet Address (a)', 'arqma_gateway')
    ),
    'viewkey' => array(
        'title' => __('Secret Viewkey', 'arqma_gateway'),
        'label' => __('Secret Viewkey'),
        'type' => 'text',
        'desc_tip' => __('Your secret Viewkey', 'arqma_gateway')
    ),
    'daemon_host' => array(
        'title' => __('Arqma wallet RPC Host/IP', 'arqma_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the Daemon Host/IP to authorize the payment with', 'arqma_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('Arqma wallet RPC port', 'arqma_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the Wallet RPC port to authorize the payment with', 'arqma_gateway'),
        'default' => '19999',
    ),
    'testnet' => array(
        'title' => __(' Testnet', 'arqma_gateway'),
        'label' => __(' Check this if you are using testnet ', 'arqma_gateway'),
        'type' => 'checkbox',
        'description' => __('Advanced usage only', 'arqma_gateway'),
        'default' => 'no'
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'arqma_gateway'),
        'label' => __('Show QR Code', 'arqma_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'show_identicon' => array(
        'title' => __('Show Identicon', 'arqma_gateway'),
        'label' => __('Show Identicon', 'arqma_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to an identicon after checkout with payment details.'),
        'default' => 'yes'
    ),
    'use_arqma_price' => array(
        'title' => __('Show Prices in Arqma', 'arqma_gateway'),
        'label' => __('Show Prices in Arqma', 'arqma_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to Arqma (experimental)'),
        'default' => 'no'
    ),
    'use_arqma_price_decimals' => array(
        'title' => __('Display Decimals', 'arqma_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 9,
    ),
);
