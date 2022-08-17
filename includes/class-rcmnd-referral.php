<?php
// Starting session
session_start();


/**
 * Fired during plugin activation
 *
 * @link       https://recommnd.io
 * @since      1.0.0
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/includes
 * @author     Recommend Inc. <info@rcmnd.co>
 */
class Rcmnd_referral_Worker {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function my_alert_function() {
        ?> <script>alert("VAE VICTIS!");</script> <?php
    }
    
    
    
    
    /**
     * Trigger actions for recommend integration.
     *
     * @param int $order_id
     */
     
    public function rcmnd_check_referral( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data )
    {
       
        $logfile = 'rcmnd-log.log';
        

        //$order = wc_get_order( $order_id );
        
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " CHECKING REFERRAL...\n", 3, $logfile);
          
        $cookieValue = $_SESSION["rcmnd_cookie"];//get_transient( 'rcmnd_cookie' );
        $pkey = get_option( 'rcmnd_pkey', '' );
        $testApi = get_option( 'rcmnd_tapi', '' );
        
        
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " ADDED TO CART - REFERRAL CODE: " . $cookieValue ."\n", 3, $logfile);

       //error_log(date("Y-m-d h:i:sa", $d) . " KEY: " . $pkey . ", SECRET: " . $psecret . "\n", 3, $logfile);
        
        // API URL
        //$url = $testApi;//'https://betapi.densel.hr/Token';
        
        if($testApi === '' || $testApi == 'undefined')
        {
            $url = "https://beta.recommnd.io/beta-api/apikeys";
        }
        else{
            $url = $testApi;
        }
		
		$url = "https://api.recommnd.io/apikeys";
        
        // Create a new cURL resource
        $ch = curl_init($url);
        
        // Setup request to send json via POST
        $data = array(
            'code' => $cookieValue,
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
        
        
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " RESPONSE FROM RECOMMEND INC STATUS CODE: ". $httpcode . "\n", 3, $logfile);
        
        
        if ($httpcode === 200) {
            
           foreach( WC()->cart->get_cart() as $cart_item ) {
              $product_in_cart = $cart_item['product_id'];
              if ( $product_in_cart === $product_id ){

                  $data = (array)WC()->session->get( '_ld_woo_product_data' );
                	if ( empty( $data[$cart_item_key] ) ) {
                		$data[$cart_item_key] = array();
                	}
                
                	$item_cart_name = $cart_item['data']->get_name();
                	
                	$data[$cart_item_key]["citem-name"] = '<div class="rcmndref-tag-parent"><img title="This product was recommended to you." class="rcmndref-tag" src="/wp-content/plugins/rcmnd-referal/images/rcmnd-logo.png"></img><span class="rcmndref-item-name">' . $item_cart_name . '</span></div>';
                
                	WC()->session->set( '_ld_woo_product_data', $data );

              }
           }
        }
	
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " RESPONSE FROM RECOMMEND INC: ". $result . "\n", 3, $logfile);
        
        unset($_SESSION["rcmnd_cookie"]);
        
    }
    
    public function rcmnd_addedtocart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data){
        $logfile = 'rcmnd-log.log';
        
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " PROVJERAVAM:" . $product_id . " \n", 3, $logfile);
        
        foreach( WC()->cart->get_cart() as $cart_item ) {
              $product_in_cart = $cart_item['product_id'];
              if ( $product_in_cart === $product_id ){

                  $data = (array)WC()->session->get( '_ld_woo_product_data' );
                	if ( empty( $data[$cart_item_key] ) ) {
                		$data[$cart_item_key] = array();
                	}
                
                	$item_cart_name = $cart_item['data']->get_name();
                	
                	$data[$cart_item_key]["citem-name"] = '<div class="rcmndref-tag-parent"><div style="float:left;width:4%;"><a target="_blank" href="https://recommnd.io"><img title="This product was recommended to you." class="rcmndref-tag" src="/wp-content/plugins/rcmnd-referal/images/rcmnd-logo.png"></img></a></div><div style="float:left;width:96%;"><p style="float:left;" class="rcmndref-item-name">' . $item_cart_name . '</p></div></div>';
                
                	WC()->session->set( '_ld_woo_product_data', $data );
              }
        }
    }
    
    
    public function rcmnd_check_referral_order( $order_id )
    {
       
        $logfile = 'rcmnd-log.log';
        

        //$order = wc_get_order( $order_id );
        
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " CHECKING REFERRAL PRODUCTION...\n", 3, $logfile);
        
        if ( ! $order_id ){
            error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " PROBLEM WITH CHECK REFERRAL PRODUCTION...\n", 3, $logfile);
            return;
        }
            
         // Getting an instance of the order object
        $order = wc_get_order( $order_id );
        
        if($order->is_paid())
            $paid = true;
        else
            $paid = false;
            
        if($paid)
        {
            $order_key = $order->get_order_number();

            $data  = $order->get_data(); // The Order data
            
            $billing_email = $data['billing']['email'];
            $billing_phone = $order_data['billing']['phone'];
            
    
            //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " CHECKING REFERRAL PRODUCTION THANK YOU PAGE, EMAIL: " . $billing_email . "\n", 3, $logfile);
              
            $cookieValue = $_SESSION["rcmnd_cookie"];//get_transient( 'rcmnd_cookie' );
            $pkey = get_option( 'rcmnd_pkey', '' );
            $testApi = get_option( 'rcmnd_tapi', '' );
            
            
            //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " PRODUCT  - REFERRAL CODE: " . $cookieValue ."\n", 3, $logfile);
    
           //error_log(date("Y-m-d h:i:sa", $d) . " KEY: " . $pkey . ", SECRET: " . $psecret . "\n", 3, $logfile);
            
            // API URL
            //$url = $testApi;//'https://betapi.densel.hr/Token';
            
            if($testApi === '' || $testApi == 'undefined')
            {
                $url = "https://beta.recommnd.io/beta-api/apikeys";
            }
            else{
                $url = $testApi;
            }
            
            $url = "https://api.recommnd.io/apikeys";
            
            // Create a new cURL resource
            $ch = curl_init($url);
            
            // Setup request to send json via POST
            $data = array(
                'code' => $cookieValue,
                'apiToken' => $pkey,
                'email' => $billing_email
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
            
            
            //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " RESPONSE FROM RECOMMEND INC STATUS CODE: ". $httpcode . "\n", 3, $logfile);
            
            if ($httpcode === 200) {
                //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " RESPONSE FROM RECOMMEND INC httpCODE200: ". $result . "\n", 3, $logfile);
               
            }
	
        //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " RESPONSE FROM RECOMMEND INC: ". $result . "\n", 3, $logfile);
        
        unset($_SESSION["rcmnd_cookie"]);
        }
       
        
    }
    
    function rcmnd_check_referral_order_additional_text( $thank_you_title, $order  ) {
        
           $cookieValue = $_SESSION["rcmnd_cookie"];//get_transient( 'rcmnd_cookie' );
           
           if($order->is_paid() && $cookieValue != '')
            {
                $order_key = $order->get_order_number();
            
    	    return  '<p>' . $thank_you_title . '</p><div class="rcmndref-payment-success"><div style="float:left;width:4%;"><a target="_blank" href="https://recommnd.io"><img class="rcmndref-tag" src="/wp-content/plugins/rcmnd-referal/images/rcmnd-logo.png"></img></a></div><div style="float:left;width:96%;"><p style="float:left;" class="rcmndref-addtocart-notice"> Thank you for buying. Discover your benefits now at <a target="_blank" href="https://recommnd.io">Recommend</a>.</p></div></div>';
            }
	}
    
    
 
    function rcmnd_after_add_to_cart_notice() {
     
        $parameterRcmndID = '';
        
        if (isset($_GET['RcmndRef'])){
        
            $parameterRcmndID = $_GET['RcmndRef'];
        
        }
        
        if (isset($_GET['RcmndREF'])){
        
        $parameterRcmndID = $_GET['RcmndREF'];
        
        }
        
        if (isset($_GET['RCMNDREF'])){
        
        $parameterRcmndID = $_GET['RCMNDREF'];
        
        }
        
        if (isset($_GET['rcmndRef'])){
        
        $parameterRcmndID = $_GET['rcmndRef'];
        
        }
        
        if (isset($_GET['rcmndref'])){
        
        $parameterRcmndID = $_GET['rcmndref'];
        
        }
        

        if($parameterRcmndID != '' ){
            /*$cont = '<div class="rcmndref-tag-parent-cart" title="Recommend Campaing recognized. Refferal code: ' . $parameterRcmndID . '"><img class="rcmndref-tag" src="/wp-content/plugins/rcmnd-referal/images/rcmnd-logo.png"></img><span class="rcmndref-addtocart-notice">This product was recommended to you.</span></div>';*/
            
            $cont = '<div class="rcmndref-tag-parent-cart" title="This product was recommended to you."><div style="float:left;width:4%;"><a target="_blank" href="https://recommnd.io"><img class="rcmndref-tag" src="/wp-content/plugins/rcmnd-referal/images/rcmnd-logo.png"></img></a></div><div style="float:left;width:96%;"><p style="float:left;" class="rcmndref-addtocart-notice">This product was recommended to you.</span></div></div>';
            
            echo '' . $cont . '';
        }
    }
        
    
    
    function filter_woocommerce_cart_item_name( $item_name,  $cart_item,  $cart_item_key ) {
        
        $default = $item_name;
        
        $data = (array)WC()->session->get( '_ld_woo_product_data' );
    	if ( empty( $data[$cart_item_key] ) ) {
    		$data[$cart_item_key] = array();
    	}
    
    	return empty( $data[$cart_item_key]["citem-name"] ) ? $default :  $data[$cart_item_key]["citem-name"];
    
    }
    
    
    
    
    
    
        

    public function set_rcmndID_cookie() {
        
        $parameterRcmndID = '';
        
        if (isset($_GET['RcmndRef'])){
        
            $parameterRcmndID = $_GET['RcmndRef'];
        
        }
        
        if (isset($_GET['RcmndREF'])){
        
        $parameterRcmndID = $_GET['RcmndREF'];
        
        }
        
        if (isset($_GET['RCMNDREF'])){
        
        $parameterRcmndID = $_GET['RCMNDREF'];
        
        }
        
        if (isset($_GET['rcmndRef'])){
        
        $parameterRcmndID = $_GET['rcmndRef'];
        
        }
        
        if (isset($_GET['rcmndref'])){
        
        $parameterRcmndID = $_GET['rcmndref'];
        
        }

        
        //echo "<script>console.log('" . json_encode($parameterRcmndID) . "');</script>";

     
        if($parameterRcmndID != '')
        {
            //set_transient( 'rcmnd_cookie', $parameterRcmndID, 3600 );
            
            $_SESSION["rcmnd_cookie"] = $parameterRcmndID;
        
            $logfile = 'rcmnd-log.log';
            //$order = wc_get_order( $order_id );
            
            //error_log(date("Y-m-d H:i:s", strtotime(date("Y-m-d") . date("H:i:s"))) . " SESSION SET TO " . $parameterRcmndID . "\n", 3, $logfile);
            
            //do_action( 'woocommerce_after_add_to_cart_button');
        }

    }

}