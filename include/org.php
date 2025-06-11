<?php
define('EDUCK_READ_MORE', "Read More");
define('EDUCK_ADD_TO_CART', "Add to Cart");
define('EDUCK_NET', "excl. VAT");

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
