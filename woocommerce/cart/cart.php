<?php
/**
 * Cart Page (custom Tailwind version for digital products / courses)
 *
 * Copy this file to:
 * yourtheme/woocommerce/cart/cart.php
 *
 * @package WooCommerce\Templates
 * @version 10.1.0 (customized)
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form max-w-5xl mx-auto px-4 py-10" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
		<div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
			<h1 class="text-lg font-semibold text-gray-900">
				<?php esc_html_e( 'Your cart', 'woocommerce' ); ?>
			</h1>
			<?php if ( WC()->cart->get_cart_contents_count() ) : ?>
				<p class="text-xs text-gray-500">
					<?php
					/* translators: %d: number of items in cart */
					printf( esc_html( _n( '%d course in cart', '%d courses in cart', WC()->cart->get_cart_contents_count(), 'woocommerce' ) ), WC()->cart->get_cart_contents_count() );
					?>
				</p>
			<?php endif; ?>
		</div>

		<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents w-full text-sm" cellspacing="0">
			<thead class="bg-gray-50 border-b border-gray-100">
				<tr class="text-xs font-semibold uppercase tracking-wide text-gray-500">
					<th class="product-remove w-10 px-6 py-3 text-left">
						<span class="screen-reader-text">
							<?php esc_html_e( 'Remove item', 'woocommerce' ); ?>
						</span>
					</th>
					<th class="product-thumbnail w-20 px-2 py-3 text-left">
						<span class="screen-reader-text">
							<?php esc_html_e( 'Thumbnail image', 'woocommerce' ); ?>
						</span>
					</th>
					<th scope="col" class="product-name px-2 py-3 text-left">
						<?php esc_html_e( 'Product', 'woocommerce' ); ?>
					</th>
					<th scope="col" class="product-price px-2 py-3 text-right">
						<?php esc_html_e( 'Price', 'woocommerce' ); ?>
					</th>
					<th scope="col" class="product-quantity px-2 py-3 text-right">
						<?php esc_html_e( 'Access', 'woocommerce' ); ?>
					</th>
					<th scope="col" class="product-subtotal px-6 py-3 text-right">
						<?php esc_html_e( 'Total', 'woocommerce' ); ?>
					</th>
				</tr>
			</thead>

			<tbody class="divide-y divide-gray-100">
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						$product_permalink = apply_filters(
							'woocommerce_cart_item_permalink',
							$_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
							$cart_item,
							$cart_item_key
						);
						?>
						<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
							<td class="product-remove px-6 py-4 align-top">
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a role="button" href="%s" class="remove inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900 transition" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
								?>
							</td>

							<td class="product-thumbnail px-2 py-4 align-top">
								<div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center">
									<?php
									$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );

									if ( ! $product_permalink ) {
										echo $thumbnail; // PHPCS: XSS ok.
									} else {
										printf( '<a href="%s" class="block">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
									}
									?>
								</div>
							</td>

							<td scope="row" role="rowheader" class="product-name px-2 py-4 align-top" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
								<div class="flex flex-col gap-1">
									<?php
									if ( ! $product_permalink ) {
										echo '<span class="font-semibold text-gray-900">' . wp_kses_post( $product_name ) . '</span>';
									} else {
										echo sprintf(
											'<a href="%s" class="font-semibold text-gray-900 hover:text-gray-700 transition">%s</a>',
											esc_url( $product_permalink ),
											wp_kses_post( $_product->get_name() )
										); // PHPCS: XSS ok.
									}

									do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

									// Meta data.
									$meta_html = wc_get_formatted_cart_item_data( $cart_item );
									if ( $meta_html ) {
										echo '<div class="text-xs text-gray-500 space-y-0.5">' . $meta_html . '</div>'; // PHPCS: XSS ok.
									}

									// Backorder notification.
									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo wp_kses_post(
											apply_filters(
												'woocommerce_cart_item_backorder_notification',
												'<p class="text-xs text-amber-600 mt-1">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>',
												$product_id
											)
										);
									}
									?>
								</div>
							</td>

							<td class="product-price px-2 py-4 text-right align-top" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
								<span class="font-medium text-gray-900">
									<?php
									echo apply_filters(
										'woocommerce_cart_item_price',
										WC()->cart->get_product_price( $_product ),
										$cart_item,
										$cart_item_key
									); // PHPCS: XSS ok.
									?>
								</span>
							</td>

							<td class="product-quantity px-2 py-4 text-right align-top" data-title="<?php esc_attr_e( 'Access', 'woocommerce' ); ?>">
								<?php
								// For digital courses we treat everything as "single access".
								$product_quantity = sprintf(
									'<span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">%s</span><input type="hidden" name="cart[%s][qty]" value="%d" />',
									esc_html__( 'Single access', 'woocommerce' ),
									esc_attr( $cart_item_key ),
									(int) $cart_item['quantity']
								);

								echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
								?>
							</td>

							<td class="product-subtotal px-6 py-4 text-right align-top" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
								<span class="font-semibold text-gray-900">
									<?php
									echo apply_filters(
										'woocommerce_cart_item_subtotal',
										WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ),
										$cart_item,
										$cart_item_key
									); // PHPCS: XSS ok.
									?>
								</span>
							</td>
						</tr>
						<?php
					}
				}
				?>

				<?php do_action( 'woocommerce_cart_contents' ); ?>

				<tr>
					<td colspan="6" class="actions px-6 py-4 bg-gray-50">
						<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
							<?php if ( wc_coupons_enabled() ) : ?>
								<div class="coupon flex flex-col sm:flex-row gap-2 sm:items-center">
									<label for="coupon_code" class="text-xs font-medium text-gray-700">
										<?php esc_html_e( 'Have a coupon?', 'woocommerce' ); ?>
									</label>
									<div class="flex gap-2">
										<input type="text" name="coupon_code" class="input-text w-full sm:w-48 rounded-full border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
										<button type="submit" class="button inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>">
											<?php esc_html_e( 'Apply', 'woocommerce' ); ?>
										</button>
									</div>
									<?php do_action( 'woocommerce_cart_coupon' ); ?>
								</div>
							<?php endif; ?>

							<div class="flex items-center justify-end gap-3">
								<button type="submit" class="button inline-flex items-center justify-center rounded-full px-4 py-2 text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>">
									<?php esc_html_e( 'Update cart', 'woocommerce' ); ?>
								</button>

								<?php do_action( 'woocommerce_cart_actions' ); ?>
							</div>

							<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
						</div>
					</td>
				</tr>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</tbody>
		</table>
	</div>

	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals max-w-5xl mx-auto px-4 mt-8">
	<div class="grid gap-6 lg:grid-cols-[2fr,1fr] items-start">
		<div>
			<?php
			/**
			 * You can add some reassurance text here if you want, e.g.:
			 *
			 * "All courses are digital products. You’ll get instant access after successful payment."
			 *
			 * or leave it empty and use hooks instead.
			 */
			?>
		</div>

		<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
			<?php
			/**
			 * Cart collaterals hook.
			 *
			 * @hooked woocommerce_cross_sell_display
			 * @hooked woocommerce_cart_totals - 10
			 */
			do_action( 'woocommerce_cart_collaterals' );
			?>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
