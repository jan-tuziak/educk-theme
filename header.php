<?php
/**
 * Theme Header
 *
 * This file outputs the <head>, opens the <body>, and includes the navbar.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Preload / load Archivo font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-bg-main text-text font-sans' ); ?>>
<?php wp_body_open(); ?>

<header class="w-full border-b border-[#E0E0E0] bg-[#F5F5F5]">
    <?php
        // Load the navbar component from template-parts.
        get_template_part( 'template-parts/layout/navbar' );
    ?>
</header>

<!-- Main page content wrapper -->
<main id="site-main" class="min-h-screen">
