<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue front-end assets.
 */
function educk_theme_assets() {

    // Tailwind compiled CSS.
    $css_path = get_template_directory() . '/dist/styles.css';
    if ( file_exists( $css_path ) ) {
        wp_enqueue_style(
            'educk-styles',
            get_template_directory_uri() . '/dist/styles.css',
            array(),
            filemtime( $css_path )
        );
    }

    // Main JS (will stay empty for now, but we’re ready for Alpine/custom JS).
    $js_path = get_template_directory() . '/assets/js/main.js';
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script(
            'educk-main',
            get_template_directory_uri() . '/assets/js/main.js',
            array(),
            filemtime( $js_path ),
            true // load in footer
        );
    }
}
add_action( 'wp_enqueue_scripts', 'educk_theme_assets' );
