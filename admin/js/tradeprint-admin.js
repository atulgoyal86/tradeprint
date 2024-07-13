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
	
	$(document).on('click', 'button.cotp_create_order_button', function(event){
		var this_order_id = $(this).attr('data-orderid');
		var a_mainhref = $('.cotp_create_order_button_'+this_order_id).attr('data-mainhref');
		$('.cotp_ship_to_store_popup').remove();
		
		$('body').append('<div class="cotp_ship_to_store_popup"><div class="cotp_ship_to_store_inner"><span class="cotp_ship_to_store_close"><span class="dashicons dashicons-no-alt"></span></span><label><input type="checkbox" data-orderid="'+this_order_id+'" class="cotp_shipto_shop_address_check cotp_shipto_shop_address_check_'+this_order_id+'" value="1"> Ship to store</label><br><a data-orderid="'+this_order_id+'" class="button button-primary cotp_create_order_btn cotp_create_order_'+this_order_id+'" data-mainhref="'+a_mainhref+'" href="'+a_mainhref+'">Create</a></div></div>');
		/*if($(this).prop('checked')){
			$('.cotp_create_order_'+this_order_id).attr('href', a_mainhref+'&cotp_ship_to_store=true');
		}
		else{
			$('.cotp_create_order_'+this_order_id).attr('href', a_mainhref);
		}*/
		
	});
	
	$(document).on('click','.cotp_ship_to_store_close', function(){
		$('.cotp_ship_to_store_popup').remove();
	});
	
	$(document).on('change', '.cotp_shipto_shop_address_check', function(event){
		var this_order_id = $(this).attr('data-orderid');
		var a_mainhref = $('.cotp_create_order_'+this_order_id).attr('data-mainhref');
		
		
		if($(this).prop('checked')){
			$('.cotp_create_order_'+this_order_id).attr('href', a_mainhref+'&cotp_ship_to_store=true');
		}
		else{
			$('.cotp_create_order_'+this_order_id).attr('href', a_mainhref);
		}
		
	});
	
	// ---------------------------------------------------------------------------
	// on upload button click
	$( document ).on( 'click', '.cotp-upload', function( event ){
		event.preventDefault(); // prevent default link click and page refresh
		
		const button = $(this)
		const imageId = button.next().next().val();
		
		const customUploader = wp.media({
			title: 'Insert File', // modal window title
			library : {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				//type : 'image'
			},
			button: {
				text: 'Use this File' // button label text
			},
			multiple: false
		}).on( 'select', function() { // it also has "open" and "close" events
			const attachment = customUploader.state().get( 'selection' ).first().toJSON();
			button.removeClass( 'cotp-upload' ).attr( 'href',attachment.url); // add image instead of "Upload Image"
			button.text('View File');
			button.next().show(); // show "Remove image" link
			button.next().next().val( attachment.id ); // Populate the hidden field with image ID
		})
		
		// already selected images
		customUploader.on( 'open', function() {

			if( imageId ) {
			  const selection = customUploader.state().get( 'selection' )
			  attachment = wp.media.attachment( imageId );
			  attachment.fetch();
			  selection.add( attachment ? [attachment] : [] );
			}
			
		})

		customUploader.open()
	
	});
	// on remove button click
	$( document).on( 'click', '.cotp-remove', function( event ){
		event.preventDefault();
		console.log('dhshgdjs');
		const button = $(this);
		button.next().val( '' ); // emptying the hidden field
		button.hide().prev().addClass( 'button' ).html( 'Upload File' ); // replace the image with text
		button.hide().prev().addClass( 'cotp-upload' );
	});
	// -------------------------------------------------

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
