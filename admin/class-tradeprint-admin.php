<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://cloud1.me/
 * @since      1.0.0
 *
 * @package    Tradeprint
 * @subpackage Tradeprint/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tradeprint
 * @subpackage Tradeprint/admin
 * @author     Gaurav Garg <gauravgargcs1991@gmail.com>
 */
class Tradeprint_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_filter( 'product_type_options', array($this, 'cotp_tradeprint_product_type') );
		add_action( 'woocommerce_process_product_meta_simple', array($this, 'cotp_tradeprint_product_type_save') );
		add_action( 'woocommerce_process_product_meta_variable', array($this, 'cotp_tradeprint_product_type_save') );
		
		add_filter('woocommerce_product_data_tabs', array($this, 'cotp_tradeprint_options_product_tab') );
		add_action( 'woocommerce_product_data_panels', array($this, 'cotp_tradeprint_options_product_tab_content') );
		add_action( 'woocommerce_process_product_meta', array($this, 'cotp_tradeprint_options_product_tab_save') );

		add_filter( 'woocommerce_settings_tabs_array', array($this, 'cotp_tradeprint_setting_tab'), 21 );
		add_action( 'woocommerce_settings_tradeprint', array($this, 'cotp_tradeprint_setting_tab_content') );
		add_action( 'woocommerce_settings_save_tradeprint', array($this, 'cotp_tradeprint_setting_tab_save') );
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tradeprint-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tradeprint-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * adding tradeprint product type option
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_product_type( $product_type_options ){
		$product_type_options['cotp_tradeprint'] = array(
			'id'            => '_cotp_tradeprint',
			'wrapper_class' => 'show_if_simple',
			'label'         => __( 'Tradeprint', 'woocommerce' ),
			'description'   => __( 'Tradeprint', 'woocommerce' ),
			'default'       => 'no'
		);

		return $product_type_options;
	}

	/**
	 * saving tradeprint product type option
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_product_type_save( $product_id ){
		$cotp_tradeprint = isset( $_POST['_cotp_tradeprint'] ) ? 'yes' : 'no';
		update_post_meta( $product_id, '_cotp_tradeprint', $cotp_tradeprint );
	}

	/**
	 * adding tradeprint setting tab
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_setting_tab( $tabs ){
		$tabs['tradeprint'] = 'Tradeprint';
		return $tabs;
	}

	/**
	 * setting fields tradeprint setting tab
	 *
	 * @since    1.0.0
	 */

	private function cotp_tradeprint_settings() {

		global $current_section;

		$settings = array(
			array(
				'name' => 'Tradeprint API Settings',
				'type' => 'title'
			),
			array(
				'name'     => 'Username',
				'id'       => 'cotp_api_username',
				'type'     => 'text',
			),
			array(
				'name'     => 'Password',
				'id'       => 'cotp_api_password',
				'type'     => 'text',
			),
			array(
				'name'     => 'Sandbox',
				'id'       => 'cotp_api_sandbox',
				'type'     => 'checkbox',
			),
			array(
				'type' => 'sectionend',
			),
		);
		return $settings;

	}

	/**
	 * content callback tradeprint setting tab
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_setting_tab_content(){
		
		WC_Admin_Settings::output_fields( $this->cotp_tradeprint_settings() );
	}

	/**
	 * save callback tradeprint setting tab
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_setting_tab_save(){
		WC_Admin_Settings::save_fields( $this->cotp_tradeprint_settings() );
	}

	/**
	 * traprint product setting tab
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_options_product_tab( $tabs ){
		$tabs['cotp_tradeprint_options'] = array(
			'label'    => 'Tradeprint Options',
			'target'   => 'cotp_tradeprint_options_tab',
			'class'    => array('show_if_cotp_tradeprint')
			/*'priority' => 21,*/
		);
		return $tabs;
	}

	/**
	 * traprint product setting tab content
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_options_product_tab_content(){
		echo '<div id="cotp_tradeprint_options_tab" class="panel woocommerce_options_panel">';

		woocommerce_wp_text_input(
			array(
			  'id' => 'cotp_product_name',
			  'label' => __( 'Tradeprint Product Name' ),
			  'placeholder' => '',
			  'type' => 'text',
			)
		);

		echo '</div>';
		echo "<script>
			jQuery( document ).ready( function( $ ) {

				$( 'input#_cotp_tradeprint' ).change( function() {
					var is_cotp_tradeprint = $( 'input#_cotp_tradeprint:checked' ).size();

					$( '.show_if_cotp_tradeprint' ).hide();
					$( '.hide_if_cotp_tradeprint' ).hide();

					if ( is_cotp_tradeprint ) {
						$( '.hide_if_cotp_tradeprint' ).hide();
					}
					if ( is_cotp_tradeprint ) {
						$( '.show_if_cotp_tradeprint' ).show();
					}
					if(!is_cotp_tradeprint){
						$('#cotp_tradeprint_options_tab').hide();
					}
				});
				$( 'input#_cotp_tradeprint' ).trigger( 'change' );
			});
		</script>";
	}

	/**
	 * save traprint product setting tab content
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_options_product_tab_save($product_id){
		$cotp_product_name = $_POST['cotp_product_name']??'';
		if( !empty( $cotp_product_name ) ) {
	        update_post_meta( $product_id, 'cotp_product_name', esc_attr( $cotp_product_name ) );
	    }
	}

}