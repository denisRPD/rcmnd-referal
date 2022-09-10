<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://recommend.co
 * @since      1.1
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.1
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 * @author     Recommend Inc. <admin@rrecommend.co>
 */
class Rcmnd_referral {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.1
	 * @access   protected
	 * @var      Rcmnd_referral_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.1
	 */
	public function __construct() {
		if ( defined( 'RCMND_VERSION' ) ) {
			$this->version = RCMND_VERSION;
		} else {
			$this->version = '1.1';
		}
		$this->plugin_name = 'rcmnd-referal';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}
	
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rcmnd_referral_Loader. Orchestrates the hooks of the plugin.
	 * - Rcmnd_referral_i18n. Defines internationalization functionality.
	 * - Rcmnd_referral_Admin. Defines all hooks for the admin area.
	 * - Rcmnd_referral_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rcmnd-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rcmnd-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rcmnd-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rcmnd-public.php';
		

		$this->loader = new Rcmnd_referral_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the class-rcmnd-i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.1
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Rcmnd_referral_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.1
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Rcmnd_referral_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_general_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_advanced_settings' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rcmnd_referral_Public( $this->get_plugin_name(), $this->get_version() ); // Init plugin

		$this->loader->add_action( 'wp_enqueue_styles', $plugin_public, 'enqueue_styles' ); // Enqueue styles
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' ); // Enqueue scipts
			
		$this->loader->add_action( 'init', $plugin_public, 'set_rcmndID_cookie' ); // On init fetch recommend referral code into cookie
        $this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_public, 'filter_woocommerce_cart_item_name', 1, 3 );
        
		// Check if test mode is active
		$gso_options = get_option( 'rcmnd_gso' );
		$is_test = ( isset($gso_options['rcmnd_pkey'] ) ) ? $gso_options['rcmnd_pkey'] : '';	
		$is_test_mode = ($is_test == 'on') ? true : false; 
		
		
        if($is_test_mode){
            $this->loader->add_action( 'woocommerce_add_to_cart', $plugin_public, 'rcmnd_check_referral_test',1,6 ); // Check referral code on add to cart action (TEST MODE)
        }
        else{
			// Show response on add to cart and thankyour pages (PRODUCTION MODE)
            $this->loader->add_action( 'woocommerce_add_to_cart', $plugin_public, 'rcmnd_addedtocart',1,6 );
            //$this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $plugin_public, 'rcmnd_check_referral_order_additional_text',1,2);
			
			$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_public, 'rcmnd_check_referral_prod',10,1 );
            $this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'rcmnd_check_referral_prod_message',1,6 );
        }

		// Show recommend referral notice below add to cart button
        $this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'rcmnd_after_add_to_cart_notice' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.1
	 * @return    Af_Densel_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


}
