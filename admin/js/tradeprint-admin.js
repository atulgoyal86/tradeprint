(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).on('click', '#cotp-get-available-attr-btn', function(){
		var product_name = $('#cotp_product_name').val();
		cotp_get_product_prices_ajax(product_name);
	});

	$(document).ready(function(){
		$('.tradeprint_admin_options').select2();
	});

	function cotp_get_product_prices_ajax(product_name){
		$('.cotp-available-attr-selection').html('');

		if(product_name != ''){
			$('#cotp_tradeprint_options_tab').css('opacity', '.5');
			$.ajax({
				url: ajaxurl,
				data: {action:'tradeprint_product_admin_ajax',product_name:product_name},
				type: 'post',
				success: function(response){
					
					if(response.success == true && response.available_attributes){
						console.log(response);
						$.each(response.available_attributes, function( attribute_name, attribute_value ) {
							
							$('.cotp-available-attr-selection').append('<div class="cotp_admin_field"><label>'+attribute_name+'</label><select multiple class="tradeprint_admin_options" name="admin_cotp_attr['+attribute_name+'][]"><option value="">Select</option></select></div>');
							
							$.each(attribute_value, function( index, value ) {
								var option_ = document.createElement("option");
								option_.value = value;
								option_.innerHTML = value;
								$('select.tradeprint_admin_options[name="admin_cotp_attr['+attribute_name+'][]"]').append(option_);
								
							});

						});
						
					}
					else{
						$('.cotp-available-attr-selection').html('');
						
					}

					$('.tradeprint_admin_options').select2();

					$('#cotp_tradeprint_options_tab').css('opacity', '1');
				}
			});
		}
		else{
			$('.cotp-available-attr-selection').html('');
		}
		
	}

	
})( jQuery );
