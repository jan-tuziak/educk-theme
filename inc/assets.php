<?php
function educk_enqueue_assets() {
    $css_path = get_template_directory() . '/dist/styles.css';

    if ( file_exists( $css_path ) ) {
        wp_enqueue_style(
            'educk-theme',
            get_template_directory_uri() . '/dist/styles.css',
            array(),
            filemtime( $css_path )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'educk_enqueue_assets' );
