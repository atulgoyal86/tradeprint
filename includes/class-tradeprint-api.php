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

	 public function get_product_prices($product_id, $selected_attributes){
		$curl = curl_init();

		$api_body = array(
			"productId" => $product_id,
			"productionData" => $selected_attributes
		);

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
		echo $response;

	 }
}