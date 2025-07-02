<?php
/*
 *  Change Main Woocommerce Button for Courses to "Read More"
 */
add_filter( 'woocommerce_loop_add_to_cart_link', 'replace_loop_add_to_cart_button', 10, 2 );
function replace_loop_add_to_cart_button( $button, $product  ) {
    // Course products
    if( $product->is_type( 'course' ) ) {
        $button_text = __( EDUCK_READ_MORE, "woocommerce" );
		return '<a class="view-product button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
    } 
    // Other product types
    else {
		//$button_text = add_to_cart_text(); <- does not work for some reason. Investigate later 
		$button_text = __( EDUCK_ADD_TO_CART, "woocommerce" );
		return '<a class="view-product button" href="?add-to-cart=' . $product->get_id() . '">' . $button_text . '</a>';
    }
}

/**
 * @snippet       Prices Incl + Excl Tax | WooCommerce Shop
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 7
 * @community     https://businessbloomer.com/club/
 */
add_filter( 'woocommerce_get_price_suffix', 'bbloomer_add_price_suffix_price_inc_tax', 99, 4 );
function bbloomer_add_price_suffix_price_inc_tax( $suffix, $product, $price, $qty ){
    $suffix = '<small> ' . wc_price( wc_get_price_excluding_tax( $product ) ) . ' ' . EDUCK_NET .'</small>';
    return $suffix;
}


// /**
//  * @snippet       Add a Checkbox to Hide/Show Checkout Field - WooCommerce
//  * @how-to        businessbloomer.com/woocommerce-customization
//  * @author        Rodolfo Melogli, Business Bloomer
//  * @compatible    WC 4.1
//  * @community     https://businessbloomer.com/club/
//  */
// add_filter( 'woocommerce_checkout_fields' , 'bbloomer_display_checkbox_and_new_checkout_field' );
// function bbloomer_display_checkbox_and_new_checkout_field( $fields ) {
// 	$fields['billing']['checkbox_trigger'] = array(
// 	    'type'      => 'checkbox',
// 	    'label'     => __('Checkbox label', 'woocommerce'),
// 	    'class'     => array('form-row-wide'),
// 	    'clear'     => true
// 	);   
	    
// 	$fields['billing']['new_billing_field'] = array(
// 	    'label'     => __('New Billing Field Label', 'woocommerce'),
// 	    'placeholder'   => _x('New Billing Field Placeholder', 'placeholder', 'woocommerce'),
// 	    'class'     => array('form-row-wide'),
// 	    'clear'     => true
// 	);
// 	return $fields;
// }
  
// add_action( 'woocommerce_after_checkout_form', 'bbloomer_conditionally_hide_show_new_field', 9999 );
// function bbloomer_conditionally_hide_show_new_field() {
//   wc_enqueue_js( "
//       jQuery('input#checkbox_trigger').change(function(){
           
//          if (! this.checked) {
//             // HIDE IF NOT CHECKED
//             jQuery('#new_billing_field_field').fadeOut();
//             jQuery('#new_billing_field_field input').val('');         
//          } else {
//             // SHOW IF CHECKED
//             jQuery('#new_billing_field_field').fadeIn();
//          }
           
//       }).change();
//   "); 
// }
