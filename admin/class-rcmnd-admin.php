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
			array( $this, 'rcmnd_gso_sanitize' ) // sanitize_callback
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
			'rcmnd_autosync', //id
			__( 'Auto sync products?', 'rcmnd' ), // title
			array( $this, 'rcmnd_autosync_cb' ), //callback
			'rcmnd_gso', //page
			'rcmnd_gso_section', // section
			array( 'label_for' => 'rcmnd_autosync' )
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
			array( $this, 'rcmnd_aso_sanitize' ) // sanitize_callback
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
			$sanitary_values['rcmnd_pkey'] = sanitize_text_field($input['rcmnd_pkey']);
		}
		return $sanitary_values;
	}
	
	private function rcmnd_aso_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['rcmnd_opt1'] ) ) {
			$sanitary_values['rcmnd_opt1'] = sanitize_text_field($input['rcmnd_opt1']);
		}
		
		if ( isset( $input['rcmnd_opt2'] ) ) {
			$sanitary_values['rcmnd_opt2'] = sanitize_text_field($input['rcmnd_opt2']);
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

	/**
	 * Render the autsync checkbox
	 *
	 * @since  1.3.6
	 */
	public function rcmnd_autosync_cb() {
		$gso_options = get_option( 'rcmnd_gso' );
		$autosync = ( isset($gso_options['rcmnd_autosync'] ) ) ? $gso_options['rcmnd_autosync'] : 'off';
		$checked = ($autosync == 'on' ) ? 'checked' : '';
		?>
			<fieldset>
				<label>
					<input type="checkbox" name="rcmnd_gso[rcmnd_autosync]" <?php echo esc_attr($checked); ?>>
					<p class="description"><?php esc_html(_e( 'If you check this option, products will be automatically syncronized with Recommend on every change.', 'rcmnd' )); ?></p>
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
	 private function admin_notice() {
?>
        <div class="notice notice-success is-dismissible">
            <p>Connection to Recommend Service OK!</p>
        </div><?php
    }

	private function admin_sync_notice() {
		?>
				<div class="notice notice-success is-dismissible">
					<p>Syncronization done!</p>
				</div><?php
			}
    
    private function admin_error_notice() { ?>
        <div class="notice notice-error is-dismissible">
            <p>Could not connect to Recommend service!</p>
            <span>Check your API Key and try again...</span>
        </div><?php
    }

	private function admin_sync_error_notice() { ?>
        <div class="notice notice-error is-dismissible">
            <p>Cannot add product.</p>
        </div><?php
    }
	
	
	/**
	 * Recommend API Check connection method
	 *
	 * @since    1.1
	 */
	private function rcmnd_check_connection(){
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';	
		$code = 'test-connection';

		$body = array(
			'apiToken' => $pkey,
			'code' => $code
		);

		// Execute the POST request
	
		$response = $this->rcmnd_api_call($body,'/apikeys');

		$responseCode = $response->{'httpCode'};
		$responseMessage = $response->{'httpMessage'};
		
		if ( $responseCode != 200 ) {
			$this->admin_error_notice();
		}
		else{
			$this->admin_notice();
		}
	}
	
	/**
	 * Recommend sync product after update
	 *
	 * @since    1.3.6
	 */
	public function rcmnd_product_update($product_id, $product){
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';	
		//$is_sync_on = ( isset($gso_options['rcmnd_autosync'] ) ) ? $gso_options['rcmnd_autosync'] : 'off';		
		//$is_sync_on_mode = ($is_sync_on == 'on') ? true : false; 
		
		$is_sync_on = get_post_meta($product_id, '_rcmnd_product_sync', true);		
		$is_sync_on_mode = ($is_sync_on == 'yes') ? true : false; 
		
		//error_log("Product updated action triggered. Updating Recommend DB.");

		if($is_sync_on_mode)
		{
			$p_id = $product-> get_id();
			$p_status = $product->get_status();
			$p_name = $product->get_name();
			$p_description = $product->get_short_description();
			$p_sku = $product->get_sku();
			$p_permalink = get_permalink( $product->get_id() );
			$p_price = $product->get_price();
			$p_stock_status = $product->get_stock_status();
			$p_category = get_post_meta($product_id,'_rcmnd_product_sync_category',true) === '' ? '0' : get_post_meta($product_id,'_rcmnd_product_sync_category',true);

			//error_log('Title => ' . $p_name);
			//error_log('$categoryId => ' . $p_category);
			
			if($p_status === 'publish') 
			{
				$pr_status = 0;
				if($p_stock_status === 'outofstock') // outofstock or instock
				{
					$pr_status = -2;
				}

				//error_log('status => ' . $pr_status);
				//error_log("-----SENDING TO RECOMMEND------");

				$body = array(
					'apiToken' => $pkey,
					'internalId' => $p_sku,
					'title' => $p_name,
					'categoryPath' => "woo integration",
					'categoryId' => $p_category,
					'price' => $p_price,
					'url' => $p_permalink,
					'image' => get_the_post_thumbnail_url($p_id),
					'description' => ($p_description === '') ? " " : $p_description,
					'status' => $pr_status
				);

				// Execute the POST request
				$response = $this->rcmnd_api_call($body,'/products/sync');

				$responseCode = $response->{'httpCode'};
				$responseMessage = $response->{'httpMessage'};

				//error_log($responseCode);
				//error_log($responseMessage);

				if ( $responseCode != 200 ) {
					//error_log("ERROR ON UPDATE PRODUCT IN RECEOMMEND DB!");
					//$this->admin_sync_error_notice();
				}
			}
		}
		else
		{
			//error_log("Sync mode is off - skipping.");
		}
	}
	
	
	/**
	 * Recommend sync products manually throgh settings page
	 *
	 * @since    1.3.6
	 */
	private function rcmnd_sync_products(){
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';	
		$is_sync_on = ( isset($gso_options['rcmnd_autosync'] ) ) ? $gso_options['rcmnd_autosync'] : 'off';		
		$is_sync_on_mode = ($is_sync_on == 'on') ? true : false; 
		
		//error_log("Trying to sync products on settings update...");

		if($is_sync_on_mode)
		{
			// SYNC MODE ON - NEED TO PROCESS PRODUCTS
			//error_log("Starting with products loop");

			$args = array(
				'post_type'      => 'product'
			);

			$loop = new WP_Query( $args );

			while ( $loop->have_posts() ) : $loop->the_post();
				error_log("-----NEW PRODUCT------");
				global $product;
			 	$p_id = $product-> get_id();
			
				$is_product_sync_on = get_post_meta($p_id, '_rcmnd_product_sync', true);		
				$is_product_sync_on_mode = ($is_product_sync_on == 'yes') ? true : false;
			
				$p_status = $product->get_status();
				$p_name = $product->get_name();
				$p_description = $product->get_short_description();
				$p_sku = $product->get_sku();
				$p_permalink = get_permalink( $product->get_id() );
				$p_price = $product->get_price();
				$p_stock_status = $product->get_stock_status();
				$p_category = get_post_meta($p_id,'_rcmnd_product_sync_category',true) === '' ? '0' : get_post_meta($p_id,'_rcmnd_product_sync_category',true);

				//error_log('Title => ' . $p_name);		
			    	//error_log('$categoryId => ' . $p_category);
				/*error_log('Status => ' . $p_status);
				error_log('apiToken => ' . $pkey);
				error_log('internalId => ' . $p_sku);
				error_log('price => ' . $p_price);
				error_log('description => ' . ($p_description === '') ? "pero" : $p_description);
				error_log('url => ' . $p_permalink);	
				error_log('image => ' . get_the_post_thumbnail_url($p_id));*/

				if($p_status === 'publish' && $is_product_sync_on_mode) 
				{
					$pr_status = 0;
					if($p_stock_status === 'outofstock') // outofstock or instock
					{
						$pr_status = -2;
					}
					//error_log('status => ' . $pr_status);

					//error_log("-----SENDING TO RECOMMEND------");

					$body = array(
						'apiToken' => $pkey,
						'internalId' => $p_sku,
						'title' => $p_name,
						'categoryPath' => "woo integration",
						'categoryId' => $p_category,
						'price' => $p_price,
						'url' => $p_permalink,
						'image' => get_the_post_thumbnail_url($p_id),
						'description' => ($p_description === '') ? " " : $p_description,
						'status' => $pr_status
					);

					// Execute the POST request
					$response = $this->rcmnd_api_call($body,'/products/sync');

					$responseCode = $response->{'httpCode'};
					$responseMessage = $response->{'httpMessage'};

					//error_log($responseCode);
					//error_log($responseMessage);


					if ( $responseCode != 200 ) {
						//error_log("ERROR ON UPDATE PRODUCT IN RECEOMMEND DB!");
					}
				}

			endwhile;
			wp_reset_query();

			//error_log("Ending with products loop");

			$this->admin_sync_notice();
		}
		else
		{
			//error_log("Sync mode is off - skipping.");
		}
	}
	
	/**
	 * Recommend Sync Options in Product Page
	 *
	 * @since    1.3.8
	 */
	public function rcmnd_product_custom_fields_add()
	{
		global $post;

		echo '<div class="rcmnd_product_sync_field">';

		// Checkbox for turning on or off sync
		woocommerce_wp_checkbox( array(
			'id'        => '_rcmnd_product_sync',
			'description'      => __('Turn on Syncronization with Recommend.', 'rcmnd'),
			'label'     => __('Sync with Recommend', 'rcmnd'),
			'desc_tip'  => 'true'
		));

		echo '</div>';

		
		// Select list for categories
		$response = $this->rcmnd_api_call('','/product-categories','GET');
		$categories = $response->{'httpBody'};

		$options[''] = __( 'Select a Recommend Category...', 'rcmnd'); // default value
		$this->get_all_categories($categories, $options,'');

		$value = get_post_meta($post->ID,'_rcmnd_product_sync_category',true);

		echo '<div class="rcmnd_product_sync_field">';
		woocommerce_wp_select( array(
			'id'        => '_rcmnd_product_sync_category',
			'description'  => __('Recommend Category selection', 'rcmnd'),
			'label'     => __('Recommend Category', 'rcmnd'),
			'desc_tip'  => 'true',
			'options' =>  $options,
			'value'   => $value,
		));
		echo '</div>';
	}

	public function product_custom_fields_rcmnd_sync_option($post_id)
	{
		// Sync Option turn on or off option save
		$rcmnd_product_sync = isset( $_POST['_rcmnd_product_sync'] ) ? 'yes' : 'no';
			update_post_meta($post_id, '_rcmnd_product_sync', esc_attr( $rcmnd_product_sync ));
		
		// Sync Option category option save
		$rcmnd_product_sync_category = isset( $_POST['_rcmnd_product_sync_category'] ) ? $_POST['_rcmnd_product_sync_category'] : '';
        	update_post_meta($post_id, '_rcmnd_product_sync_category', esc_attr( $rcmnd_product_sync_category ));
	}
	
	public function rcmnd_set_custom_columns($columns) 
	{
		$columns['_rcmnd_product_sync'] = __('Recommend Sync', 'rcmnd'); // Sync product on/off

		return $columns;
	}
	
	// Show custom field in a new column in list view
	public function rcmnd_custom_column( $column, $post_id ) {

		if($column == '_rcmnd_product_sync')
		{ 
			$get_rcmnd_product_sync = get_post_meta($post_id,'_rcmnd_product_sync',true);

			if($get_rcmnd_product_sync == 'yes')
			{ 
				echo '<div class="rcmnd_product_sync_column_view_field">';
					echo '<img src="' . plugin_dir_url(dirname( __FILE__ )) . 'images/rcmnd-icon-sync-on.svg"' . 'title="Sync with Recommend Turned on" alt="Sync with Recommend" width="20" height="20">';
				echo '</div>';
			} 
			else
			{ 
				echo '<div class="rcmnd_product_sync_column_view_field">';
					echo '<img src="' . plugin_dir_url(dirname( __FILE__ )) . 'images/rcmnd-icon-sync-off.svg"' . 'title="Sync with Recommend Turned off" alt="Sync with Recommend" width="20" height="20">';
				echo '</div>';
			} 
		}  
	}

		
	
	/**
	 * Recommend API POST REQUEST
	 *
	 * @since    1.1
	 */
	private function rcmnd_api_call($body, $route, $method='POST'){
		
        $response_object = (object) ['httpCode' => 500, 'httpMessage' => ''];
	
		$url = 'https://api.recommend.co' . $route;
		//$url = 'https://rpd-api-dev.azurewebsites.net' . $route;

		$args = array(
			'method'      => $method,
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
            $httpMessage = json_decode( wp_remote_retrieve_body( $response ) );
			
            $response_object->httpCode = $httpCode;
			
			if(isset($httpMessage->message)){
				$response_object->httpMessage = $httpMessage->message;
			}
			
			$response_object->httpBody = $httpMessage;
		}
			
		return $response_object;
	}
	
	
	private function get_all_categories($categories, &$options, $prefix = '')
	{
		foreach ($categories as $category) {

			if(!isset($category->parentId)){
				$options[$category->id] = __( $prefix . $category->mpath . ' ' . strtoupper($category->name), 'rcmnd');
			}
			else{
				 $options[$category->id] = __( '	' . $category->mpath . ' ' . $category->name, 'rcmnd');
			}

			if (!empty($category->children)) {
				$this->get_all_categories($category->children, $options, '');
			}
		}
	}
	
}
