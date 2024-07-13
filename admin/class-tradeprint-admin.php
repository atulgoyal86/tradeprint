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


		add_action('wp_ajax_tradeprint_product_admin_ajax', array($this, 'cotp_tradeprint_product_admin_ajax'));
		add_action('wp_ajax_nopriv_tradeprint_product_admin_ajax', array($this, 'cotp_tradeprint_product_admin_ajax'));


		add_filter( 'manage_woocommerce_page_wc-orders_columns', array($this, 'cotp_tradeprint_add_new_order_admin_list_column'),20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column' , array($this, 'cotp_tradeprint_add_new_order_admin_list_column_value'), 20, 2 );

		add_action( 'admin_init', array($this, 'cotp_tradeprint_create_order'));

		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'cotp_admin_tradeprint_order_details'));
		
		
		// order item file upload option for every item in order in admin order edit screen
		add_action( 'woocommerce_admin_order_item_headers', array( $this, 'cotp_admin_order_item_headers') );
		add_action( 'woocommerce_admin_order_item_values', array( $this, 'cotp_admin_order_item_values'), 9999, 3 );
		
		add_action( 'woocommerce_before_save_order_item', array( $this, 'cotp_update_order_item_files'), 9999 );
	}
	
	public function cotp_update_order_item_files( $item ){
		if ( $item->get_type() !== 'line_item' ) return;
		if ( ! $_POST ) return;
		if ( isset( $_POST['items'] ) ) {
		  // ITS AJAX SAVE
		  parse_str( rawurldecode( $_POST['items'] ), $output );
		} else {
		  $output = $_POST;
		}
	   
	   $item->update_meta_data( 'tradeprint_attachment_id', array( 'tradeprint_attachment_id'=> $output['tradeprint_attachment_id'][$item->get_id()]) );
	   $item->update_meta_data( 'tradeprint_uploaded_url', array( 'url'=> wp_get_attachment_url($output['tradeprint_attachment_id'][$item->get_id()])) );
	}
	
	public function cotp_admin_order_item_headers( $order ){
		echo '<th class="cotp_item_file">File</th>';
	}
	
	public function cotp_admin_order_item_values( $product, $item, $item_id ){
		$cotp_item_file_id = $item->get_meta( 'tradeprint_attachment_id', true ) ? $item->get_meta( 'tradeprint_attachment_id', true ) : '';
        
		$cotp_item_file_url = wp_get_attachment_url( $cotp_item_file_id['tradeprint_attachment_id'] );
		echo '<td class="cotp_item_file" width="10%">';
		
		
		if( $cotp_item_file_url ){ ?>
			<a href="<?php echo esc_url( $cotp_item_file_url ) ?>" class="button" target="_blank">
				View File
			</a>
			<a href="#" class="cotp-remove">Remove File</a>
			<input type="hidden" name="tradeprint_attachment_id[<?php echo $item_id; ?>]" value="<?php echo absint( $cotp_item_file_id['tradeprint_attachment_id'] ) ?>">
		<?php }else{ ?>
			<button type="button" class="button cotp-upload">Upload File</button>
			<a href="#" class="cotp-remove" style="display:none">Remove File</a>
			<input type="hidden" name="tradeprint_attachment_id[<?php echo $item_id; ?>]" value="">
		<?php }
		echo '</td>';
		//echo '<td class="cotp_item_file" width="10%"><div class="view"></div><input type="text" name="cotp_item_file[' . $item_id . ']" value="' . $cotp_item_file['url'] . '" class=""></div></td>';
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tradeprint-admin.css', array(), $this->version.'.2', 'all' );

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
		
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tradeprint-admin.js', array( 'jquery' ), $this->version.'.4', false );

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
			array(
				'name' => 'Commission Settings',
				'type' => 'title'
			),
			array(
				'name'     => 'Commission',
				'id'       => 'cotp_commission_global',
				'type'     => 'number',
				'default' => 0,
				'custom_attributes' => array(
					'step' 	=> '.01',
					'min'	=> '0'
				)
			),
			array(
				'type' => 'sectionend',
			),

			array(
				'name' => 'Delivery Settings',
				'type' => 'title'
			),
			array(
				'name'     => 'Extend estimated delivery days',
				'id'       => 'cotp_extend_delivery_days',
				'type'     => 'number',
				'default' => 0,
				'custom_attributes' => array(
					'step' 	=> '1',
					'min'	=> '0'
				)
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
	 * traprint product admin ajax to get availabe attributes
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_product_admin_ajax(){
		$result = array();
		$result['success'] = false;

		$product_name = $_POST['product_name']??'';

		$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			
		$tradeprint_product_attributes = $tradeprint_api->get_product_attributes( $product_name );

		if($tradeprint_product_attributes && !empty($tradeprint_product_attributes)){
			$result['success'] = true;
			$result['msg'] = 'Attributes Fatched';
			$result['available_attributes'] = $tradeprint_product_attributes['attributes'];
		}
		else{
			$result['msg'] = 'No Attributes Fatched';
		}

		wp_send_json($result); die;
	}

	/**
	 * traprint product setting tab content
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_options_product_tab_content(){
		global $post;
		$admin_cotp_attr = get_post_meta( $post->ID, 'admin_cotp_attr', true );
		$admin_cotp_attr = $admin_cotp_attr??array();
		$cotp_product_name = get_post_meta( $post->ID, 'cotp_product_name', true );
		
		$admin_cotp_attr_default = get_post_meta( $post->ID, 'admin_cotp_attr_default', true );
		$admin_cotp_attr_default = $admin_cotp_attr_default??array();
		
		$admin_cotp_attr_hide = get_post_meta( $post->ID, 'admin_cotp_attr_hide', true );
		$admin_cotp_attr_hide = $admin_cotp_attr_hide??array();
		
		$admin_cotp_attr_html = '';
		if($cotp_product_name != ''){
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			
			$tradeprint_product_attributes = $tradeprint_api->get_product_attributes( $cotp_product_name );
			
			if($tradeprint_product_attributes && !empty($tradeprint_product_attributes)){
				$available_attributes = $tradeprint_product_attributes['attributes'];

				if( !empty($available_attributes)){
					foreach($available_attributes as $attribute_name => $attributes){

						$op = '';
						$default_op = '';
						if( !empty($attributes)){
							foreach($attributes as $attribute){
								if(isset($admin_cotp_attr[$attribute_name]) && in_array($attribute, $admin_cotp_attr[$attribute_name])){
									$op .= '<option selected value="'.$attribute.'">'.$attribute.'</option>';
								}
								else{
									$op .= '<option value="'.$attribute.'">'.$attribute.'</option>';
								}
								
								if(isset($admin_cotp_attr_default[$attribute_name]) &&  $admin_cotp_attr_default[$attribute_name] == $attribute){
									$default_op .= '<option selected value="'.$attribute.'">'.$attribute.'</option>';
								}
								else{
									$default_op .= '<option value="'.$attribute.'">'.$attribute.'</option>';
								}
							}
						}

						$admin_cotp_attr_html .= '<div class="cotp_admin_field">
						<label>'.$attribute_name.'</label>
						<select multiple class="tradeprint_admin_options" name="admin_cotp_attr['.$attribute_name.'][]">
						<option value="">Select</option>'.$op.'</select>
						<label><input type="checkbox" name="admin_cotp_attr_hide[]" '.(!empty($admin_cotp_attr_hide) && in_array($attribute_name, $admin_cotp_attr_hide)?'checked':'').' value="'.$attribute_name.'"> Hide</label>
						
						<label>Default Value ('.$attribute_name.')</label>
						<select class="tradeprint_admin_options" name="admin_cotp_attr_default['.$attribute_name.']">
						<option value="">Select</option>'.$default_op.'</select>
						</div>';
					}
				}
					
			}
		}
		
		echo '<div id="cotp_tradeprint_options_tab" class="panel woocommerce_options_panel">';
		
		woocommerce_wp_text_input(
			array(
			  'id' => 'cotp_product_name',
			  'label' => __( 'Tradeprint Product Name' ),
			  'placeholder' => '',
			  'type' => 'text',
			)
		);

		woocommerce_wp_text_input(
			array(
			  'id' => 'cotp_product_commission',
			  'label' => __( 'Tradeprint Product Commission (%)' ),
			  'placeholder' => '',
			  'default' => 0,
			  'type' => 'number', 
				'custom_attributes' => array(
					'step' 	=> '.01',
					'min'	=> '0'
				) 
			)
		);
		
		woocommerce_wp_checkbox( array(
			'id'            => 'cotp_product_hide_file_upload',
			'label'         => __( 'Hide File Upload Field' )
		) );

		echo '<div class="cotp-attribute-settings">';
		echo '<button id="cotp-get-available-attr-btn" type="button" class="button primary">Get Available Attributes</button>';
		echo '<h3>Exclude Attributes</h3>';
		echo '<div class="cotp-available-attr-selection">'.$admin_cotp_attr_html.'</div>';

		echo '</div>';

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
		
		if( isset($_POST['cotp_product_commission']) ) {
	        update_post_meta( $product_id, 'cotp_product_commission', esc_attr( $_POST['cotp_product_commission'] ) );
	    }
		
		if( isset($_POST['cotp_product_hide_file_upload']) ) {
	        update_post_meta( $product_id, 'cotp_product_hide_file_upload', esc_attr( $_POST['cotp_product_hide_file_upload'] ) );
	    }
		else{
			update_post_meta( $product_id, 'cotp_product_hide_file_upload', 'no' );
		}

		if(isset($_POST['admin_cotp_attr']) && !empty($_POST['admin_cotp_attr'])){
			update_post_meta( $product_id, 'admin_cotp_attr', $_POST['admin_cotp_attr'] );
		}
		else{
			update_post_meta( $product_id, 'admin_cotp_attr', array() );
		}
		
		if(isset($_POST['admin_cotp_attr_default']) && !empty($_POST['admin_cotp_attr_default'])){
			update_post_meta( $product_id, 'admin_cotp_attr_default', $_POST['admin_cotp_attr_default'] );
		}
		else{
			update_post_meta( $product_id, 'admin_cotp_attr_default', array() );
		}
		
		if(isset($_POST['admin_cotp_attr_hide']) && !empty($_POST['admin_cotp_attr_hide'])){
			update_post_meta( $product_id, 'admin_cotp_attr_hide', $_POST['admin_cotp_attr_hide'] );
		}
		else{
			update_post_meta( $product_id, 'admin_cotp_attr_hide', array() );
		}
	}

	/**
	 * woocommerce order custom column
	 *
	 * @since    1.0.0
	 */
	public function cotp_tradeprint_add_new_order_admin_list_column( $columns ){
		$columns['cotp_tradeprint_order'] = 'Tradeprint Order';
    	return $columns;
	}

	public function cotp_tradeprint_add_new_order_admin_list_column_value( $column, $order ){
		switch ( $column )
    	{
			case 'cotp_tradeprint_order' :
				$cotp_tradeprint_order_created = get_post_meta($order->get_id(), 'cotp_tradeprint_order_created', true);
				//echo get_post_meta($order->get_id(), 'cotp_tradeprint_order', true);
				if(isset($cotp_tradeprint_order_created) && $cotp_tradeprint_order_created == 'yes'){
					echo 'Created';
					
				}
				else{
					
					/*echo '<label><input type="checkbox" data-orderid="'.$order->get_id().'" class="cotp_shipto_shop_address_check cotp_shipto_shop_address_check_'.$order->get_id().'" value="1"> Ship to store</label><br>
					<a data-orderid="'.$order->get_id().'" class="button button-primary cotp_create_order_btn cotp_create_order_'.$order->get_id().'" data-mainhref="'.admin_url('/admin.php?page=wc-orders&process_tradeprint_order='.$order->get_id()).'" href="'.admin_url('/admin.php?page=wc-orders&process_tradeprint_order='.$order->get_id()).'">Create</a>'; */
					
					echo '<button type="button" data-orderid="'.$order->get_id().'" class="button button-primary cotp_create_order_button cotp_create_order_button_'.$order->get_id().'" data-mainhref="'.admin_url('/admin.php?page=wc-orders&process_tradeprint_order='.$order->get_id()).'">Create</button>';
				}
				break;
		}
	}

	public function cotp_tradeprint_create_order(){
		if(isset($_GET['process_tradeprint_order']) && $_GET['process_tradeprint_order'] != ''){
			
			$cotp_ship_to_store = (isset($_GET['cotp_ship_to_store']))??'false';
			
			$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
			$tradeprint_order = $tradeprint_api->create_order( $_GET['process_tradeprint_order'], $cotp_ship_to_store );

			echo '<script>location.replace("'.admin_url('/admin.php?page=wc-orders').'")</script>';
			if($tradeprint_order){
				
			}
		}
	}

	public function cotp_admin_tradeprint_order_details( $order ){
		$cotp_tradeprint_order_created = get_post_meta($order->get_id(), 'cotp_tradeprint_order_created', true);
		$cotp_tradeprint_order_response = get_post_meta($order->get_id(), 'cotp_tradeprint_order', true);

		if(isset($cotp_tradeprint_order_response) && $cotp_tradeprint_order_response != ''){
			$cotp_tradeprint_order_response = json_decode($cotp_tradeprint_order_response, true);

			echo '<div class="cotr_admin_tradeprint">';
			echo '<h3>Tradeprint Order</h3>';

			if($cotp_tradeprint_order_response['success'] && $cotp_tradeprint_order_created == 'yes'){
				$tradeprint_api = new Tradeprint_Api($this->plugin_name, $this->version);
				
				echo '<div class="cotr-admin-success"><b>Order Date:</b> '.$cotp_tradeprint_order_response['result']['order']['dateCreated'].'</div>';
				
				$tradeprint_order_status = $tradeprint_api->get_order_status( 'cotp_wc_'.$order->get_id() );
				if($tradeprint_order_status){
					echo '<div class="cotr-admin-success"><b>Order Status:</b> '.$tradeprint_order_status['status'].'</div>';
				
				}
				if(isset($cotp_tradeprint_order_response['result']['order']['tpOrderDetails']) && !empty($cotp_tradeprint_order_response['result']['order']['tpOrderDetails'])){

					echo '<div class="cotr-admin-success"><b>Order Number:</b> '.($cotp_tradeprint_order_response['result']['order']['tpOrderDetails']['orderNumber']??'').'</div>';
				}
				
			}
			else{
				echo '<div class="cotr-admin-error">'.$cotp_tradeprint_order_response['errorMessage'].'</div>';
			}

			echo '</div>';
		}
		
	}
}
