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
 */
add_filter( 'woocommerce_get_price_suffix', 'add_price_suffix_price_inc_tax', 99, 4 );
function add_price_suffix_price_inc_tax( $suffix, $product, $price, $qty ){
    $suffix = '<small> ' . wc_price( wc_get_price_excluding_tax( $product ) ) . ' ' . EDUCK_NET .'</small>';
    return $suffix;
}

// check out these resources:
// https://www.businessbloomer.com/woocommerce-add-custom-checkout-field-php/
// https://www.businessbloomer.com/woocommerce-hide-checkout-billing-fields-if-virtual-product-cart/

// remember that you can modify checkout fields visibility in theme's modify window.
// and from Elementor you can also modify checkout fields visibility.

/**
 * @snippet       Add a Checkbox to Hide/Show Checkout Field - WooCommerce
 */
add_filter( 'woocommerce_checkout_fields' , 'display_checkbox_and_new_checkout_field' );
function display_checkbox_and_new_checkout_field( $fields ) {
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    
    // remove postcode validation
    unset($fields['billing']['billing_postcode']['validate']);
    unset($fields['shipping']['shipping_postcode']['validate']);

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
    $fields['billing']['billing_address_1']['priority'] = 8;
    $fields['billing']['billing_postcode']['priority'] = 9;
    $fields['billing']['billing_city']['priority'] = 10;
    
    $fields['billing']['billing_company']['required'] = false;
    $fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_postcode']['required'] = false;
    $fields['billing']['billing_city']['required'] = false;

	return $fields;
}

add_filter( 'woocommerce_default_address_fields', 'customising_checkout_fields' );
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
  
add_action( 'woocommerce_after_checkout_form', 'conditionally_hide_show_new_field', 9999 );
function conditionally_hide_show_new_field() {
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


add_action( 'woocommerce_checkout_process', 'validate_new_checkout_field' );
function validate_new_checkout_field() {    
    if ( $_POST['checkbox_vat_invoice'] ) {
        if ( empty( $_POST['billing_tax_no'] ) ) {
            wc_add_notice( 'Podaj proszę NIP swojej firmy', 'error' );
        }
        
        if ( empty( $_POST['billing_company'] ) ) {
            wc_add_notice( 'Podaj proszę nazwę swojej firmy', 'error' );
        }

        if ( empty( $_POST['billing_address_1'] ) ) {
            wc_add_notice( 'Podaj proszę adres swojej firmy', 'error' );
        }

        if ( empty( $_POST['billing_city'] ) ) {
            wc_add_notice( 'Podaj proszę miasto swojej firmy', 'error' );
        }

        if ( empty( $_POST['billing_postcode'] ) ) {
            wc_add_notice( 'Podaj proszę kod pocztowy swojej firmy', 'error' );
        }
    }
}

add_action( 'woocommerce_checkout_update_order_meta', 'save_new_checkout_field' );
function save_new_checkout_field( $order_id ) { 
    if (!empty($_POST['billing_tax_no'])) {
        update_post_meta($order_id, 'billing_tax_no', sanitize_text_field($_POST['billing_tax_no']));
    }

    if (!empty($_POST['billing_company'])) {
        update_post_meta($order_id, 'billing_company_name', sanitize_text_field($_POST['billing_company']));
    }
}
 
add_action( 'woocommerce_thankyou', 'show_new_checkout_field_thankyou' );
function show_new_checkout_field_thankyou( $order_id ) {    
    if ( get_post_meta( $order_id, '_billing_tax_no', true ) ) echo '<p><strong>NIP:</strong> ' . get_post_meta( $order_id, '_billing_tax_no', true ) . '</p>';
}
  
add_action( 'woocommerce_admin_order_data_after_billing_address', 'show_new_checkout_field_order' );
function show_new_checkout_field_order( $order ) {    
    $order_id = $order->get_id();
    if ( get_post_meta( $order_id, '_billing_tax_no', true ) ) echo '<p><strong>NIP:</strong> ' . get_post_meta( $order_id, '_billing_tax_no', true ) . '</p>';
}
 
add_action( 'woocommerce_email_after_order_table', 'show_new_checkout_field_emails', 20, 4 );
function show_new_checkout_field_emails( $order, $sent_to_admin, $plain_text, $email ) {
    if ( get_post_meta( $order->get_id(), '_billing_tax_no', true ) ) echo '<p><strong>NIP:</strong> ' . get_post_meta( $order->get_id(), '_billing_tax_no', true ) . '</p>';
}

// add_action('woocommerce_process_shop_order_meta', 'educk_save_nip_admin_edit');
// function educk_save_nip_admin_edit($order_id){
//     if (isset($_POST['billing_tax_no'])) {
//         update_post_meta($order_id, 'billing_tax_no', sanitize_text_field($_POST['billing_tax_no']));
//     }
// }