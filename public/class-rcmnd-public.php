<?php

if( empty(session_id()) && !headers_sent()){
    session_start();
}

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://recommend.co
 * @since      1.1
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/public
 * @author     Recommend Inc. <admin@recommend.co>
 */
class Rcmnd_referral_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.1
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rcmnd-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rcmnd-public.js', array( 'jquery' ), $this->version, false );

	}
	
	
	/**
	 * Set add to cart button tags
	 *
	 * @since    1.1
	 */
	public function rcmnd_addedtocart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data){
        $this->rcmnd_set_cart_tags($cart_item_key, $product_id);
    }
	
	
	
	/**
	 * Check referral conversion via API, TEST MODE
	 *
	 * @since    1.1
	 */
	public function rcmnd_check_referral_test( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ){
		
		$order_total = '0';		
		$order_currency = '';
		$cookieValue = '';
		$cookieValueSSNID = '';
		
		if( isset ($_SESSION["rcmnd_cookie"])){
			$cookieValue = sanitize_text_field($_SESSION["rcmnd_cookie"]);
		}
		
		if( isset ($_SESSION["rcmnd_cookie_ssnid"])){
			$cookieValueSSNID = sanitize_text_field($_SESSION["rcmnd_cookie_ssnid"]);
		}
		
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';	
		
		$body = array(
			'apiToken' => $pkey,
			'ssnid' => $cookieValueSSNID,
			'code' => $cookieValue,
			'cartTotal' => sanitize_text_field($order_total)  . ' ' . sanitize_text_field($order_currency),
			'orderNumber' => 'Testing mode'
		);
				
		$response = $this->rcmnd_api_call($body, '/apikeys');
			
		$responseCode = $response->{'httpCode'};
		$responseMessage = $response->{'httpMessage'};
		$responseConvesionId = $response->{'conversionId'};

		if ($responseCode === 200) 
		{
		    $this->rcmnd_set_cart_tags($cart_item_key, $product_id);
			unset($_SESSION["rcmnd_cookie"]);
	    }
		
	}
	
	/**
	 * SET referral conversion via API on processing order, PRODUCTION MODE
	 *
	 * @since    1.1
	 */
	
	public function rcmnd_set_referral_prod($order_id,$posted_data, $order){
		
		if ( ! $order_id ){
		    return;
		}
		
		$cookieValue = '';
		$cookieValueSSNID = '';
				
		if( isset ($_SESSION["rcmnd_cookie"])){
			$cookieValue = sanitize_text_field($_SESSION["rcmnd_cookie"]);
		}

		if( isset ($_SESSION["rcmnd_cookie_ssnid"])){
			$cookieValueSSNID = sanitize_text_field($_SESSION["rcmnd_cookie_ssnid"]);
		}

		if(isset($cookieValue))
		{
			update_post_meta( $order->get_id(), 'rcmnd_conversion_code', $cookieValue );
			update_post_meta( $order->get_id(), 'rcmnd_conversion_ssnid', $cookieValueSSNID );

			unset($_SESSION["rcmnd_cookie"]);
			unset($_SESSION["rcmnd_cookie_ssnid"]);

		}
    }
	
	/**
	 * Check referral conversion via API on order completed, PRODUCTION MODE
	 *
	 * @since    1.1
	 */
	public function rcmnd_check_referral_prod($order_id){
        if ( ! $order_id ){
            return;
        }
		
	$rcmnd_conversion_code = '';
	$rcmnd_conversion_ssnid = '';

         // Getting an instance of the order object
        $order = wc_get_order( $order_id );
	
	$order_key = $order->get_order_number(); // The Order key
	$data  = $order->get_data(); // The Order data

	$order_total = '0';		
	$order_currency = '';
	$rcmnd_conversion_code = '';
	$rcmnd_conversion_ssnid = '';

	if( isset ($data['billing']['email'])){
		$billing_email = sanitize_text_field($data['billing']['email']);
	}

	if( isset ($data['billing']['phone'])){
		$billing_phone = sanitize_text_field($data['billing']['phone']);
	}

	if( isset ($data['total'])){
		$order_total = $data['total'];
	}

	if( isset ($data['currency'])){
		$order_currency = $data['currency'];
	}
		
	$rcmnd_conversion_code = $order->get_meta('rcmnd_conversion_code'); // The Order data
	$rcmnd_conversion_ssnid = $order->get_meta('rcmnd_conversion_code'); // The Order data


	unset($_SESSION["rcmnd_cookie_paid"]);

	if($rcmnd_conversion_code !== '')
	{
		//error_log("Getting inside request");

		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';					

		//error_log($pkey);

		$body = array(
			'apiToken' => $pkey,
			'ssnid' => $rcmnd_conversion_ssnid,
			'code' => $rcmnd_conversion_code,
			'email' => (is_email( $billing_email ) ? sanitize_email($billing_email) : ''),
			'phone' => filter_var($billing_phone, FILTER_SANITIZE_NUMBER_INT),
			'cartTotal' => sanitize_text_field($order_total)  . ' ' . sanitize_text_field($order_currency),
			'orderNumber' => sanitize_text_field($order_key)
		);

		$response = $this->rcmnd_api_call($body, '/apikeys');

		$responseCode = $response->{'httpCode'};
		$responseMessage = $response->{'httpMessage'};
		$responseConversionId = $response->{'conversionId'};

		if ($responseCode === 200) 
		{
			// Add referral code to this order
			error_log('Payment Complete - Converision Triggered - Updating PostMeta with Conversion ID');
			update_post_meta( $order->get_id(), 'rcmnd_conversion_id', $responseConversionId );
		}

		unset($_SESSION["rcmnd_cookie"]);
	}
		
    }
	
	/**
	 * Display success referral conversion on thank you page, PRODUCTION MODE
	 *
	 * @since    1.1
	 */
	public function rcmnd_check_referral_prod_message($order_id)
	{
		$order = wc_get_order( $order_id );
		
		$aso_options = get_option( 'rcmnd_aso' );
		$opt1 = ( isset($aso_options['rcmnd_opt1'] ) ) ? sanitize_text_field($aso_options['rcmnd_opt1']) : '';
		
		$rcmnd_conversion_code = $order->get_meta('rcmnd_conversion_code'); // The Order data

		if( isset ($rcmnd_conversion_code) && $rcmnd_conversion_code !== '')
		{
			$message = '
					<div class="rcmndref-payment-success">
						<div class="rcmndref-payment-success-image" style="float:left;width:5%;">
							<a target="_blank" href="https://recommend.co">
								<img class="rcmndref-tag" src="' . esc_html(plugin_dir_url( __DIR__ ) . 'images/rcmnd-logo.png') .'">

							</a>
						</div>
						<div class="rcmndref-payment-success-notice" style="float:left;width:85%;margin-left: 2%;margin-top: 1%;">
							<p style="float:left;" class="rcmndref-addtocart-notice"> ' . esc_html($opt1) .'
								|  <a target="_blank" href="https://recommend.co"> Recommend</a>
							</p>
						</div>
					</div>
					</br>';
		}
		else{
			$message = '';
		}
		
		echo wp_kses_post($message);
	}
	
	
	/**
	 * Update Woo cart item name with Recommend Logo if referral code recognized
	 *
	 * @since    1.1
	 */
	public function filter_woocommerce_cart_item_name( $item_name,  $cart_item,  $cart_item_key ) {
        
        $default = sanitize_text_field($item_name);
        
        $data = (array)WC()->session->get( '_ld_woo_product_data' );
    	if ( empty( $data[$cart_item_key] ) ) {
    		$data[$cart_item_key] = array();
    	}
    
    	return empty( $data[$cart_item_key]["citem-name"] ) ? $default :  sanitize_text_field($data[$cart_item_key]["citem-name"]);
    }
	
	/**
	 * Update Woo AddToCart button with Recommend Logo if referral code recognized
	 *
	 * @since    1.1
	 */
	public function rcmnd_after_add_to_cart_notice(){
		$cookieValue = '';
		$cookieValueUID = '';
		$cookieValueSSNID = '';
		
		if( isset ($_SESSION["rcmnd_cookie"])){
			$cookieValue = sanitize_text_field($_SESSION["rcmnd_cookie"]);
		}

		if( isset ($_SESSION["rcmnd_cookie_ssnid"])){
			$cookieValueSSNID = sanitize_text_field($_SESSION["rcmnd_cookie_ssnid"]);
		}
		
		if( isset ($_SESSION["rcmnd_cookie_uid"])){
			$cookieValueUID = sanitize_text_field($_SESSION["rcmnd_cookie_uid"]);
		}

		$aso_options = get_option( 'rcmnd_aso' );
		$opt2 = ( isset($aso_options['rcmnd_opt2'] ) ) ? sanitize_text_field($aso_options['rcmnd_opt2']) : '';

		if($cookieValue != '' && $opt2 != '')
		{   
			echo '
			<div class="rcmndref-tag-parent-cart" style="width:100%;" title="' . esc_html($cookieValue) . '$' . esc_html($cookieValueSSNID) . '">
				<div style="float:left;width:10%;">
					<a target="_blank" href="https://recommend.co">
						<img style="margin: 1.4em 0;max-width:35px;width:100%;" src="' . esc_html(plugin_dir_url( __DIR__ ) . 'images/rcmnd-logo.png') .'">
					</a>
				</div>
				<div style="float:left;width:90%;">
					<p style="float:left;margin: 1.8em 0;" class="rcmndref-addtocart-notice">' . esc_html($opt2) . '</span>
				</div>
			</div>';
        }
	}
	
	
	// Helper functions
	
	/**
	 * Update Cart tags function
	 *
	 * @since    1.1
	 */
	private function rcmnd_set_cart_tags($cart_item_key, $product_id){
		
		foreach( WC()->cart->get_cart() as $cart_item ) 
		   {
			  $product_in_cart = $cart_item['product_id'];
			  if ( $product_in_cart === $product_id )
			  {
				  $data = (array)WC()->session->get( '_ld_woo_product_data' );
					if ( empty( $data[$cart_item_key] ) ) {
						$data[$cart_item_key] = array();
					}
					$item_cart_name = $cart_item['data']->get_name();
					
					$data[$cart_item_key]["citem-name"] = '
					<div class="rcmndref-tag-parent">
						<div style="float:left;width:10%;margin-top: 2%;">
							<a target="_blank" href="https://recommend.co">
								<img title="' . __( 'This product was recommended to you.', 'recommend-referral-integration' ) . '" class="rcmndref-tag" src="' . esc_html(plugin_dir_url( __DIR__ ) . 'images/rcmnd-logo.png') .'"></img>
							</a>
						</div>
						<div style="float:right;width:85%;">
							<p style="float:left;" class="rcmndref-item-name">' . esc_html($item_cart_name) . '</p>
						</div>
					</div>';
				
					WC()->session->set( '_ld_woo_product_data', $data );
			  }
		   }
	}
	
	 private function admin_notice() {?>
        <div class="notice notice-success is-dismissible">
            <p>Connection to Recommend Service OK!</p>
        </div><?php
    }
    

	/**
	 * Conversion Update Status Functions
	 *
	 * @since    1.3.6
	 */
	public function rcmnd_order_action( $actions, $order ){
		
		$order_rcmnd_conversion = $order->get_meta('rcmnd_conversion_id'); // The Order data
				
		if($order_rcmnd_conversion != null)
		{
			// TODO: Fetch status from api using conversionId
			$order_rcmnd_code_status = 'created'; // $order->get_meta('rcmnd_code_status')

			$order_key = $order->get_order_number(); // The Order key
			$order_data_conversion = $order->get_meta('rcmnd_conversion_id');

			$rcmnd_conversionId = '';
			if( $order_data_conversion !== null){
				$rcmnd_conversionId = $order_data_conversion;
			}
			
			$gso_options = get_option( 'rcmnd_gso' );
			$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';		
			
			$body = array(
				'apiKey' => '' . $pkey . '',
				'conversionId' => $rcmnd_conversionId
			);	

			$response = $this->rcmnd_api_call($body,'/apikeys/getConversionStatus','POST');

            		$responseCode = $response->{'httpCode'};
			$responseMessage = $response->{'httpMessage'};
			
			//error_log($responseMessage);
			
			if($responseMessage !== null)
			{
				$order_rcmnd_code_status = $responseMessage;
			}
			
			if($order_rcmnd_code_status === 'created')
			{
				$actions['rcmnd_approve_action'] = __('Approve Recommend Conversion', 'WooCommerce');
				$actions['rcmnd_reject_action'] = __('Reject Recommend Conversion', 'WooCommerce');
			}
			else if($order_rcmnd_code_status === 'approved')
			{
				$actions['rcmnd_reject_action'] = __('Reject Recommend Conversion', 'WooCommerce');
			}
			else if($order_rcmnd_code_status === 'rejected')
			{
				$actions['rcmnd_approve_action'] = __('Approve Recommend Conversion', 'WooCommerce');
			}
			else 
			{
				// Status is pending, paid or unknown
			}
		}
		return $actions;
	}
	
	public function triggered_rcmnd_order_approve_action( $order ){	
		$order_key = $order->get_order_number(); // The Order key
	
		$order_data_conversion = $order->get_meta('rcmnd_conversion_id');
		
		$rcmnd_conversionId = '';
		if( $order_data_conversion !== null){
			$rcmnd_conversionId = $order_data_conversion;
		}
		
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';				
				
		if($rcmnd_conversionId !== '')
		{		
			$body = array(
				'apiKey' => $pkey,
				'conversionId' => $rcmnd_conversionId
			);	

			$response = $this->rcmnd_api_call($body,'/apikeys/approve','POST');

            		$responseCode = $response->{'httpCode'};
			$responseMessage = $response->{'httpMessage'};
					
			if ($responseCode === 200) 
			{
				//error_log('RCMND: Conversion Approved.');
			}
			else
			{
				//error_log('RCMND: Cannot approve conversion, EX: ' . $responseMessage);
			}	
		}
		else
		{
			error_log('RCMND: Conversion cannot be approved Approved since conversionId cannot be read.');
		}
	}
	
	public function triggered_rcmnd_order_reject_action( $order ){
	
		$order_key = $order->get_order_number(); // The Order key
	
		$order_data_conversion = $order->get_meta('rcmnd_conversion_id');
		
		$rcmnd_conversionId = '';
		if( $order_data_conversion !== null){
			$rcmnd_conversionId = $order_data_conversion;
		}
		
		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';				
		
	
		if($rcmnd_conversionId !== '')
		{		
			$body = array(
				'apiKey' => $pkey,
				'conversionId' => $rcmnd_conversionId
			);	

			$response = $this->rcmnd_api_call($body,'/apikeys/reject','POST');

            		$responseCode = $response->{'httpCode'};
			$responseMessage = $response->{'httpMessage'};
			
			if ($responseCode === 200) 
			{
				//error_log('RCMND: Conversion Rejected.');
			}
			else
			{
				//error_log('RCMND: Cannot reject conversion, EX: ' . $responseMessage);
			}	
		}
		else
		{
			//error_log('RCMND: Conversion cannot be reject since conversionId cannot be read.');
		}
	}
	
	/**
	 * Recommend API POST REQUEST
	 *
	 * @since    1.1
	 */
	private function rcmnd_api_call($body, $route, $method='POST'){
					
		$response_object = (object) ['httpCode' => 500, 'conversionId' => 0, 'httpMessage' => ''];
	
		$url = 'https://api.recommend.co' . $route;
		
		if($method === 'POST')
			$body = wp_json_encode($body);

		$args = array(
			'method'      => $method,
			'body'        => $body,
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
			if(isset($httpMessage->conversionId)){
				$response_object->conversionId = $httpMessage->conversionId;
			}
		}
					
		return $response_object;
	}
	
	/**
	 * Set Session variable with Recommend Referral Code
	 *
	 * @since    1.1
	 */
	public function set_rcmndID_cookie() {
        
        $parameterRcmndID = '';
	$parameterSSNID = '';
		        
        if (isset($_GET['RcmndRef'])){
            $parameterRcmndID = sanitize_text_field($_GET['RcmndRef']);
        }
        
        if (isset($_GET['RcmndREF'])){
			$parameterRcmndID = sanitize_text_field($_GET['RcmndREF']);        
        }
        
        if (isset($_GET['RCMNDREF'])){
        	$parameterRcmndID = sanitize_text_field($_GET['RCMNDREF']);        
        }
        
        if (isset($_GET['rcmndRef'])){
        	$parameterRcmndID = sanitize_text_field($_GET['rcmndRef']);        
        }
        
        if (isset($_GET['rcmndref'])){
			$parameterRcmndID = sanitize_text_field($_GET['rcmndref']);       
        }
		
        if (isset($_GET['ssnid'])){
			$parameterSSNID = sanitize_text_field($_GET['ssnid']);       
        }

        if($parameterRcmndID != '')
        {            
            $_SESSION["rcmnd_cookie"] = sanitize_text_field($parameterRcmndID);
        }
		
	if($parameterSSNID != '')
        {            
            $_SESSION["rcmnd_cookie_ssnid"] = sanitize_text_field($parameterSSNID);
        }	
    }
}
