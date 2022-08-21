<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://recommend.co
 * @since      1.1
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.1
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 * @author     Recommend Inc. <admin@recommend.co>
 */
class Rcmnd_referral_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rcmnd',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
