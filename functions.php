<?php
include_once get_theme_file_path('config.php'); // loads $config and $TLD variables. Must be first!
include_once get_theme_file_path('include/wp-login-modified.php');
if ($TLD === 'pl'){
	include_once get_theme_file_path('include/polish-text-and-translations.php');
}

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles');
function enqueue_parent_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

/*
 *  Change Main Woocommerce Button for Courses to "Read More"
 */
add_filter( 'woocommerce_loop_add_to_cart_link', 'replace_loop_add_to_cart_button', 10, 2 );
function replace_loop_add_to_cart_button( $button, $product  ) {
    // Course products
    if( $product->is_type( 'course' ) ) {
        $button_text = __( $config['woo']['read_more'], "woocommerce" );
		return '<a class="view-product button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
    } 
    // Other product types
    else {
		//$button_text = add_to_cart_text(); <- does not work for some reason. Investigate later 
		$button_text = __( $config['woo']['add_to_cart'], "woocommerce" );
		return '<a class="view-product button" href="?add-to-cart=' . $product->get_id() . '">' . $button_text . '</a>';
    }
}

/**
 * Add MailerLite JavaScript code
 * to add MailerLite forms as JavaScript snippets
 **/
function add_mailerlite_js_code(){
	wp_register_script( 'add_mailerlite_js_code', '', [], '', true );
	wp_enqueue_script( 'add_mailerlite_js_code'  );
	wp_add_inline_script( 'add_mailerlite_js_code', '
			<!-- MailerLite Universal -->
			<script>
			(function(m,a,i,l,e,r){ m["MailerLiteObject"]=e;function f(){
			var c={ a:arguments,q:[]};var r=this.push(c);return "number"!=typeof r?r:f.bind(c.q);}
			f.q=f.q||[];m[e]=m[e]||f.bind(f.q);m[e].q=m[e].q||f.q;r=a.createElement(i);
			var _=a.getElementsByTagName(i)[0];r.async=1;r.src=l+"?v"+(~~(new Date().getTime()/1000000));
			_.parentNode.insertBefore(r,_);})(window, document, "script", "https://static.mailerlite.com/js/universal.js", "ml");

			var ml_account = ml("accounts", "3008326", "m2s0x0f0v7", "load");
			</script>
			<!-- End MailerLite Universal -->
		' );
}
add_action( 'wp_enqueue_scripts', 'add_mailerlite_js_code' );

/**
 * Enqueue LearnDash Focus Mode styles
 **/
function enqueue_learndash_styles(){
	wp_register_style( 'learndash-focus-styles', false );
	wp_enqueue_style( 'learndash-focus-styles' );
	wp_add_inline_style( 'learndash-focus-styles', '
		/* Lesson Title*/
		div.ld-focus-content > h1 {
			font-size: 1.6em;
		}
		
		/* Mark as complete button (at the top and the bottom) */
		input.learndash_mark_complete_button {
			font-size: 0.8em !important;
		}
		
		/* Registration message */
		div.ld-login-modal-register  div.ld-alert-messages {
			color: #fff;
		}
	' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_learndash_styles' );

/**
 * Enqueue Script for Allowing All Consent for Google Analytics
 **/
add_action( 'wp_enqueue_scripts', 'allow_all_consent' );
function allow_all_consent(){
	wp_register_script( 'allow-all-consent', '', [], '', true );
	wp_enqueue_script( 'allow-all-consent'  );
	wp_add_inline_script( 'allow-all-consent', 
		'
		gtag("consent", "update", {
			"ad_user_data": "granted",
			"ad_personalization": "granted",
			"ad_storage": "granted",
			"analytics_storage": "granted"
		  });
		' );
}

/**
 * Redirect to checkout after adding to cart
 **/
add_filter ('woocommerce_add_to_cart_redirect', function( $url, $adding_to_cart ) {
    return wc_get_checkout_url();
}, 10, 2 ); 

/**
 * @snippet       Prices Incl + Excl Tax | WooCommerce Shop
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 7
 * @community     https://businessbloomer.com/club/
 */
add_filter( 'woocommerce_get_price_suffix', 'bbloomer_add_price_suffix_price_inc_tax', 99, 4 );
function bbloomer_add_price_suffix_price_inc_tax( $suffix, $product, $price, $qty ){
    $suffix = '<br><small>' . wc_price( wc_get_price_excluding_tax( $product ) ) . ' ' . $config['woo']['netto'] . '</small>';
    return $suffix;
}

/**
 * @snippet       Remove Tax if Field Value - WooCommerce Checkout
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer, BusinessBloomer.com
 * @testedwith    WooCommerce 8
 * @community     https://businessbloomer.com/club/
 */
add_action( 'woocommerce_checkout_update_order_review', 'bbloomer_taxexempt_checkout_based_on_country' );
function bbloomer_taxexempt_checkout_based_on_country( $post_data ) {
        WC()->customer->set_is_vat_exempt( false );
        parse_str( $post_data, $output );
        if ( $output['billing_country'] !== 'PL' && $output['billing_tax_no'] !== '' && $output['billing_company_name'] !== '' ){
			WC()->customer->set_is_vat_exempt( true );
		} 
}

/**
 * @snippet       Refresh Checkout Upon Input Field Change
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 7
 * @community     https://businessbloomer.com/club/
 */
add_filter( 'woocommerce_checkout_fields', 'bbloomer_checkout_fields_trigger_refresh', 9999 );
function bbloomer_checkout_fields_trigger_refresh( $fields ) {
   $fields['billing']['billing_company_name']['class'][] = 'update_totals_on_change';
   $fields['billing']['billing_tax_no']['class'][] = 'update_totals_on_change';  
   return $fields;
}

/**
 * Dynamically add CSS classes if user is logged in or out
 **/
add_action('wp_head', 'dyanmicCss');
function dyanmicCss() {
    if (is_user_logged_in()) {
        echo '<style> .hide-when-logged-in { display: none !important; }</style>';
    } else {
        echo '<style> .hide-when-logged-out, #my-account-menu-link { display: none !important; }</style>';
    }
}

/**
 * @snippet       Remove "Payments" Tab | WordPress Dashboard
 * @tutorial      Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 9
 * @community     https://businessbloomer.com/club/
 */
 add_action( 'admin_menu', 'bbloomer_remove_payments_from_wp_sidebar_menu', 9999 );
 function bbloomer_remove_payments_from_wp_sidebar_menu() {   
	remove_menu_page( 'admin.php?page=wc-settings&tab=checkout' );
	remove_menu_page( 'admin.php?page=wc-admin&path=/wc-pay-welcome-page' ); 
	remove_menu_page( 'admin.php?page=wc-admin&task=payments' ); 
	remove_menu_page( 'admin.php?page=wc-admin&task=woocommerce-payments' ); 
 }