<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme setup: supports, menus, etc.
 */
function educk_theme_setup() {
    // Let WordPress manage <title>.
    add_theme_support( 'title-tag' );

    // Featured images support.
    add_theme_support( 'post-thumbnails' );

    // HTML5 markup support.
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
    ) );

    // WooCommerce support (we’ll use this later when we start overriding templates).
    add_theme_support( 'woocommerce' );

    // Register nav menus.
    register_nav_menus( array(
        'primary'   => __( 'Primary Menu', 'educk' ),
        'footer'    => __( 'Footer Menu', 'educk' ),
    ) );
}
add_action( 'after_setup_theme', 'educk_theme_setup' );
