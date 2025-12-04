<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load theme files from /inc
 */
require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/assets.php';

// Optional, will stay empty for now but good to have ready.
if ( file_exists( get_template_directory() . '/inc/woo.php' ) ) {
    require get_template_directory() . '/inc/woo.php';
}
if ( file_exists( get_template_directory() . '/inc/helpers.php' ) ) {
    require get_template_directory() . '/inc/helpers.php';
}
