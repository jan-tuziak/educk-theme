<?php

/**
 * Main site navbar
 *
 * Desktop:
 *  - logo left
 *  - menu in the middle
 *  - cart icon on the right
 *
 * Mobile:
 *  - logo left
 *  - hamburger in the middle
 *  - cart icon right
 *  - full vertical menu overlay
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<header class="border-b border-[#E0E0E0] bg-[#F5F5F5]">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 lg:py-4">
        <!-- Logo -->
        <div class="flex items-center">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-1">
                <span class="text-[28px] font-semibold tracking-widest text-[#F3B515] leading-none">
                    EDUC
                </span>
                <span class="text-[28px] leading-none text-[#E04430] -ml-1">
                    &lt;
                </span>
            </a>
        </div>

        <!-- Desktop nav -->
        <nav class="hidden flex-1 items-center justify-center lg:flex">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'primary-nav flex items-center gap-8',
                'fallback_cb'    => false,
                'depth'          => 1,
            ));
            ?>
        </nav>

        <!-- Right side: cart + mobile toggle -->
        <div class="flex items-center gap-3">
            <!-- Cart button -->
            <a href="<?php echo esc_url(wc_get_cart_url() ?? '#'); ?>"
                class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border-2 border-[#F3B515] bg-[#333333] text-white">
                <!-- Simple cart icon (SVG) -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l3-7H6.4M7 13L5.4 5M7 13l-2 9h14l-2-9M10 21a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z" />
                </svg>

                <!-- Cart badge (static 1 for now, can be wired to Woo later) -->
                <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#E04430] px-1 text-[11px] font-semibold">
                    1
                </span>
            </a>

            <!-- Mobile menu toggle -->
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-[#333333] text-white lg:hidden"
                aria-label="Open menu"
                aria-expanded="false"
                data-nav-toggle>
                <!-- Hamburger icon -->
                <span class="block h-0.5 w-5 bg-current transition-all data-[state=open]:translate-y-[3px] data-[state=open]:rotate-45"></span>
                <span class="block h-0.5 w-5 bg-current transition-all data-[state=open]:opacity-0 mt-[5px]"></span>
                <span class="block h-0.5 w-5 bg-current transition-all data-[state=open]:-translate-y-[3px] data-[state=open]:-rotate-45 mt-[5px]"></span>
            </button>
        </div>
    </div>

    <!-- Mobile menu overlay -->
    <div
        class="hidden bg-[#333333] text-white lg:hidden"
        data-nav-menu>
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-6 text-[18px]">
            <?php
            // Reuse the same menu but vertical
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'primary-nav-mobile flex flex-col gap-4',
                'fallback_cb'    => false,
                'depth'          => 1,
            ));
            ?>
        </div>
    </div>
</header>