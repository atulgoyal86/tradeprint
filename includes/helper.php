<?php 
function is_tradeprint_product($product_id){
	$cotp_tradeprint = get_post_meta( $product_id, '_cotp_tradeprint', true );
	if(isset($cotp_tradeprint) && $cotp_tradeprint == 'yes'){
		return true;
	}else{
		return false;
	}
}
?>