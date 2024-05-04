<?php 
function is_tradeprint_product($product_id){
	$cotp_tradeprint = get_post_meta( $product_id, '_cotp_tradeprint', true );
	if(isset($cotp_tradeprint) && $cotp_tradeprint == 'yes'){
		return true;
	}else{
		return false;
	}
}

function create_attachment($data_file){
	$file_data = $data_file;
	$wp_upload_dir = wp_upload_dir();
	$trimed_filename = str_replace(" ", "_", $file_data['name']);
	$unique_file_name = wp_unique_filename( $wp_upload_dir['path'], $trimed_filename );
	move_uploaded_file($file_data['tmp_name'], $wp_upload_dir['path'].'/'. $unique_file_name);
	$image_url = $wp_upload_dir['path'].'/'.$unique_file_name;
	$image_data = file_get_contents( $image_url );
	$imagename = basename( $image_url );
	if ( wp_mkdir_p( $wp_upload_dir['path'] ) ) {
	$image = $wp_upload_dir['path'] . '/' . $imagename;
	}
	else {
	$image = $wp_upload_dir['basedir'] . '/' . $imagename;
	}
	file_put_contents( $image, $image_data );
	$wp_filetype = wp_check_filetype( $imagename, null );
	$attachment = array(
	'guid'          => $image_url,
	'post_mime_type' => $wp_filetype['type'],
	'post_title' => sanitize_file_name( $imagename ),
	'post_content' => '',
	'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $image );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $image );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	return $attach_id;
}
?>