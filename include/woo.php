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
//  * @snippet       Hide Fields if Virtual @ WooCommerce Checkout
//  * @how-to        businessbloomer.com/woocommerce-customization
//  * @author        Rodolfo Melogli, Business Bloomer
//  * @compatible    WooCommerce 8
//  * @community     https://businessbloomer.com/club/
//  */
 
// add_filter( 'woocommerce_checkout_fields', 'bbloomer_simplify_checkout_virtual' );
  
// function bbloomer_simplify_checkout_virtual( $fields ) {
//    $only_virtual = true;
//    foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
//       // Check if there are non-virtual products
//       if ( ! $cart_item['data']->is_virtual() ) $only_virtual = false;
//    }
//    if ( $only_virtual ) {
//       unset($fields['billing']['billing_company']);
//       unset($fields['billing']['billing_address_1']);
//       unset($fields['billing']['billing_address_2']);
//       unset($fields['billing']['billing_city']);
//       // unset($fields['billing']['billing_postcode']);
//       // unset($fields['billing']['billing_country']);
//       unset($fields['billing']['billing_state']);
//       unset($fields['billing']['billing_phone']);
//       // add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
//    }
//    return $fields;
// }







// check out these resources:
// https://www.businessbloomer.com/woocommerce-add-custom-checkout-field-php/
// https://www.businessbloomer.com/woocommerce-hide-checkout-billing-fields-if-virtual-product-cart/


// remember that you can modify checkout fields visibility in theme's modify window.



/**
 * @snippet       Add a Checkbox to Hide/Show Checkout Field - WooCommerce
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WC 4.1
 * @community     https://businessbloomer.com/club/
 */
add_filter( 'woocommerce_checkout_fields' , 'bbloomer_display_checkbox_and_new_checkout_field' );
function bbloomer_display_checkbox_and_new_checkout_field( $fields ) {
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    
    $fields['billing']['checkbox_vat_invoice'] = array(
	    'type'      => 'checkbox',
	    'label'     => __('Chcę otrzymać fakturę VAT', 'woocommerce'),
	    'class'     => array('form-row-wide'),
	    'clear'     => true
	);   
	    
	$fields['billing']['billing_tax_no'] = array(
	    'label'     => __('NIP', 'woocommerce'),
	    'placeholder'   => _x('1234567890', 'placeholder', 'woocommerce'),
	    'class'     => array('form-row-wide'),
	    'clear'     => true
	);

    $fields['billing']['billing_email']['priority'] = 1;
    $fields['billing']['billing_first_name']['priority'] = 2;
    $fields['billing']['billing_last_name']['priority'] = 3;
    $fields['billing']['billing_country']['priority'] = 4;
    
    $fields['billing']['checkbox_vat_invoice']['priority'] = 5;
    $fields['billing']['billing_tax_no']['priority'] = 6;
    $fields['billing']['billing_company']['priority'] = 7;
    $fields['billing']['billing_company']['required'] = false;
    $fields['billing']['billing_address_1']['priority'] = 8;
    $fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_postcode']['priority'] = 9;
    $fields['billing']['billing_postcode']['required'] = false;
    $fields['billing']['billing_city']['priority'] = 10;
    $fields['billing']['billing_city']['required'] = false;
	return $fields;
}

add_filter( 'woocommerce_default_address_fields', 'customising_checkout_fields', 1000, 1 );
function customising_checkout_fields( $address_fields ) {
    $address_fields['first_name']['required'] = true;
    $address_fields['last_name']['required'] = true;
    $address_fields['country']['required'] = true;

    $address_fields['company']['required'] = false;
    $address_fields['city']['required'] = false;
    $address_fields['state']['required'] = false;
    $address_fields['postcode']['required'] = false;
    $address_fields['address_1']['required'] = false;
    $address_fields['address_2']['required'] = false;

    return $address_fields;
}

// add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields' );
// function custom_override_default_locale_fields( $fields ) {
//     $fields['address_1']['priority'] = 8;
//     return $fields;
// }
  
add_action( 'woocommerce_after_checkout_form', 'bbloomer_conditionally_hide_show_new_field', 9999 );
function bbloomer_conditionally_hide_show_new_field() {
  wc_enqueue_js( "
    jQuery('input#checkbox_vat_invoice').change(function() {
        if (! this.checked) {
            // HIDE IF NOT CHECKED
            jQuery(`#billing_tax_no_field`).fadeOut();
            jQuery(`#billing_tax_no_field input`).val('');         
            jQuery(`#billing_company_field`).fadeOut();
            jQuery(`#billing_company_field input`).val('');         
            jQuery(`#billing_state_field`).fadeOut();
            jQuery(`#billing_state_field input`).val('');         
            jQuery(`#billing_address_1_field`).fadeOut();
            jQuery(`#billing_address_1_field input`).val('');         
            jQuery(`#billing_city_field`).fadeOut();
            jQuery(`#billing_city_field input`).val('');         
            jQuery(`#billing_postcode_field`).fadeOut();
            jQuery(`#billing_postcode_field input`).val('');         
        } else {
            // SHOW IF CHECKED
            jQuery(`#billing_tax_no_field`).fadeIn();
            jQuery(`#billing_company_field`).fadeIn();
            jQuery(`#billing_state_field`).fadeIn();
            jQuery(`#billing_address_1_field`).fadeIn();
            jQuery(`#billing_city_field`).fadeIn();
            jQuery(`#billing_postcode_field`).fadeIn();
        }
    }).change();
  "); 
}
