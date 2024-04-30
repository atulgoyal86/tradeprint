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
						<div class="cotp-tradeprint cotp-product-attributes">
							<?php if( !empty($tradeprint_product_attributes['attributes'])){ ?>
								<?php foreach($tradeprint_product_attributes['attributes'] as $attribute_name => $attributes){ ?>
								
									<div class="cotp-product-attribute-single">
										<label><?php echo $attribute_name; ?></label>
										<select class="tradeprint_attr_select" name="tradeprint_attrs[<?php echo $attribute_name; ?>]">
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
					<?php
				}
			}
			
		}
	}

}
