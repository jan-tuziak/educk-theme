<?php

/**
 * Remove language dropdown from wp-login.php
 **/
add_filter( 'login_display_language_dropdown', '__return_false' );

/**
 * Change URL under Logo on wp-login.php
 **/
add_filter( 'login_headerurl', 'my_custom_login_url' );
function my_custom_login_url($url) {
    return '/';
}

/**
 * Disable the logout confirmation when hitting the `/wp-login.php?action=logout` url
 **/
add_action('check_admin_referer', 'logout_without_confirm', 10, 2);
function logout_without_confirm($action, $result)
{
    /**
     * Allow logout without confirmation
     */
    if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '/';
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
        header("Location: $location");
        die;
    }
}

/**
 * Redirect to home page after user logs in
 **/
add_filter( 'login_redirect', 'custom_login_redirect', 10, 3 );
function custom_login_redirect( $redirect_to, $request, $user ) { 
    return '/';
}

/**
 * Style Login Page
 **/
function custom_login_css() {
	echo 
	"<style type=\"text/css\">
		@import url('https://fonts.googleapis.com/css?family=Archivo&display=swap');
		
		.login h1 a {
			background-image: url(" . echo $config['wp-login']['logo_url'] . ");
			background-size: contain;
			background-position: center;
			background-repeat: no-repeat;
			margin: 0;
			width: 100%;
			
		}
		
		body.login {
			background: #F3CB4A;
			background-position-x: right;
			background-position-y: top;
			background-repeat: no-repeat;
			background-image: url(" . echo $config['wp-login']['background_image_url'] . ");
			font-family: 'Archivo';
		}

		/* body.login #loginform, body.login #registerform, body.login #resetpassform */ body.login form {
			border: 1px solid #D7D7D7;
			border-radius: 40px;
		}
		
		body.login #wp-submit {
			background: #FFC818;
			border: 1.3px solid #B58A01;
			border-radius: 50px;
			color: #3A3E40;
			
			font-style: normal;
			font-weight: 500;
			font-size: 18px;
			line-height: 138%;
			padding: 5px 12px;
		}

		body.login #wp-submit:hover {
			background: #FACF45;
		}

		body.login .wp-generate-pw {
			background: #F1F1F1;
			border: 1.3px solid #B58A01;
			border-radius: 50px;
			color: #3A3E40;
			
			font-style: normal;
			font-weight: 500;
			font-size: 14px;
			line-height: 138%;
			padding: 5px 12px;
		}

		body.login .wp-generate-pw:hover {
			background: #F1F1F1;
			border: 1.3px solid #B58A01;
			color: #3A3E40;
		}
	</style>";
}
add_action('login_head', 'custom_login_css');