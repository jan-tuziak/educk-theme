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


add_action( 'woocommerce_after_checkout_form', 'conditionally_hide_show_new_field', 9999 );
function conditionally_hide_show_new_field() {
  wc_enqueue_js( "
    jQuery('input#checkbox_vat_invoice').change(function() {
        if (! this.checked) {
            // HIDE IF NOT CHECKED
            jQuery(`#billing_tax_no_field`).fadeOut();
            jQuery(`#billing_tax_no_field input`).val('');         
            jQuery(`#billing_company_name_field`).fadeOut();
            jQuery(`#billing_company_name_field input`).val('');         
            jQuery(`#billing_state_field`).fadeOut();
            jQuery(`#billing_state_field input`).val('');         
            jQuery(`#billing_address_1_field`).fadeOut();
            jQuery(`#billing_address_1_field input`).val('');         
            jQuery(`#billing_city_field`).fadeOut();
            jQuery(`#billing_city_field input`).val('');             
        } else {
            // SHOW IF CHECKED
            jQuery(`#billing_tax_no_field`).fadeIn();
            jQuery(`#billing_company_name_field`).fadeIn();
            jQuery(`#billing_state_field`).fadeIn();
            jQuery(`#billing_address_1_field`).fadeIn();
            jQuery(`#billing_city_field`).fadeIn();
        }
    }).change();
  "); 
}


add_action( 'woocommerce_checkout_process', 'validate_new_checkout_field' );
function validate_new_checkout_field() {    
    if ( $_POST['checkbox_vat_invoice'] ) {
        if ( empty( $_POST['billing_tax_no'] ) ) {
            wc_add_notice( EDUCK_TAX_NO_ERROR, 'error' );
        }

        if ( empty( $_POST['billing_company_name'] ) ) {
            wc_add_notice( EDUCK_COMPANY_NAME_ERROR, 'error' );
        }

        if ( empty( $_POST['billing_address_1'] ) ) {
            wc_add_notice( EDUCK_COMPANY_ADDRESS_ERROR, 'error' );
        }

        if ( empty( $_POST['billing_city'] ) ) {
            wc_add_notice( EDUCK_COMPANY_CITY_ERROR, 'error' );
        }
    }
}