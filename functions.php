<?php
$TLD = end(explode(".", parse_url('http://' . $_SERVER['SERVER_NAME'], PHP_URL_HOST))); // 'org' or 'pl'
$include_file = ($TLD === 'pl') ? 'include/pl.php' : 'include/org.php';
include_once get_theme_file_path($include_file);
include_once get_theme_file_path('include/wp-login-modified.php');
include_once get_theme_file_path('include/woo.php');
include_once get_theme_file_path('include/elementor-form-turnstile-handler.php');
include_once get_theme_file_path('include/educk-force-coupons.php');

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles() {
	//Skipping parent style, because it was messing up headings on Blog posts
	//wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
        //wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
	
        wp_enqueue_style(
			'child-style', 
			get_stylesheet_directory_uri() . '/style.css', 
			array(),
			wp_get_theme()->get( 'Version' ), //proper style versioning for production
			//filemtime(get_stylesheet_directory() . "/style.css" ), //quick style versioning for development (omits caching)
			'all');
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
 * send events to dataLayer when forms are submitted
 **/
function educk_add_fbq_form_events_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      jQuery(document).on('submit_success', function(e) {
        const formId = e.target.id;
	window.dataLayer = window.dataLayer || [];
        if (formId === 'new_subscriber') {
          window.dataLayer.push({'event': 'new_subscriber'});
        } else {
	  window.dataLayer.push({'event': 'form_submit_successful'});
        }
      });
    });
    </script>
    <?php
}
add_action('wp_footer', 'educk_add_fbq_form_events_script');

/**
 * Increase time limit for Action Scheduler
 **/
function eg_increase_time_limit( $time_limit ) {
	return 60;
}
add_filter( 'action_scheduler_queue_runner_time_limit', 'eg_increase_time_limit' );

/**
 * Increase Concurrent Batches for Action Scheduler
 **/
function eg_increase_action_scheduler_concurrent_batches( $concurrent_batches ) {
	return 2;
}
add_filter( 'action_scheduler_queue_runner_concurrent_batches', 'eg_increase_action_scheduler_concurrent_batches' );
