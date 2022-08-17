<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://recommnd.io
 * @since      1.0.0
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
 * @since      1.0.0
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 * @author     Recommend Inc. <info@rcmnd.co>
 */
class Rcmnd_referral {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Rcmnd_referral_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RCMND_VERSION' ) ) {
			$this->version = RCMND_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'rcmnd-referal';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		
		// Hook into the admin menu
        add_action( 'admin_menu', array( $this, 'create_plugin_settings_page'));
        
         // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );

	}
	
	public function create_plugin_settings_page() {
    // Add the menu item and page
    $page_title = __( 'Recommend Referral Integration Settings', 'rcmnd' );
    $menu_title = 'Recommend';
    $capability = 'manage_options';
    $slug = 'rcmnd';
    $callback = array( $this, 'plugin_settings_page_content' );
    $icon = plugins_url( 'rcmnd-referal/images/rcmnd-logo.ico' );
    $position = 100;

    add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
}





    public function plugin_settings_page_content() {?>
    	<div class="wrap">
        		<div style="display:inline-block;width: 100%;max-width: 500px;">
        		    <div style="width: 5%;float: left;"><img style="margin: 1.4em 0;" src="/wp-content/plugins/rcmnd-referal/images/rcmnd-logo.ico"></img></div>
        		    <div style="width: 90%;float: left;"><h2>Recommend Referral Integration Settings</h2></div>
        		</div>
        		<?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                
                    $pkey = get_option( 'rcmnd_pkey', '' );
                    $testApi = get_option( 'rcmnd_tapi', '' );
                    $url = $testApi;//'https://betapi.densel.hr/Token';
                    
					$url = 'https://api.recommnd.io/apikeys';
					
                    // Create a new cURL resource
                    $ch = curl_init($url);
                    
                    // Setup request to send json via POST
                    $data = array(
                        'apiToken' => $pkey,
                    );
                    $payload = json_encode($data);
                    
                    // Attach encoded JSON string to the POST fields
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    
                    // Set the content type to application/json
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    
                    // Return response instead of outputting
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                    // Execute the POST request
                    $result = curl_exec($ch);
            		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    
                    // Close cURL resource
                    curl_close($ch);
                    
                    if ($httpcode === 200) {
                        $this->admin_notice();
                    }else{
                        $this->admin_error_notice();
                    }
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'rcmnd_fields' );
                    do_settings_sections( 'rcmnd_fields' );
                    submit_button(__( 'Save changes', 'rcmnd' ), 'primary');
                ?>
    		</form>
    	</div> <?php
    
}

    
    


    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Settings saved! Connection OK!</p>
        </div><?php
    }
    
    public function admin_error_notice() { ?>
        <div class="notice notice-error is-dismissible">
            <p>Settings saved! Could not connect!</p>
            <span>Check your API Key and try again...</span>
        </div><?php
    }

    public function setup_sections() {
        add_settings_section( 'our_first_section', 'Authentification', array( $this, 'section_callback' ), 'rcmnd_fields' );
    }

    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'our_first_section':
    			echo '';
    			break;
    	}
    }

    public function setup_fields() {
        $fields = array(
        	array(
        		'uid' => 'rcmnd_pkey',
        		'label' => 'API Key',
        		'section' => 'our_first_section',
        		'type' => 'text',
        		'placeholder' => 'Enter your Key...',
        		'helper' => '',
        		'supplimental' => 'Find your API Key within the Integration module in Recommend.',
        	),

        	/*array(
        		'uid' => 'rcmnd_tapi',
        		'label' => 'Api URL',
        		'section' => 'our_first_section',
        		'type' => 'text',
        		'placeholder' => 'Enter API URL...',
        		'helper' => '',
        		'supplimental' => 'Enter your testing API URL here.',
        	),*/
        	array(
        		'uid' => 'rcmnd_istest',
        		'label' => 'Testing?',
        		'section' => 'our_first_section',
        		'type' => 'checkbox',
        		'helper' => '',
        		'supplimental' => 'If you are testing plugin set this checkbox. In testing mode, plugin will send referral check after adding product to cart.',
        	)
        );
    	foreach( $fields as $field ){

        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'rcmnd_fields', $field['section'], $field );
            register_setting( 'rcmnd_fields', $field['uid'] );
    	}
    }

    public function field_callback( $arguments ) {

        $value = get_option( $arguments['uid'] );

        if( ! $value ) {
            $value = $arguments['default'];
        }

        switch( $arguments['type'] ){
            case 'text': // If it is a text field
                printf( '<input class="rcmnd-inputfields" name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'checkbox': // If it is a text field
                $checked = ($value == 'on' ) ? 'checked' : '';
                printf( '<input name="%1$s" id="%1$s" type="%2$s" %3$s />', $arguments['uid'], $arguments['type'], $checked );
            break;
        }

        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper );
        }

        if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }

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
	 * @since    1.0.0
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
		
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rcmnd-referral.php';

		$this->loader = new Rcmnd_referral_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Af_Densel_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Rcmnd_referral_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rcmnd_referral_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		 $plugin_referral = new Rcmnd_referral_Worker();
		
		 $this->loader->add_action( 'init', $plugin_referral, 'set_rcmndID_cookie' );


        $this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_referral, 'filter_woocommerce_cart_item_name', 1, 3 );
        

        $is_test_mode = (get_option( 'rcmnd_istest', '' ) == 'on') ? true : false;
        
        if($is_test_mode){
            $this->loader->add_action( 'woocommerce_add_to_cart', $plugin_referral, 'rcmnd_check_referral',1,6 );
        }
        else{
            $this->loader->add_action( 'woocommerce_add_to_cart', $plugin_referral, 'rcmnd_addedtocart',1,6 );
            
            $this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $plugin_referral, 'rcmnd_check_referral_order_additional_text',1,2);
            
            $this->loader->add_action( 'woocommerce_thankyou', $plugin_referral, 'rcmnd_check_referral_order',1,6 );
        }
        
		
		 
		 
        $this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_referral, 'rcmnd_after_add_to_cart_notice' );
		 
		   
        
		 
		 // add_action( 'init', array( $this, 'set_rcmndID_cookie' ) );
	//	add_action( 'woocommerce_add_to_cart', array( $this, 'rcmnd_check_referral' )); 
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Af_Densel_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	
    
   

}
