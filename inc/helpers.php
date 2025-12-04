<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Tailwind classes to primary menu links (desktop + mobile).
 *
 * - Base: dark text, subtle bottom border, smooth hover.
 * - Hover: orange text + orange underline.
 * - Active: orange text + underline, slightly bolder.
 */
if ( ! function_exists( 'educk_primary_nav_link_classes' ) ) {
    function educk_primary_nav_link_classes( $atts, $item, $args ) {
        // Only affect the "primary" menu location.
        if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
            return $atts;
        }

        // Base link classes (normal state).
        $base_classes = 'inline-flex items-center border-b-2 border-transparent pb-1 text-[15px] font-medium text-[#333333] transition-colors duration-150 hover:text-[#E04430] hover:border-[#E04430]';

        // Active/Current link classes.
        $active_classes = 'inline-flex items-center border-b-2 border-[#E04430] pb-1 text-[15px] font-semibold text-[#E04430] transition-colors duration-150';

        $item_classes = (array) $item->classes;

        $is_current =
            in_array( 'current-menu-item', $item_classes, true ) ||
            in_array( 'current-menu-parent', $item_classes, true ) ||
            in_array( 'current-menu-ancestor', $item_classes, true );

        $new_classes = $is_current ? $active_classes : $base_classes;

        // Preserve any existing classes WP or plugins set.
        if ( isset( $atts['class'] ) && $atts['class'] ) {
            $atts['class'] .= ' ' . $new_classes;
        } else {
            $atts['class'] = $new_classes;
        }

        return $atts;
    }
}
add_filter( 'nav_menu_link_attributes', 'educk_primary_nav_link_classes', 10, 3 );
