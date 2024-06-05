<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://cloud1.me/
 * @since      1.0.0
 *
 * @package    Tradeprint
 * @subpackage Tradeprint/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tradeprint
 * @subpackage Tradeprint/public
 * @author     Gaurav Garg <gauravgargcs1991@gmail.com>
 */
class Tradeprint_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('woocommerce_before_add_to_cart_button',array($this, 'cotp_tradeprint_product_attributes'));
		add_action('wp_ajax_tradeprint_product_prices_ajax', array($this, 'cotp_tradeprint_product_prices_ajax'));
		add_action('wp_ajax_nopriv_tradeprint_product_prices_ajax', array($this, 'cotp_tradeprint_product_prices_ajax'));

		add_action('wp_ajax_tradeprint_expected_delivery_ajax', array($this, 'cotp_tradeprint_expected_delivery_ajax'));
		add_action('wp_ajax_nopriv_tradeprint_expected_delivery_ajax', array($this, 'cotp_tradeprint_expected_delivery_ajax'));

		
		add_filter( 'woocommerce_add_cart_item_data', array($this, 'cotp_tradeprint_cart_item_data'), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array($this, 'cotp_tradeprint_get_cart_item_data'), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'cotp_tradeprint_order_item_data'), 10, 4 );
		add_action( 'woocommerce_before_calculate_totals', array($this, 'cotp_tradeprint_calculate_item_price'), 99 );

		add_filter( 'woocommerce_account_orders_columns', array($this, 'cotp_manage_account_orders_column'), 10, 1 );

		add_action( 'woocommerce_my_account_my_orders_column_cotp-status', array($this, 'cotp_account_orders_column_rows') );

		add_action('woocommerce_order_details_before_order_table', array( $this, 'cotp_show_myaccount_order_status'));

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tradeprint_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tradeprint_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tradeprint-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tradeprint_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tradeprint_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tradeprint-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * tradeprint product attribute render on single product page.
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_product_attributes(){
		global $product;
		
		if(is_tradeprint_product($product->get_id())){
			$tradeprint_product_name = get_post_meta($product->get_id(), 'cotp_product_name', true);

			$cotp_product_commission = get_post_meta($product->get_id(), 'cotp_product_commission', true);
			$cotp_product_commission = $cotp_product_commission??0;

			if(isset($tradeprint_product_name) && $tradeprint_product_name != ''){
				$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			
				$tradeprint_product_attributes = $tradeprint_api->get_product_attributes( $tradeprint_product_name );

				if($tradeprint_product_attributes && !empty($tradeprint_product_attributes)){

					$admin_cotp_attr = get_post_meta( $product->get_id(), 'admin_cotp_attr', true );
					$admin_cotp_attr = $admin_cotp_attr??array();
					
					$admin_cotp_attr_default = get_post_meta( $product->get_id(), 'admin_cotp_attr_default', true );
					$admin_cotp_attr_default = $admin_cotp_attr_default??array();
					
					$admin_cotp_attr_hide = get_post_meta( $product->get_id(), 'admin_cotp_attr_hide', true );
					$admin_cotp_attr_hide = $admin_cotp_attr_hide??array();
					?>
						<style>
							.single-product span.woocommerce-Price-amount.amount {
								display: none !important;
							}
							form.cart .quantity {
								display: none !important;
							}
						</style>
						<div class="cotp-tradeprint-main">
							<input type="hidden" id="tradeprint_product_id" name="tradeprint_product_id" value="<?php echo $tradeprint_product_attributes['productKey']; ?>">
							<div class="cotp-tradeprint cotp-product-attributes">
								<?php if( !empty($tradeprint_product_attributes['attributes'])){ ?>
									<?php foreach($tradeprint_product_attributes['attributes'] as $attribute_name => $attributes){ ?>
									
										<div class="cotp-product-attribute-single" <?php echo (!empty($admin_cotp_attr_hide) && in_array($attribute_name, $admin_cotp_attr_hide))?'style="display:none";':''; ?>>
											<label><?php echo $attribute_name; ?></label>
											<select data-cotpattribute="<?php echo $attribute_name; ?>" class="tradeprint_attr_select" name="tradeprint_attrs[<?php echo $attribute_name; ?>]">
												<option value="">Select</option>

												<?php if( !empty($attributes)){ ?>
													<?php foreach($attributes as $attribute){ 
														$is_this_default = false;
														if(isset($admin_cotp_attr_default[$attribute_name]) && $admin_cotp_attr_default[$attribute_name] == $attribute){
															$is_this_default = true;
														}
													?>
														<?php if(!isset($admin_cotp_attr[$attribute_name]) || !in_array($attribute, $admin_cotp_attr[$attribute_name])){ ?>
														<option <?php echo ($is_this_default)?'selected':''; ?> value="<?php echo $attribute; ?>"><?php echo $attribute; ?></option>
														<?php } ?>
													<?php } ?>
												<?php } ?>
											</select>
										</div>

									<?php } ?>
								<?php } ?>
								
							</div>
							
							<div class="cotp-tradeprint cotp-quantity-options">
								
							</div>

							<div class="cotp-tradeprint cotp-tradeprint-prices">
								
							</div>

							<div class="cotp-tradeprint cotp-tradeprint-expected-delivery">
								
							</div>

							<div class="cotp-tradeprint cotp-tradeprint-file-upload">
								
							</div>
						</div>

						<script>
							jQuery(document).ready(function($){
								let postcode_box = '<div class="cotp-delivery-box"><div class="cotp-postcode-field"><input type="text" name="cotp_postcode_field" id="cotp_postcode_field"></div><div class="cotp-postcode-submit"><button class="cotp-check-deliveryajax button alt wp-element-button" type="button">Check</button></div></div>';

								let file_upload_field = '<div class="cotp-upload-main"><button id="cotp_upload_now_btn" type="button" class="button alt wp-element-button">Upload Now</button><a id="cotp_upload_later" href="#">Upload Later</a><input class="button alt wp-element-button cotp-uploadNow_select" type="file" accept=".pdf" id="tradeprint_upload" name="tradeprint_upload"></div>';

								$('form.cart .single_add_to_cart_button').prop('disabled', true);

								$('.tradeprint_attr_select').on('change', function(){
									cotp_get_product_prices_ajax();
								});

								$(document).on('change', '.tradeprint_quantity_options', function(){
									var selected_quantity = $(this).val();
									$('.cotp-tradeprint-prices .tradeprice_service_level').hide();
									$('.cotp-tradeprint-prices .tradeprice_service_level_qty_'+selected_quantity).show();
									$('form.cart .single_add_to_cart_button').prop('disabled', true);
									$('.cotp-tradeprint-expected-delivery').html('');
								});

								$(document).on('change','input[name="tradeprint_service_level"]', function(){
									$('.cotp-tradeprint-expected-delivery').html('');
									$('.cotp-tradeprint-file-upload').html('');
									
									if($('input[name="tradeprint_service_level"]:checked').length > 0){
										$('form.cart .single_add_to_cart_button').prop('disabled', false);

										$('.cotp-tradeprint-expected-delivery').html(postcode_box);
										$('.cotp-tradeprint-file-upload').html(file_upload_field);
									}
									else{
										$('form.cart .single_add_to_cart_button').prop('disabled', true);
									}
								});

								$(document).on('click','.cotp-check-deliveryajax', function(){
									cotp_get_expected_delivery_date();
								});

								$(document).on('click', '#cotp_upload_now_btn', function(){
									$(this).hide();
									$('#tradeprint_upload').show();
								});

								$(document).on('click', '#cotp_upload_later', function(event){
									event.preventDefault();
									$('#cotp_upload_now_btn').show();
									$('#tradeprint_upload').hide();
									$('#tradeprint_upload').val('');
								});

								function cotp_get_expected_delivery_date(){
									//$('.cotp-tradeprint-expected-delivery').html('');

									var all_attribute_selected = true;
									var selected_attributes = {};
									var product_id = $("#tradeprint_product_id").val();
									var quantity_ = $(".tradeprint_quantity_options").val();
									var service_level_ = $('input[name="tradeprint_service_level"]:checked').val();

									var postcode = $('#cotp_postcode_field').val();

									$('.tradeprint_attr_select').each(function(){
										var attr_name = $(this).attr('data-cotpattribute');
										var attr_value = $(this).val();
										selected_attributes[attr_name] = attr_value;

										if(attr_value == ''){
											all_attribute_selected = false;
										}
									});

									if(all_attribute_selected && postcode != ''){
										$('.cotp-tradeprint-main').css('opacity', '.5');
										$.ajax({
											url: '<?php echo admin_url('admin-ajax.php'); ?>',
											data: {
												action:'tradeprint_expected_delivery_ajax',
												wc_product_id:<?php echo $product->get_id(); ?>,
												selected_attributes:selected_attributes,
												product_id:product_id,
												quantity:quantity_,
												service_level:service_level_,
												postcode:postcode
											},
											type: 'post',
											success: function(response){
												$('.temp-res').remove();
												$('#cotp_postcode_field').after('<span class="temp-res">'+response.delivery_data+'</span>');
// 												if(response.success && response.prices_options && response.prices_options.length > 0){
													
// 												}
// 												else{
													
// 												}

												$('.cotp-tradeprint-main').css('opacity', '1');
											}
										});
									}
									else{
										$('.cotp-tradeprint-expected-delivery').html(postcode_box);
									}
								}

								function cotp_get_product_prices_ajax(){
									$('.cotp-quantity-options').html('');
									$('.cotp-tradeprint-prices').html('');
									$('.cotp-tradeprint-expected-delivery').html('');
									$('form.cart .single_add_to_cart_button').prop('disabled', true);

									var all_attribute_selected = true;
									var selected_attributes = {};
									var product_id = $("#tradeprint_product_id").val();
									$('.tradeprint_attr_select').each(function(){
										var attr_name = $(this).attr('data-cotpattribute');
										var attr_value = $(this).val();
										selected_attributes[attr_name] = attr_value;

										if(attr_value == ''){
											
											all_attribute_selected = false;
											
										}
									});

									if(all_attribute_selected){
										$('.cotp-tradeprint-main').css('opacity', '.5');
										$.ajax({
											url: '<?php echo admin_url('admin-ajax.php'); ?>',
											data: {action:'tradeprint_product_prices_ajax',wc_product_id:<?php echo $product->get_id(); ?>,selected_attributes:selected_attributes,product_id:product_id},
											type: 'post',
											success: function(response){
												if(response.success && response.prices_options && response.prices_options.length > 0){
													$('.cotp-quantity-options').append('<label>Quantity Options</label>');
													$('.cotp-quantity-options').append('<select class="tradeprint_quantity_options" name="tradeprint_quantity"><option value="">Select Quantity</option></select>');
												
													$.each(response.prices_options, function( index, value ) {
														var option_ = document.createElement("option");
														option_.value = value.quantity;
														option_.innerHTML = value.quantity;
														$('.cotp-quantity-options .tradeprint_quantity_options').append(option_);
														
														$('.cotp-tradeprint-prices').append('<div class="tradeprice_service_level tradeprice_service_level_qty_'+value.quantity+'" style="display:none"></div>');

														$.each(value.prices, function( price_index, price_value ) {
															$('.cotp-tradeprint-prices .tradeprice_service_level_qty_'+value.quantity).append('<label class="service_level_opt_'+convertToSlug(price_value.serviceLevel)+'"><input type="radio" name="tradeprint_service_level" value="'+price_value.serviceLevel+'"><?php echo get_woocommerce_currency_symbol(); ?> '+price_value.price+' ('+price_value.serviceLevel+')</label>');
														});
													});
												}
												else{
													$('.cotp-quantity-options').html('');
													$('.cotp-tradeprint-prices').html('');
													$('form.cart .single_add_to_cart_button').prop('disabled', true);
													if(response.prices_options == false){
														$('.cotp-quantity-options').html('<p class="woocommerce-error message-wrapper danger-color">No product found for this combination.</p>');
													}
												}

												$('.cotp-tradeprint-main').css('opacity', '1');
											}
										});
									}
									else{
										$('.cotp-quantity-options').html('');
										$('.cotp-tradeprint-prices').html('');
										$('form.cart .single_add_to_cart_button').prop('disabled', true);
									}
									
								}
							});
							
							function convertToSlug(Text) {
							  return Text.toLowerCase()
								.replace(/ /g, "-")
								.replace(/[^\w-]+/g, "");
							}
						</script>
					<?php
				}
			}
			
		}
	}

	/**
	 * tradeprint product prices ajax callback.
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_product_prices_ajax(){
		$result = array();
		$result['success'] = false;
		$selected_attributes = $_POST['selected_attributes']??array();
		$product_id = $_POST['product_id']??'';

		$wc_product_id = $_POST['wc_product_id'];

		$cotp_product_commission = $this->cotp_get_commssion_percentage( $wc_product_id );

		if( !empty($selected_attributes) && $product_id != ''){
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			$tradeprint_product_prices = $tradeprint_api->get_product_prices( $product_id, $selected_attributes );

			if($tradeprint_product_prices && !empty($tradeprint_product_prices) && $cotp_product_commission > 0){
				foreach($tradeprint_product_prices as $key => $tradeprint_product_price){
					if(!empty($tradeprint_product_price['prices'])){
						foreach($tradeprint_product_price['prices'] as $key2 => $price_){
							$tradeprint_product_price['prices'][$key2]['price'] = round(( $price_['price'] + ($price_['price'] * ( $cotp_product_commission/100 ))), 2);
							
						}
					}

					$tradeprint_product_prices[$key] = $tradeprint_product_price;
				}
			}

			$result['success'] = true;
			$result['msg'] = 'Prices Fatched';
			$result['prices_options'] = $tradeprint_product_prices;
		}
		else{
			$result['msg'] = 'Attributes or tradeprint product is missing';
		}

		wp_send_json($result); die;
	}

	/**
	 * tradeprint product cart item data
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_cart_item_data( $cart_item_data, $product_id, $variation_id ){
		if(is_tradeprint_product($product_id)){
			if ( isset( $_POST['tradeprint_attrs'] ) && !empty($_POST['tradeprint_attrs'])) {
				$cart_item_data['tradeprint_attrs'] = $_POST['tradeprint_attrs'];
				
			}
			if ( isset( $_POST['tradeprint_quantity'] ) ) {
				$cart_item_data['tradeprint_quantity'] = sanitize_text_field( $_POST['tradeprint_quantity'] );
			}
			if ( isset( $_POST['tradeprint_service_level'] ) ) {
				$cart_item_data['tradeprint_service_level'] = sanitize_text_field( $_POST['tradeprint_service_level'] );
			}
			if ( isset( $_POST['tradeprint_product_id'] ) ) {
				$cart_item_data['tradeprint_product_id'] = sanitize_text_field( $_POST['tradeprint_product_id'] );
			}

			if ( isset( $_FILES["tradeprint_upload"] ) && 0 == $_FILES['tradeprint_upload']['error'] ) {
				$tradeprint_upload = create_attachment($_FILES['tradeprint_upload']);
				$cart_item_data['tradeprint_attachment_id'] = $tradeprint_upload;
			}
		}
		return $cart_item_data;
	}

	/**
	 * tradeprint product display cart item data
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_get_cart_item_data( $item_data, $cart_item_data ){
		if ( isset( $cart_item_data['tradeprint_attrs'] ) && !empty($cart_item_data['tradeprint_attrs']) ) {

			foreach($cart_item_data['tradeprint_attrs'] as $attr_name => $attr_value){
				$item_data[] = array(
					'key'   => __( $attr_name ),
					'value' => wc_clean( $attr_value ),
				);
			}
			
		}

		if ( isset( $cart_item_data['tradeprint_quantity'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Tradeprint Quantity' ),
				'value' => wc_clean( $cart_item_data['tradeprint_quantity'] ),
			);
		}

		if ( isset( $cart_item_data['tradeprint_service_level'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Service Level' ),
				'value' => wc_clean( $cart_item_data['tradeprint_service_level'] ),
			);
		}

		// if ( isset( $cart_item_data['tradeprint_attachment_id'] ) ) {
		// 	$item_data[] = array(
		// 		'key'   => __( 'File' ),
		// 		'value' => '<a href="'.wp_get_attachment_url($cart_item_data['tradeprint_attachment_id']).'">Download Uploaded File</a>'
		// 	);
		// }

		return $item_data;
	}

	/**
	 * tradeprint product order item data
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_order_item_data( $item, $cart_item_key, $values, $order ){

		if ( isset( $values['tradeprint_attrs'] ) && !empty($values['tradeprint_attrs'])) {

			foreach($values['tradeprint_attrs'] as $attr_name => $attr_value){
				$item->add_meta_data(
					__( $attr_name ),
					$attr_value,
					true
				);
			}
			
		}

		if ( isset( $values['tradeprint_attrs'] ) && !empty($values['tradeprint_attrs'])) {
			$item->add_meta_data(
				'tradeprint_production_data',
				$values['tradeprint_attrs']
			);
			
		}
		
		
		if ( isset( $values['tradeprint_quantity'] ) ) {
			$item->add_meta_data(
				__( 'Tradeprint Quantity' ),
				$values['tradeprint_quantity'],
				true
			);
		}
		if ( isset( $values['tradeprint_service_level'] ) ) {
			$item->add_meta_data(
				__( 'Service Level' ),
				$values['tradeprint_service_level'],
				true
			);
		}
		if ( isset( $values['tradeprint_product_id'] ) ) {
			$item->add_meta_data(
				__( 'Tradeprint Product Id' ),
				$values['tradeprint_product_id'],
				true
			);
		}

		if ( isset( $values['tradeprint_attachment_id'] ) ) {
			$item->add_meta_data(
				__( 'File' ),
				'<a target="_blank" href="'.wp_get_attachment_url($values['tradeprint_attachment_id']).'">Download Uploaded File</a>',
				true
			);
		}

		if ( isset( $values['tradeprint_attachment_id'] ) ) {
			$item->add_meta_data(
				'tradeprint_uploaded_url',
				array(
					'url' => wp_get_attachment_url($values['tradeprint_attachment_id'])
				),
				true
			);
		}
	}

	/**
	 * tradeprint product price calculate
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_calculate_item_price( $cart_object ){
		$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			if(is_tradeprint_product( $product_id )){

				$cotp_product_commission = $this->cotp_get_commssion_percentage( $product_id );
				
				if( isset( $cart_item["tradeprint_attrs"] ) 
				&& isset( $cart_item["tradeprint_quantity"] ) 
				&& isset( $cart_item["tradeprint_service_level"] ) 
				&& isset( $cart_item["tradeprint_product_id"] ) ) {

					
					$tradeprint_product_prices = $tradeprint_api->get_product_prices( $cart_item["tradeprint_product_id"], $cart_item["tradeprint_attrs"], $cart_item["tradeprint_quantity"], $cart_item["tradeprint_service_level"] );
					
					if($tradeprint_product_prices){
						$price_ = $tradeprint_product_prices[0]['prices'][0]['price']??0;

						if($cotp_product_commission > 0){
							$price_ = round(( $price_ + ( $price_ * ($cotp_product_commission/100))), 2);
						}
						if($price_ > 0){
							if( method_exists( $cart_item['data'], "set_price" ) ) {
											
								 $cart_item['data']->set_price( $price_ );
							} else {
								  
								 $cart_item['data']->price = ( $price_ );                    
							} 
					 	}
					}
				}
			}
			
		}
	}


	/**
	 * tradeprint expected delivery ajax callback.
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_expected_delivery_ajax(){
		$result = array();
		$result['success'] = false;
		$selected_attributes = $_POST['selected_attributes']??array();
		$product_id = $_POST['product_id']??'';

		$wc_product_id = $_POST['wc_product_id'];

		$quantity = $_POST['quantity']??'';
		$service_level = $_POST['service_level']??'';

		$post_code = $_POST['postcode']??'';

		$artwork_services = array(
			'Just Print',
			'File Check',
			'File Assist or File Check with Proofing'
		);

		if( $post_code != '' && !empty($selected_attributes) && $product_id != '' && $quantity != '' && $service_level != ''){
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			
			$tradeprint_delivery_data = $tradeprint_api->get_expected_delivery_date( $product_id, $selected_attributes, $quantity, $service_level, $artwork_services[0], $post_code );

			

			$result['success'] = true;
			$result['msg'] = 'Expected Delivery Fatched';
			$result['delivery_data'] = $tradeprint_delivery_data;
		}
		else{
			$result['msg'] = 'Attributes or tradeprint product or postcode is missing';
		}

		wp_send_json($result); die;
	}

	public function cotp_manage_account_orders_column( $columns ){
		$order_actions  = $columns['order-actions']; // Save Order actions
		unset($columns['order-actions']); // Remove Order actions
		unset($columns['order-status']);
		// Add your custom column key / label
		$columns['cotp-status'] = __( 'Status', 'woocommerce' );

		// Add back previously saved "Order actions"
		$columns['order-actions'] = $order_actions;

		return $columns;
	}

	public function cotp_account_orders_column_rows( $order ){
		$cotp_tradeprint_order_created = get_post_meta($order->get_id(), 'cotp_tradeprint_order_created', true);
		if(isset($cotp_tradeprint_order_created) && $cotp_tradeprint_order_created == 'yes'){
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			$tradeprint_order_status = $tradeprint_api->get_order_status( 'cotp_wc_'.$order->get_id() );
			if($tradeprint_order_status){
				echo esc_html($tradeprint_order_status['status']);
			}
			else{
				echo esc_html( wc_get_order_status_name( $order->get_status() ) );
			}
		}
		else{
			echo esc_html( wc_get_order_status_name( $order->get_status() ) );
		}
	}

	public function cotp_show_myaccount_order_status( $order ){
		
		$cotp_tradeprint_order_created = get_post_meta($order->get_id(), 'cotp_tradeprint_order_created', true);
		if(isset($cotp_tradeprint_order_created) && $cotp_tradeprint_order_created == 'yes'){
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			$tradeprint_order_status = $tradeprint_api->get_order_status( 'cotp_wc_'.$order->get_id() );
			if($tradeprint_order_status){
				echo '<mark class="cotr-status">'.$tradeprint_order_status['status'].'</mark>';
				echo '<style>.order-status{display:none !important;}</style>';
			}
			else{
				echo '<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>';
			}
		}
		else{
			echo '<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>';
		}
	}

	/**
	 * tradeprint get product commission
	 * @since    1.0.0
	 */
	public function cotp_get_commssion_percentage( $product_id ){
		$cotp_product_commission_global = get_option('cotp_commission_global')??0;

		$cotp_product_commission = get_post_meta($product_id, 'cotp_product_commission', true);
		$cotp_product_commission = $cotp_product_commission??0;
		if(!isset($cotp_product_commission) || $cotp_product_commission == '' || $cotp_product_commission == 0){
			$cotp_product_commission = $cotp_product_commission_global;
		}

		return $cotp_product_commission;
	}
}
