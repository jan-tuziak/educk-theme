<?php
/**
 * Ensures all WooCommerce coupons exclude sale items.
 * - Runs hourly via WP-Cron
 * - Enforces on every coupon save (covers coupons made by other plugins)
 *
 * Drop this file in your theme (e.g. /inc/educk-force-coupons.php) and include it from functions.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class EDUCK_Theme_Force_Coupons_Exclude_Sale_Items {
	const CRON_HOOK = 'educk_force_exclude_sale_items_cron';

	public static function boot() {
		// Schedule cron when theme is switched to
		add_action( 'after_switch_theme', [ __CLASS__, 'schedule_cron' ] );
		// Unschedule when switching away from this theme
		add_action( 'switch_theme', [ __CLASS__, 'unschedule_cron' ] );

		// Safety net: ensure cron is scheduled even if theme wasn't just switched
		add_action( 'init', [ __CLASS__, 'maybe_schedule_cron' ] );

		// Cron callback
		add_action( self::CRON_HOOK, [ __CLASS__, 'process_all_coupons' ] );

		// Enforce on every coupon save/create
		add_action( 'save_post_shop_coupon', [ __CLASS__, 'enforce_on_save' ], 10, 3 );
	}

	public static function schedule_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			// start in ~1 minute, then hourly
			wp_schedule_event( time() + 60, 'hourly', self::CRON_HOOK );
		}
	}

	public static function unschedule_cron() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	public static function maybe_schedule_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, 'hourly', self::CRON_HOOK );
		}
	}

	/**
	 * Force "Exclude sale items" on save (new or updated coupon).
	 */
	public static function enforce_on_save( $post_ID, $post, $update ) {
		if ( 'shop_coupon' !== $post->post_type || wp_is_post_revision( $post_ID ) || 
wp_is_post_autosave( $post_ID ) ) {
			return;
		}

		// Prefer Woo CRUD
		if ( function_exists( 'wc_get_coupon' ) ) {
			$coupon = wc_get_coupon( $post_ID );
			if ( $coupon && method_exists( $coupon, 'set_exclude_sale_items' ) ) {
				if ( ! $coupon->get_exclude_sale_items() ) {
					$coupon->set_exclude_sale_items( true );
					$coupon->save();
				}
				return;
			}
		}

		// Fallback: set meta directly
		update_post_meta( $post_ID, 'exclude_sale_items', 'yes' );
	}

	/**
	 * Cron job: batch through all coupons missing "Exclude sale items" and fix them.
	 */
	public static function process_all_coupons() {
		$processed = 0;
		$updated   = 0;

		$paged    = 1;
		$per_page = 200;

		do {
			$query = new WP_Query( [
				'post_type'      => 'shop_coupon',
				'post_status'    => [ 'publish', 'pending', 'draft', 'future', 'private' ],
				'posts_per_page' => $per_page,
				'paged'          => $paged,
				'fields'         => 'ids',
				'meta_query'     => [
					'relation' => 'OR',
					[
						'key'     => 'exclude_sale_items',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'exclude_sale_items',
						'value'   => 'yes',
						'compare' => '!=',
					],
				],
			] );

			$ids = $query->posts;

			foreach ( $ids as $coupon_id ) {
				$processed++;

				if ( function_exists( 'wc_get_coupon' ) ) {
					$coupon = wc_get_coupon( $coupon_id );
					if ( $coupon && method_exists( $coupon, 'set_exclude_sale_items' ) ) {
						if ( ! $coupon->get_exclude_sale_items() ) {
						 $coupon->set_exclude_sale_items( true );
						 $coupon->save();
						 $updated++;
						}
						continue;
					}
				}

				// Fallback: set meta
				$current = get_post_meta( $coupon_id, 'exclude_sale_items', true );
				if ( 'yes' !== $current ) {
					update_post_meta( $coupon_id, 'exclude_sale_items', 'yes' );
					$updated++;
				}
			}

			$paged++;
		} while ( $query->max_num_pages >= $paged );

		// Optional: simple logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'[EDUCK THEME] Coupons processed: %d, updated: %d @ %s',
				$processed,
				$updated,
				current_time( 'mysql' )
			) );
		}
	}
}

EDUCK_Theme_Force_Coupons_Exclude_Sale_Items::boot();

