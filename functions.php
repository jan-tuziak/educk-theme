<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function educk_theme_assets() {
    wp_enqueue_style(
        'educk-styles',
        get_template_directory_uri() . '/dist/styles.css',
        array(),
        filemtime( get_template_directory() . '/dist/styles.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'educk_theme_assets' );
