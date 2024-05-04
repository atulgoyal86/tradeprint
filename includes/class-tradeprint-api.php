<?php

/**
 * The tradeprint api function of the plugin.
 *
 * @link       https://https://cloud1.me/
 * @since      1.0.0
 *
 * @package    Tradeprint
 * @subpackage Tradeprint/includes
 */

/**
 * The tradeprint api functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the tradeprint api stylesheet and JavaScript.
 *
 * @package    Tradeprint
 * @subpackage Tradeprint/includes
 * @author     Gaurav Garg <gauravgargcs1991@gmail.com>
 */
class Tradeprint_Api {
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
	 * The api base url of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $api_base_url;

    /**
	 * The api sandbox of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $sandbox;

    /**
	 * The api username of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $api_username;

    /**
	 * The api password of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $api_password;

	private $api_token;

	private $api_token_at;

	private $is_auth = true;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->sandbox = get_option('cotp_api_sandbox')??0;
        $this->api_username = get_option('cotp_api_username')??'';
        $this->api_password = get_option('cotp_api_password')??'';
		$this->api_token = get_option('cotp_api_token')??'';
		$this->api_token_at = get_option('cotp_api_token_at')??'';

        if($this->sandbox){
            $this->api_base_url = 'https://sandbox.orders.tradeprint.io/v2/';
        }
        else{
            $this->api_base_url = 'https://orders.tradeprint.io/v2/';
        }
        //$this->api_token = '';

		if($this->api_username == '' || $this->api_password == ''){
			$this->is_auth = false;
		}
		else if($this->api_token == ''){
			$this->authenticate();
		}
		else{
			$to_time = strtotime('now');
			$token_minutes = round(abs($this->api_token_at - $to_time) / 60,2);

			if($token_minutes >= 480){
				$this->authenticate();
			}
		}
	}

	/**
	 * tradeprint api login to generate token.
	 *
	 * @since    1.0.0
	 */
    public function authenticate(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->api_base_url.'login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "username": "'.$this->api_username.'",
            "password": "'.$this->api_password.'"
        } ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

		if($response['success']){
			update_option('cotp_api_token', $response['result']['token']);
			$this->api_token = $response['result']['token'];

			$token_generated_at = strtotime("now");

			update_option('cotp_api_token_at', $token_generated_at);
			$this->api_token_at = $token_generated_at;

			$this->is_auth = true;
		}
		else{
			$this->is_auth = false;
		}
    }

	/**
	 * tradeprint api get product attribute.
	 *
	 * @since    1.0.0
	 */
	public function get_product_attributes( $product_name ){
		if(!$this->is_auth){
			return false;
		}

		$product_name = rawurlencode($product_name);

		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->api_base_url.'products-v2/attributes-v2/'.$product_name,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->api_token
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);

		if(isset($response['success'])){
			if($response['success']){
				return $response['result']['values'];
			}
			else{
				return false;
			}
			
		}
		else{
			return false;
		}

	}

	/**
	 * tradeprint api get product prices by productid and production data.
	 *
	 * @since    1.0.0
	 */

	public function get_product_prices($product_id, $selected_attributes, $quantity = '', $service_level = ''){
		$curl = curl_init();

		$api_body = array(
			"productId" => $product_id,
			"productionData" => $selected_attributes
		);

		if($quantity != ''){
			$api_body['quantity'] = array((int)$quantity);
		}

		if($service_level != ''){
			$api_body['serviceLevel'] = $service_level;
		}
		

		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->api_base_url.'products-v2/prices-v2',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($api_body),
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '.$this->api_token,
			'Content-Type: application/json'
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);
		if(isset($response['success'])){
			if($response['success']){
				return $response['result'];
			}
			else{
				return false;
			}
			
		}
		else{
			return false;
		}

	}


	/**
	 * tradeprint api get product expected delivery date
	 *
	 * @since    1.0.0
	 */

	public function get_expected_delivery_date($product_id, $selected_attributes, $quantity = '', $service_level = '', $artworkService = '', $postcode = ''){
		$curl = curl_init();

		$api_body = array(
			"productId" => $product_id,
			"productionData" => $selected_attributes
		);

		if($quantity != ''){
			$api_body['quantity'] = array((int)$quantity);
		}

		if($service_level != ''){
			$api_body['serviceLevel'] = $service_level;
		}

		if($artworkService != ''){
			$api_body['artworkService'] = $artworkService;
		}

		if($postcode != ''){
			$api_body['deliveryAddress']['postcode'] = $postcode;
		}
		

		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->api_base_url.'products-v2/prices-v2',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($api_body),
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '.$this->api_token,
			'Content-Type: application/json'
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);
		if(isset($response['success'])){
			if($response['success']){
				return $response['result'];
			}
			else{
				return false;
			}
			
		}
		else{
			return false;
		}

	}

	public function get_order_status( $order_refrence ){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->api_base_url.'orders/'.$order_refrence,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '.$this->api_token,
			'Content-Type: application/json'
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);
		//print_r($response); die;
		if(isset($response['success'])){
			if($response['success']){
				return $response['result'];
			}
			else{
				return false;
			}
			
		}
		else{
			return false;
		}
	}

	public function create_order( $wc_order_id ){
		$order = wc_get_order( $wc_order_id );
		
		$tradeprint_order = array();
		$tradeprint_order['currency'] = 'GBP'; //get_woocommerce_currency();
		$tradeprint_order['orderReference'] = (string) 'cotp_wc_'.$order->get_id();
		$tradeprint_order['billingAddress'] = array(
			"firstName"=> $order->get_billing_first_name(),
			"lastName"=> $order->get_billing_last_name(),
			"add1"=> $order->get_billing_address_1(),
			"add2"=> $order->get_billing_address_2(),
			"postcode"=> $order->get_billing_postcode(),
			"town"=> $order->get_billing_city(),
			"country"=> $order->get_billing_country(),
			//"companyName"=> $order->get_billing_company(),
			"email"=> $order->get_billing_email(),
			//"contactPhone"=> $order->get_billing_phone(),
			//"mobile"=> $order->get_billing_phone()
		);

		$tradeprint_items = array();

		foreach ( $order->get_items() as $item_id => $item ) {
			
			if(is_tradeprint_product($item->get_product_id())){
				$production_data = $item->get_meta('tradeprint_production_data', true);

				$file_url = $item->get_meta('tradeprint_uploaded_url', true);

				$tradeprint_items_data = array(
					"productId" => $item->get_meta('Tradeprint Product Id', true),
					"fileUrls" => array(),
					"withoutArtwork" => true,
					"quantity" => (int)$item->get_meta('Tradeprint Quantity', true),
					"serviceLevel" => $item->get_meta('Service Level', true),
					"productionData" => $production_data,
					"partnerContactDetails" => array(
						"firstName"=> $order->get_billing_first_name(),
						"lastName"=> $order->get_billing_last_name(),
						"email"=> $order->get_billing_email(),
						//"contactPhone"=> $order->get_billing_phone(),
						//"companyName"=> $order->get_billing_company()
					),
					"deliveryAddress" => array(
						//"companyName"=> $order->get_shipping_company(),
						"firstName"=> $order->get_shipping_first_name(),
						"lastName"=> $order->get_shipping_last_name(),
						"add1"=> $order->get_shipping_address_1(),
						"add2"=> $order->get_shipping_address_2(),
						"town"=> $order->get_shipping_city(),
						"postcode"=> $order->get_shipping_postcode(),
						"country"=> $order->get_shipping_country() //"GB"
					),
					"extraData" => array(
						// "description"=> "Order description",
						"comments"=> $order->get_customer_note()
						// "partnerItemId"=> "doe_12345",
						// "merchandisingProductName"=> "Comp Slip",
						// "referenceLabel"=> "Sample Reference",
						// "purchaseOrder"=> "12345679"
					)
				);

				if( isset($file_url) && !empty($file_url) && isset($file_url['url']) && $file_url['url'] != ''){
					$tradeprint_items_data['fileUrls'] = array($file_url['url']);
					$tradeprint_items_data['withoutArtwork'] = false;
				}

				// $tradeprint_items_data['fileUrls'] = array('https://wp.cloud1.me/agdev/wp-content/uploads/2024/05/MI-Calculator-Brief.pdf');
				// 	$tradeprint_items_data['withoutArtwork'] = false;

				if($order->get_billing_company() != ''){
					$tradeprint_items_data['partnerContactDetails']['companyName'] = $order->get_billing_company();
					$tradeprint_order['billingAddress']['companyName'] = $order->get_billing_company();
				}
				if($order->get_billing_phone() != ''){
					$tradeprint_items_data['partnerContactDetails']['contactPhone'] = $order->get_billing_phone();

					$tradeprint_order['billingAddress']['contactPhone'] = $order->get_billing_phone();
					$tradeprint_order['billingAddress']['mobile'] = $order->get_billing_phone();
				}
				if($order->get_shipping_company() != ''){
					$tradeprint_items_data['deliveryAddress']['companyName'] = $order->get_shipping_company();
				}

				$tradeprint_items[] = $tradeprint_items_data;
			}
		}


		$tradeprint_order['orderItems'] = $tradeprint_items;

		if( !empty( $tradeprint_items ) ){
			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->api_base_url.'orders',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($tradeprint_order),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.$this->api_token,
				'Content-Type: application/json'
			),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			update_post_meta($wc_order_id, 'cotp_tradeprint_order', $response);
			
			$response = json_decode($response, true);
			//echo '<pre>'; print_r($response); die;
			if(isset($response['success'])){
				if($response['success']){
					update_post_meta($wc_order_id, 'cotp_tradeprint_order_created', 'yes');
					
					return $response['result'];
				}
				else{
					return false;
				}
				
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}
}