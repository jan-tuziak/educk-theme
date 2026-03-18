<?php
define('EDUCK_READ_MORE', "Zobacz więcej");
define('EDUCK_ADD_TO_CART', "Dodaj do koszyka");
define('EDUCK_NET', "netto");
define('EDUCK_TAX_NO_ERROR', "Podaj proszę NIP swojej firmy");
define('EDUCK_COMPANY_NAME_ERROR', "Podaj proszę nazwę swojej firmy");
define('EDUCK_COMPANY_ADDRESS_ERROR', "Podaj proszę adres swojej firmy");
define('EDUCK_COMPANY_CITY_ERROR', "Podaj proszę miasto swojej firmy");

/**
 * Enqueue Helper Function
 **/
add_action( 'wp_enqueue_scripts', 'enqueue_helper_functions' );
function enqueue_helper_functions(){
	wp_register_script( 'enqueue-helper-functions', '', [], '', true );
	wp_enqueue_script( 'enqueue-helper-functions'  );
	wp_add_inline_script( 'enqueue-helper-functions', '
		function waitForElm(selector) {
			return new Promise(resolve => {
				if (document.querySelector(selector)) {
					return resolve(document.querySelector(selector));
				}

				const observer = new MutationObserver(mutations => {
					if (document.querySelector(selector)) {
						observer.disconnect();
						resolve(document.querySelector(selector));
					}
				});

				// If you get "parameter 1 is not of type Node" error, see https://stackoverflow.com/a/77855838/492336
				observer.observe(document.body, {
					childList: true,
					subtree: true
				});
			});
		}	
		
		' );
}

/**
 * Translate Empty Cart Message to Polish
 **/
add_action( 'wp_enqueue_scripts', 'translate_empty_cart_message' );
function translate_empty_cart_message(){
	wp_register_script( 'translate-emppty-cart-message', '', [], '', true );
	wp_enqueue_script( 'translate-emppty-cart-message'  );
	wp_add_inline_script( 'translate-emppty-cart-message', 
		'
		waitForElm(".woocommerce-mini-cart__empty-message").then((elm) => {
			elm.textContent  = "Brak produktów w koszyku 😢";
		});
		' );
}

/**
 * Enqueue Translation for LearnDash's Registration Username field
 **/
add_action( 'wp_enqueue_scripts', 'translate_registration_username' );
function translate_registration_username(){
	wp_register_script( 'translate-registration-username', '', [], '', true );
	wp_enqueue_script( 'translate-registration-username'  );
	wp_add_inline_script( 'translate-registration-username', 
		'
		waitForElm("label[for=\"user_reg_login\"]").then((elm) => {
		  elm.firstChild.textContent = "Nazwa użytkownika";
		});

		' );
}

/**
 * Enqueue Translation for WooCommerce fields
 **/
add_action( 'wp_enqueue_scripts', 'translate_woo_fields' );
function translate_woo_fields(){
	wp_register_script( 'translate-woo-fields', '', [], '', true );
	wp_enqueue_script( 'translate-woo-fields'  );
	wp_add_inline_script( 'translate-woo-fields', '
		waitForElm("input[id=\"coupon_code\"]").then((elm) => {
			elm.placeholder = "Kod kuponu";
		});
		
		waitForElm(".e-woocommerce-login-nudge").then((elm) => {
			elm.textContent  = "Jeżeli już robiłeś/-aś u nas zakupy, to proszę zaloguj się.";
		});
		
		waitForElm("label[for=\"password\"]").then((elm) => {
			elm.firstChild.textContent = "Hasło ";
		});
		
		waitForElm("button.e-woocommerce-form-login-submit").then((elm) => {
			elm.textContent = "Zaloguj się";
		});
		
		waitForElm("span.elementor-woocomemrce-login-rememberme").then((elm) => {
			elm.textContent = "Zapamiętaj mnie";
		});
		
		waitForElm("p.lost_password > a").then((elm) => {
			elm.textContent = "Zapomniałeś/-aś hasła?";
		});
		
		waitForElm("label.e-coupon-anchor-description").then((elm) => {
			elm.textContent = "Jeżeli masz kod kuponu, wpisz go poniżej.";
		});
		
		waitForElm("button.e-apply-coupon").then((elm) => {
			elm.textContent = "Wykorzystaj kupon";
		});
		
		' );
}

/*
 * Show "Webinaria Live" text in Elementor page (using shortcodes) if product category is 'webinaria' or 'zimowe-webinary' 
 */
function webinaria_live_text( $atts ) {
    if (has_term( array('webinaria','zimowe-webinary'), 'product_cat')){
      echo '<span style="color:#E63831;text-align:center;font-weight:bold;font-size:18px;">Webinaria Live</span>';
	}
}
add_shortcode( 'my_webinaria_live_text', 'webinaria_live_text');

// Hardcoded translations for WooCommerce checkout page
add_filter('gettext', function($translated, $text, $domain) {

    if ($domain === 'woocommerce') {

        if ($text === 'Have a coupon?') {
            return 'Masz kupon?';
        }

        if ($text === 'Click here to enter your coupon code') {
            return 'Kliknij tutaj, aby wpisać kod kuponu';
        }

    }

    return $translated;

}, 20, 3);
