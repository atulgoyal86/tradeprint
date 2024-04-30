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
			if(isset($tradeprint_product_name) && $tradeprint_product_name != ''){
				$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			
				$tradeprint_product_attributes = $tradeprint_api->get_product_attributes( $tradeprint_product_name );

				if($tradeprint_product_attributes && !empty($tradeprint_product_attributes)){
					?>
						<style>
							.single-product span.woocommerce-Price-amount.amount {
								display: none;
							}
						</style>
						<div class="cotp-tradeprint-main">
							<input type="hidden" id="tradeprint_product_id" name="tradeprint_product_id" value="<?php echo $tradeprint_product_attributes['productKey']; ?>">
							<div class="cotp-tradeprint cotp-product-attributes">
								<?php if( !empty($tradeprint_product_attributes['attributes'])){ ?>
									<?php foreach($tradeprint_product_attributes['attributes'] as $attribute_name => $attributes){ ?>
									
										<div class="cotp-product-attribute-single">
											<label><?php echo $attribute_name; ?></label>
											<select data-cotpattribute="<?php echo $attribute_name; ?>" class="tradeprint_attr_select" name="tradeprint_attrs[<?php echo $attribute_name; ?>]">
												<option value="">Select</option>

												<?php if( !empty($attributes)){ ?>
													<?php foreach($attributes as $attribute){ ?>
														<option value="<?php echo $attribute; ?>"><?php echo $attribute; ?></option>
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
						</div>

						<script>
							jQuery(document).ready(function($){
								$('.tradeprint_attr_select').on('change', function(){
									cotp_get_product_prices_ajax();
								});

								$(document).on('change', '.tradeprint_quantity_options', function(){
									var selected_quantity = $(this).val();
									$('.cotp-tradeprint-prices .tradeprice_service_level').hide();
									$('.cotp-tradeprint-prices .tradeprice_service_level_qty_'+selected_quantity).show();
								});

								function cotp_get_product_prices_ajax(){
									$('.cotp-quantity-options').html('');
									$('.cotp-tradeprint-prices').html('');

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
											data: {action:'tradeprint_product_prices_ajax',selected_attributes:selected_attributes,product_id:product_id},
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
															$('.cotp-tradeprint-prices .tradeprice_service_level_qty_'+value.quantity).append('<label><input type="radio" name="tradeprint_service_level" value="'+price_value.serviceLevel+'"><?php echo get_woocommerce_currency_symbol(); ?> '+price_value.price+' ('+price_value.serviceLevel+')</label>');
														});
													});
												}
												else{
													$('.cotp-quantity-options').html('');
													$('.cotp-tradeprint-prices').html('');
												}

												$('.cotp-tradeprint-main').css('opacity', '1');
											}
										});
									}
									else{
										$('.cotp-quantity-options').html('');
										$('.cotp-tradeprint-prices').html('');
									}
									
								}
							});
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
		if( !empty($selected_attributes) && $product_id != ''){
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			$tradeprint_product_prices = $tradeprint_api->get_product_prices( $product_id, $selected_attributes );

			$result['success'] = true;
			$result['msg'] = 'Prices Fatched';
			$result['prices_options'] = $tradeprint_product_prices;
		}
		else{
			$result['msg'] = 'Attributes or tradeprint product is missing';
		}

		wp_send_json($result); die;
	}
}
