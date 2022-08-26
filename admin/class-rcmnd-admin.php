<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://recommend.co
 * @since      1.1
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/admin
 * @author     Recommend Inc. <admin@recommend.co>
 */
class Rcmnd_referral_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;


	/**
	 * The options name to be used in this plugin
	 *
	 * @since  	1.1
	 * @access 	private
	 * @var  	string 		$option_name 	Option name of this plugin
	 */
	private $option_name = 'rcmnd';


	/**
	 * The version of this plugin.
	 *
	 * @since    1.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.1
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rcmnd-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rcmnd-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * An options page under the Settings submenu
	 *
	 * @since  1.1
	 */
	 public function add_options_page() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Recommend Referral Integration Settings', 'rcmnd' ),
			__( 'Recommend', 'rcmnd' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);

	}
	 
	 /**
	 * Render the options page for plugin
	 *
	 * @since  1.1
	 */
	public function display_options_page() {
		include_once 'partials/rcmnd-admin-display.php';
	}
	
	
	/**
	 * Register all related settings of this plugin
	 *
	 * @since  1.1
	 */

	public function register_general_settings() {
		
		
		register_setting(
			'rcmnd_gso_group', // option_group
			'rcmnd_gso', // option_name
			array( $this, 'rcmnd_gso_sanitize' ), // sanitize_callback
		);
		

		add_settings_section(
			'rcmnd_gso_section', //id
			__( 'General', 'rcmnd' ), // title
			array( $this, 'rcmnd_gso_section_cb' ), //callback
			'rcmnd_gso'
		);
		
		add_settings_field(
			'rcmnd_pkey', // id
			__( 'API Key', 'rcmnd' ), // title
			array( $this, 'rcmnd_pkey_cb' ), //callback
			'rcmnd_gso', // page
			'rcmnd_gso_section', // section
			array( 'label_for' => 'rcmnd_pkey' )
		);

		add_settings_field(
			'rcmnd_istest', //id
			__( 'Testing?', 'rcmnd' ), // title
			array( $this, 'rcmnd_istest_cb' ), //callback
			'rcmnd_gso', //page
			'rcmnd_gso_section', // section
			array( 'label_for' => 'rcmnd_istest' )
		);
	}
	
	public function register_advanced_settings(){
		register_setting(
			'rcmnd_aso_group', // option_group
			'rcmnd_aso', // option_name
			array( $this, 'rcmnd_aso_sanitize' ), // sanitize_callback
		);
		
		add_settings_section(
			'rcmnd_aso_section', //id
			__( 'Advanced', 'rcmnd' ), // title
			array( $this, 'rcmnd_aso_section_cb' ), //callback
			'rcmnd_aso'
		);
		
		add_settings_field(
			'rcmnd_opt1', // id
			__( 'Thank you page text', 'rcmnd' ), // title
			array( $this, 'rcmnd_opt1_cb' ), //callback
			'rcmnd_aso', // page
			'rcmnd_aso_section', // section
			array( 'label_for' => 'rcmnd_opt1' )
		);
		
		add_settings_field(
			'rcmnd_opt2', // id
			__( 'Add to cart button text', 'rcmnd' ), // title
			array( $this, 'rcmnd_opt2_cb' ), //callback
			'rcmnd_aso', // page
			'rcmnd_aso_section', // section
			array( 'label_for' => 'rcmnd_opt2' )
		);
	}
	
	/**
	 * Sanitize 
	 *
	 * @param  string $position $_POST value
	 * @since  1.1
	 * @return string           Sanitized value
	 */
	private function rcmnd_gso_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['rcmnd_pkey'] ) ) {
			$sanitary_values['rcmnd_pkey'] = filter_var($input['rcmnd_pkey'], FILTER_SANITIZE_STRING);
		}
		return $sanitary_values;
	}
	
	private function rcmnd_aso_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['rcmnd_opt1'] ) ) {
			$sanitary_values['rcmnd_opt1'] = filter_var($input['rcmnd_opt1'], FILTER_SANITIZE_STRING);
		}
		
		if ( isset( $input['rcmnd_opt2'] ) ) {
			$sanitary_values['rcmnd_opt2'] = filter_var($input['rcmnd_opt2'], FILTER_SANITIZE_STRING);
		}
		return $sanitary_values;
	}
	
	
	/**
	 * Render the text for the sections
	 *
	 * @since  1.1
	 */
	public function rcmnd_gso_section_cb() {
		echo '<p>' . esc_html(__( 'Please change the settings accordingly.', 'rcmnd' )) . '</p>';
	}
	
	public function rcmnd_aso_section_cb() {
		echo '<p>' . esc_html(__( 'Customize your display text options here.', 'rcmnd' )) . '</p>';
	}
	
	
	/**
	 * Render the input fields
	 *
	 * @since  1.1
	 */
	 
	public function rcmnd_pkey_cb() {
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? $gso_options['rcmnd_pkey'] : '';
		?>
			<fieldset>
				<label>
					<input class="rcmnd-inputfields" placeholder="Enter your Key..." type="text" name="rcmnd_gso[rcmnd_pkey]" value="<?php echo esc_attr($pkey); ?>"/>
					<p class="description"><?php esc_html(_e( 'Find your API Key within the Integration module in Recommend.', 'rcmnd' )); ?></p>
				</label>
			</fieldset>
		<?php
	}
	
	public function rcmnd_istest_cb() {
		$gso_options = get_option( 'rcmnd_gso' );
		$istest = ( isset($gso_options['rcmnd_istest'] ) ) ? $gso_options['rcmnd_istest'] : 'off';
		$checked = ($istest == 'on' ) ? 'checked' : '';
		?>
			<fieldset>
				<label>
					<input type="checkbox" name="rcmnd_gso[rcmnd_istest]" <?php echo esc_attr($checked); ?>>
					<p class="description"><?php esc_html(_e( 'If you are testing plugin set this checkbox. In testing mode, plugin will send referral check after adding product to cart.', 'rcmnd' )); ?></p>
				</label>
			</fieldset>
		<?php
	}
	
	public function rcmnd_opt1_cb() {
		$aso_options = get_option( 'rcmnd_aso' );
		$opt1 = ( isset($aso_options['rcmnd_opt1'] ) ) ? $aso_options['rcmnd_opt1'] : '';
		?>
			<fieldset>
				<label>
					<input class="rcmnd-inputfields" placeholder="Enter your Opt1..." type="text" name="rcmnd_aso[rcmnd_opt1]" value="<?php echo esc_attr($opt1); ?>"/>
					<p class="description"><?php esc_html(_e( 'This text will be added on thank you page if conversion is fired.', 'rcmnd' )); ?></p>
				</label>
			</fieldset>
		<?php
	}
	
	public function rcmnd_opt2_cb() {
		$aso_options = get_option( 'rcmnd_aso' );
		$opt2 = ( isset($aso_options['rcmnd_opt2'] ) ) ? $aso_options['rcmnd_opt2'] : '';
		?>
			<fieldset>
				<label>
					<input class="rcmnd-inputfields" placeholder="Enter your Opt2..." type="text" name="rcmnd_aso[rcmnd_opt2]" value="<?php echo esc_attr($opt2); ?>"/>
					<p class="description"><?php esc_html(_e( 'This text will be add after add to cart button if referral code is recognized.', 'rcmnd' )); ?></p>
				</label>
			</fieldset>
		<?php
	}

	/**
	 * Admin page custom notice messages
	 *
	 * @since    1.1
	 */
	 private function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Connection to Recommend Service OK!</p>
        </div><?php
    }
    
    private function admin_error_notice() { ?>
        <div class="notice notice-error is-dismissible">
            <p>Could not connect to Recommend service!</p>
            <span>Check your API Key and try again...</span>
        </div><?php
    }
	
	/**
	 * Recommend API Check connection method
	 *
	 * @since    1.1
	 */
	private function rcmnd_check_connection(){
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? filter_var($gso_options['rcmnd_pkey'], FILTER_SANITIZE_STRING) : '';	
		$code = 'test-connection';

		$body = array(
			'apiToken' => $pkey,
			'code' => $code
		);

		// Execute the POST request
		$responseCode = $this->rcmnd_api_call($body);
		
		if ( $responseCode != 200 ) {
			$this->admin_error_notice();
		}
		else{
			$this->admin_notice();
		}
	}
	
	/**
	 * Recommend API POST REQUEST
	 *
	 * @since    1.1
	 */
	private function rcmnd_api_call($body){
		
		$httpCode = 500;
		$url = "https://api.recommend.co/apikeys";
		
		$args = array(
			'method'      => 'POST',
			'body'        => wp_json_encode( $body ),
			'timeout'     => '45',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array
			(
				'Content-Type' => 'application/json'
			),
			'cookies'     => array()
		);
		
		// Execute the POST request
		$response = wp_remote_post( $url, $args );
		
		if ( !is_wp_error( $response )) 
		{
			$httpCode = wp_remote_retrieve_response_code( $response );
		}
			
		return $httpCode;
	}
	
}
