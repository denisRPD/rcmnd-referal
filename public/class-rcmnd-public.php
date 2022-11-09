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
		
		if( isset ($_SESSION["rcmnd_cookie"])){
			$cookieValue = sanitize_text_field($_SESSION["rcmnd_cookie"]);
		}	
		else{
			$cookieValue = '';
		}

		$gso_options = get_option( 'rcmnd_gso' );
		$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';	

		$body = array(
			'apiToken' => $pkey,
			'code' => $cookieValue
		);
		
		$responseCode = $this->rcmnd_api_call($body);
		
		if ($responseCode === 200) 
		{
		    $this->rcmnd_set_cart_tags($cart_item_key, $product_id);
			unset($_SESSION["rcmnd_cookie"]);
	    }

	}
	
	/**
	 * Check referral conversion via API on processing order, PRODUCTION MODE
	 *
	 * @since    1.1
	 */
	public function rcmnd_check_referral_prod($order_id){
        if ( ! $order_id ){
            return;
        }
				
         // Getting an instance of the order object
        $order = wc_get_order( $order_id );
	
		$order_key = $order->get_order_number(); // The Order key
		$data  = $order->get_data(); // The Order data
					
		if( isset ($data['billing']['email'])){
			$billing_email = sanitize_text_field($data['billing']['email']);
		}
		
		if( isset ($data['billing']['phone'])){
			$billing_phone = sanitize_text_field($data['billing']['phone']);
		}
		
		if( isset ($_SESSION["rcmnd_cookie"])){
			$cookieValue = sanitize_text_field($_SESSION["rcmnd_cookie"]);
		}

		unset($_SESSION["rcmnd_cookie_paid"]);

		if(isset ($_SESSION["rcmnd_cookie"]) && isset($cookieValue))
		{
			$gso_options = get_option( 'rcmnd_gso' );
			$pkey = ( isset($gso_options['rcmnd_pkey'] ) ) ? sanitize_text_field($gso_options['rcmnd_pkey']) : '';					
		
			$body = array(
				'apiToken' => $pkey,
				'code' => $cookieValue,
				'email' => (is_email( $billing_email ) ? sanitize_email($billing_email) : ''),
				'phone' => filter_var($billing_phone, FILTER_SANITIZE_NUMBER_INT)
			);
		
			$responseCode = $this->rcmnd_api_call($body);
			
			if ($responseCode === 200) 
			{
				$_SESSION["rcmnd_cookie_paid"] = sanitize_text_field('true');
			}
		
			unset($_SESSION["rcmnd_cookie"]);
		}
		
    }
	
	/**
	 * Display success referral conversion on thank you page, PRODUCTION MODE
	 *
	 * @since    1.1
	 */
	public function rcmnd_check_referral_prod_message($order_id){
				
		$aso_options = get_option( 'rcmnd_aso' );
		$opt1 = ( isset($aso_options['rcmnd_opt1'] ) ) ? sanitize_text_field($aso_options['rcmnd_opt1']) : '';
		
		if( isset ($_SESSION["rcmnd_cookie_paid"])){
			$cookieValuePaid = sanitize_text_field($_SESSION["rcmnd_cookie_paid"]);
		}
		else{
			$cookieValuePaid = 'false';
		}

		if($cookieValuePaid === 'true'){
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
				
		if( isset ($_SESSION["rcmnd_cookie"])){
			$cookieValue = sanitize_text_field($_SESSION["rcmnd_cookie"]);
		}
		else{
			$cookieValue = '';
		}

		$aso_options = get_option( 'rcmnd_aso' );
		$opt2 = ( isset($aso_options['rcmnd_opt2'] ) ) ? sanitize_text_field($aso_options['rcmnd_opt2']) : '';

		if($cookieValue != '' && $opt2 != '')
		{   
			echo '
			<div class="rcmndref-tag-parent-cart" title="' . esc_html($cookieValue) . '">
				<div style="float:left;width:10%;">
					<a target="_blank" href="https://recommend.co">
						<img style="margin: 1.4em 0;max-width:35px;width:100%;" src="' . esc_html(plugin_dir_url( __DIR__ ) . 'images/rcmnd-logo.png') .'">
					</a>
				</div>
				<div style="float:left;width:80%;">
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
	
	/**
	 * Recommend API POST REQUEST
	 *
	 * @since    1.1
	 */
	private function rcmnd_api_call($body){
		$httpCode = 500;
		$url = "https://api.recommend.co/apikeys";
		
		//$url = "https://rpd-api-stage.azurewebsites.net/apikeys";

		
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
	
	/**
	 * Set Session variable with Recommend Referral Code
	 *
	 * @since    1.1
	 */
	public function set_rcmndID_cookie() {
        
        $parameterRcmndID = '';
        
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

        if($parameterRcmndID != '')
        {            
            $_SESSION["rcmnd_cookie"] = sanitize_text_field($parameterRcmndID);
        }
    }

}
