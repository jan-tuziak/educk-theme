<?php

/**
 * Quantity Discount for "Zimowe webinary 2023"
 */
add_action('woocommerce_cart_calculate_fees', 'ts_add_custom_discount', 10, 1 );
function ts_add_custom_discount( $wc_cart ){
	$discount = 0;
    $product_ids = array();
	$in_cart = false;

	foreach ( $wc_cart->get_cart() as $cart_item_key => $cart_item ) {
		$cart_product = $cart_item['data'];
		if ( has_term( 'zimowe-webinary-2023', 'product_cat', $cart_product->get_id() ) ) {
			$in_cart = true;
			$product_ids[] = $cart_product->get_id();
		}
	}

	if( $in_cart ) {
		$count_ids = count($product_ids);

		if( $count_ids >= 1 ) { 
		   foreach( $product_ids as $id ) {
				$product = wc_get_product( $id );
				$price = $product->get_price();
			   	$percent = 0;
			   switch($count_ids){
				   case 1:
					   $percent = 0;
					   break;
				   case 2:
					   $percent = 15;
					   break;
					case 3:
					   $percent = 20;
					   break;
				   default:
					   $percent = 25;
					   break;
			   }
				$discount -= ($price * $percent) /100; //apply a discount on all "zimowe-webinary-2023" products
		   }
	   }

	} 

    if( $discount != 0 ){
        $wc_cart->add_fee( 'Discount', $discount, true  );
        # Note: Last argument in add_fee() method is related to applying the tax or not to the discount (true or false)
    }
}

/**
 * 10% Discount if at leasat two courses are in a basket
 */
add_action('woocommerce_cart_calculate_fees', 'black_week_10_percent_discount', 10, 1 );
function black_week_10_percent_discount( $wc_cart ){
	$discount = 0;
	$percent = 8.13; // 8.13% net is 10% gross
	$count = count( WC()->cart->get_cart() );
		
	if( $count > 1 ) {
		foreach($wc_cart->get_cart() as $cart_item_key => $cart_item) {
			$id = $cart_item['data']->get_id();
			$product = wc_get_product( $id );
			$price = $product->get_price();
			$discount -= ($price * $percent) /100;
		}
	} 

    if( $discount != 0 ){
        $wc_cart->add_fee( 'Black Week', $discount, true  );
        # Note: Last argument in add_fee() method is related to applying the tax or not to the discount (true or false)
    }
}

/**
 * Change Text for Sale Badge to "Black Week"
 **/
add_filter('woocommerce_sale_flash', 'ds_change_sale_text'); function ds_change_sale_text() { return '<span class="onsale">Promocja</span>'; }
