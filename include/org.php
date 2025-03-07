<?php
define('EDUCK_READ_MORE', "Read More");
define('EDUCK_ADD_TO_CART', "Add to Cart");
define('EDUCK_NET', "net");

// /*
//  *  Change Main Woocommerce Button for Courses to "Read More"
//  */
// add_filter( 'woocommerce_loop_add_to_cart_link', 'replace_loop_add_to_cart_button', 10, 2 );
// function replace_loop_add_to_cart_button( $button, $product  ) {
//     // Course products
//     if( $product->is_type( 'course' ) ) {
//         $button_text = __( "Read More", "woocommerce" );
// 		return '<a class="view-product button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
//     } 
//     // Other product types
//     else {
// 		//$button_text = add_to_cart_text(); <- does not work for some reason. Investigate later 
// 		$button_text = __( "Add to Cart", "woocommerce" );
// 		return '<a class="view-product button" href="?add-to-cart=' . $product->get_id() . '">' . $button_text . '</a>';
//     }
// }

// /**
//  * @snippet       Prices Incl + Excl Tax | WooCommerce Shop
//  * @how-to        businessbloomer.com/woocommerce-customization
//  * @author        Rodolfo Melogli, Business Bloomer
//  * @compatible    WooCommerce 7
//  * @community     https://businessbloomer.com/club/
//  */
// add_filter( 'woocommerce_get_price_suffix', 'bbloomer_add_price_suffix_price_inc_tax', 99, 4 );
// function bbloomer_add_price_suffix_price_inc_tax( $suffix, $product, $price, $qty ){
//     $suffix = '<small> ' . wc_price( wc_get_price_excluding_tax( $product ) ) . ' net</small>';
//     return $suffix;
// }
