# ArQmA Gateway for WooCommerce
[![Build Status](https://travis-ci.com/ArqTras/arqmawp.svg?branch=master)](https://travis-ci.org/arqtras/arqmawp)

## Features

* Payment validation done through either `arqma-wallet-rpc` or the [blocks.arqma.com blockchain explorer](https://blocks.arqma.com/).
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of Arqma.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* Arqma wallet to receive payments - [GUI](https://github.com/arqma/arqma/releases)
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/arqma/arqmawp) or clone with `git clone https://github.com/arqma/arqmawp`
* Unzip or place the `arqma-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "Arqma Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

## Option 1: Use your wallet address and viewkey

This is the easiest way to start accepting Arqma on your website. You'll need:

* Your Arqma wallet address starting with `a`
* Your wallet's secret viewkey

Then simply select the `viewkey` option in the settings page and paste your address and viewkey. You're all set!

Note on privacy: when you validate transactions with your private viewkey, your viewkey is sent to (but not stored on) blocks.arqma.com over HTTPS. This could potentially allow an attacker to see your incoming, but not outgoing, transactions if they were to get his hands on your viewkey. Even if this were to happen, your funds would still be safe and it would be impossible for somebody to steal your money. For maximum privacy use your own `arqma-wallet-rpc` instance.

## Option 2: Using `arqma-wallet-rpc`

The most secure way to accept ArQmA on your website. You'll need:

* Root access to your webserver
* Latest [Arqma-currency binaries](https://github.com/arqma/arqma/releases)

After downloading (or compiling) the Arqma binaries on your server, install the [systemd unit files](https://github.com/monero-integrations/monerowp/tree/master/assets/systemd-unit-files) or run `arqmad` and `arqma-wallet-rpc` with `screen` or `tmux`. You can skip running `arqmad` by using a remote node with `arqma-wallet-rpc` by adding `--daemon-address us.supportarqma.com:19994` to the `arqma-wallet-rpc.service` file.

Note on security: using this option, while the most secure, requires you to run the Arqma wallet RPC program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds.

## Configuration

* `Enable / Disable` - Turn on or off Arqma gateway. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: Arqma Gateway)
* `Discount for using Arqma` - Percentage discount applied to orders for paying with Arqma. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `Confirmation Type` - Confirm transactions with either your viewkey, or by using `arqma-wallet-rpc`. (Default: viewkey)
* `Arqma Address` (if confirmation type is viewkey) - Your public Arqma address starting with a. (No default)
* `Secret Viewkey` (if confirmation type is viewkey) - Your *private* viewkey (No default)
* `Arqma wallet RPC Host/IP` (if confirmation type is `arqma-wallet-rpc`) - IP address where the wallet rpc is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `Arqma wallet RPC port` (if confirmation type is `arqma-wallet-rpc`) - Port the wallet rpc is bound to with the `--rpc-bind-port` argument. (Default 19996)
* `Testnet` - Check this to change the blockchain explorer links to the testnet explorer. (Default: unchecked)
* `SSL warnings` - Check this to silence SSL warnings. (Default: unchecked)
* `Show QR Code` - Show payment QR codes. There is no Arqma software that can read QR codes at this time (Default: unchecked)
* `Show Prices in Arqma` - Convert all prices on the frontend to Arqma. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in Arqma is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded and will be displayed down to the nanoArqma. (Default: 11)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of Arqma in the selected currency. If no currency is provided, the store's default currency will be used.

```
[Arqma-price]
[Arqma-price currency="BTC"]
[Arqma-price currency="USD"]
[Arqma-price currency="CAD"]
[Arqma-price currency="EUR"]
[Arqma-price currency="GBP"]
```
Will display:
```
1 ARQ = 123.68000 USD
1 ARQ = 0.01827000 BTC
1 ARQ = 123.68000 USD
1 ARQ = 168.43000 CAD
1 ARQ = 105.54000 EUR
1 ARQ = 94.84000 GBP
```


#### Arqma accepted here badge

This will display a badge showing that you accept Arqma-currency.

`[arqma-accepted-here]`

![Arqma Accepted Here](/assets/images/arqma-accepted-here.png?raw=true "Arqma Accepted Here")

## Credits

Credit is due to @mosu-forge @cryptochangements34 and @SerHack for their monerowp plugin that this is based on.

## Donations

monero-integrations: 44krVcL6TPkANjpFwS2GWvg1kJhTrN7y9heVeQiDJ3rP8iGbCd5GeA4f3c2NKYHC1R4mCgnW7dsUUUae2m9GiNBGT4T8s2X
mosu_forge: ar4Ki4t462uUCwrdaT12W49oudwkonrbG9yT1Y5s4RZUYjMDJg1fZMS57HefnjAWpn2tjjeQpNHqyH2KbXawgJWU1Lg8BYtKe
ArQmA Network: 32DEr9inVahpfYL8NSLFHVftJqY3Rb1tUb
