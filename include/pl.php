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
		// waitForElm("input[id=\"coupon_code\"]").then((elm) => {
		// 	elm.placeholder = "Kod kuponu";
		// });
		
		// waitForElm(".e-woocommerce-login-nudge").then((elm) => {
		// 	elm.textContent  = "Jeżeli już robiłeś/-aś u nas zakupy, to proszę zaloguj się.";
		// });
		
		// waitForElm("label[for=\"password\"]").then((elm) => {
		// 	elm.firstChild.textContent = "Hasło ";
		// });
		
		// waitForElm("button.e-woocommerce-form-login-submit").then((elm) => {
		// 	elm.textContent = "Zaloguj się";
		// });
		
		// waitForElm("span.elementor-woocomemrce-login-rememberme").then((elm) => {
		// 	elm.textContent = "Zapamiętaj mnie";
		// });
		
		// waitForElm("p.lost_password > a").then((elm) => {
		// 	elm.textContent = "Zapomniałeś/-aś hasła?";
		// });
		
		// waitForElm("label.e-coupon-anchor-description").then((elm) => {
		// 	elm.textContent = "Jeżeli masz kod kuponu, wpisz go poniżej.";
		// });
		
		// waitForElm("button.e-apply-coupon").then((elm) => {
		// 	elm.textContent = "Wykorzystaj kupon";
		// });

		function translateAll() {

			    // Coupon
			    const coupon = document.querySelector("p.e-woocommerce-coupon-nudge");
			    if (coupon && !coupon.dataset.translated) {
			        coupon.innerHTML = 'Masz kupon? <a href="#" class="e-show-coupon-form">Kliknij tutaj, aby wpisać kod kuponu</a>';
			        coupon.dataset.translated = "true";
			    }
			
			    // Coupon input
			    const couponInput = document.querySelector("input#coupon_code");
			    if (couponInput && !couponInput.dataset.translated) {
			        couponInput.placeholder = "Kod kuponu";
			        couponInput.dataset.translated = "true";
			    }
			
			    // Login nudge
			    const loginNudge = document.querySelector(".e-woocommerce-login-nudge");
			    if (loginNudge && !loginNudge.dataset.translated) {
			        loginNudge.textContent = "Jeżeli już robiłeś/-aś u nas zakupy, to proszę zaloguj się.";
			        loginNudge.dataset.translated = "true";
			    }
			
			    // Password label
			    const passwordLabel = document.querySelector("label[for='password']");
			    if (passwordLabel && !passwordLabel.dataset.translated) {
			        passwordLabel.firstChild.textContent = "Hasło ";
			        passwordLabel.dataset.translated = "true";
			    }
			
			    // Login button
			    const loginBtn = document.querySelector("button.e-woocommerce-form-login-submit");
			    if (loginBtn && !loginBtn.dataset.translated) {
			        loginBtn.textContent = "Zaloguj się";
			        loginBtn.dataset.translated = "true";
			    }
			
			    // Remember me
			    const remember = document.querySelector("span.elementor-woocomemrce-login-rememberme");
			    if (remember && !remember.dataset.translated) {
			        remember.textContent = "Zapamiętaj mnie";
			        remember.dataset.translated = "true";
			    }
			
			    // Lost password
			    const lost = document.querySelector("p.lost_password > a");
			    if (lost && !lost.dataset.translated) {
			        lost.textContent = "Zapomniałeś/-aś hasła?";
			        lost.dataset.translated = "true";
			    }
			
			    // Coupon description
			    const couponDesc = document.querySelector("label.e-coupon-anchor-description");
			    if (couponDesc && !couponDesc.dataset.translated) {
			        couponDesc.textContent = "Jeżeli masz kod kuponu, wpisz go poniżej.";
			        couponDesc.dataset.translated = "true";
			    }
			
			    // Apply coupon button
			    const applyBtn = document.querySelector("button.e-apply-coupon");
			    if (applyBtn && !applyBtn.dataset.translated) {
			        applyBtn.textContent = "Wykorzystaj kupon";
			        applyBtn.dataset.translated = "true";
			    }
			}
			
			
			// 🚀 pierwsze odpalenie
			translateAll();
			
			// 👀 jeden observer dla wszystkiego
			const observer = new MutationObserver(() => {
			    translateAll();
			});
			
			observer.observe(document.body, {
			    childList: true,
			    subtree: true
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
