=== Recommend Referral, Integration Plugin ===
Contributors: denisrcmnd
Donate link: https://about.recommend.co
Tags: recommend, referral, webshop, integration, codes
Requires at least: 4.6
Tested up to: 6.1
Requires PHP: 5.6
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Recommend Referral is an integration referral plugin for WordPress, integrated with the Woocommerce shop and based on an external API service.

== Description ==

Recommend Referral is a flexible and easy to use integration plugin that allows you to integrate your Woocommerce store with Recommend platform using Recommend API service.

Input your Recommend Referral API key and select the mode of operation. Find your API Key within the Integration module in Recommend.

The plugin integrates into woocommerce hooks on the product page, cart, payment and payment confirmation.

By using additional parameters in the product link, the plugin catches campaigns on the Recommend platform.

After a successful product purchase, the plugin communicates with the Recommend API service and checks for conversions.

If you are testing plugin set "Testing?" checkbox to true. In testing mode, plugin will send referral check after adding product to cart.


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `rcmnd-referral.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Frequently Asked Questions ==

= Any questions? =

All the answers are on our site (at least we try): https://about.recommend.co/api-docs

== Screenshots ==

1. Product page (screenshots/SS1_ProductCard.png)
2. Recommend referral in action (screenshots/SS2_ProductCart.png)
3. Checkout page (screenshots/SS3_ThankYouPage.png)
4. Options panel (screenshots/SS4_OptionsPanel.png)


== Changelog ==

= 1.2 =
* Session Starting Fix. Test mode fix. Tested up to 6.0.3.

= 1.1 =
* Stable release.

= 0.5 =
* First beta release.

== Upgrade Notice ==

= 0.5 =
First beta release.
