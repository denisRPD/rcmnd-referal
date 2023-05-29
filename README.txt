=== Recommend Marketing, Integration Plugin ===
Contributors: denisrcmnd
Donate link: https://www.recommend.co/for-developers
Tags: recommend, referral, webshop, integration, marketing
Requires at least: 4.6
Tested up to: 6.2.2
Requires PHP: 5.6
Stable tag: 1.3.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Recommend Marketing is an integration referral plugin for WordPress, integrated with the Woocommerce shop and based on an external API service.

== Description ==

Recommend Marketing is a flexible and easy to use integration plugin that allows you to integrate your Woocommerce store with Recommend platform using Recommend API service.

Input your Recommend Marketing API key and select the mode of operation. Find your API Key within the Integration module in Recommend.

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

All the answers are on our site (at least we try): https://www.recommend.co/for-developers

== Screenshots ==

1. Settings page (screenshots/screenshot-1.png)
2. Settings page sync success (screenshots/screenshot-2.png)
3. Product settings page (screenshots/screenshot-3.png)
4. Product page with referral (screenshots/screenshot-4.png)
5. Checkout page - payment success with conversion (screenshots/screenshot-5.png)
6. Order oage - conversion info with referral codes (screenshots/screenshot-6.png)


== Changelog ==

= 1.3.11 =
* Session data removed - cookies added for tracking conversion codes. Code cleanup.

= 1.3.10 =
* Syncronization options changed to be optional. Minor fixes in remote API calls.

= 1.3.9 =
* Single product sync and categories added to settings. Card payment fetching conversion fixed. User sessionId added to request. ReadMe updated.

= 1.3.8 =
* Test mode fixes.

= 1.3.7 =
* Product sync option updated.

= 1.3.6 =
* Conversion status update added to orders.
* Product sync option added - automatic and initial sync on settings update.

= 1.3.5 =
* OrderId and CartTotal added to request for conversion.

= 1.3.4 =
* Code cleanup.

= 1.3.3 =
* Session Call Fix.

= 1.3.2 =
* Register Function Fix in Admin page.

= 1.3.1 =
* Minor Fixes.

= 1.3 =
* Production Fixes.

= 1.2 =
* Session Starting Fix. Test mode fix. Tested up to 6.0.3.

= 1.1 =
* Stable release.

= 0.5 =
* First beta release.

== Upgrade Notice ==

= 0.5 =
First beta release.
