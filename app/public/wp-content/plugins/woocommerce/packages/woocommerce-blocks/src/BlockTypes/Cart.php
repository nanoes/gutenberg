<?php
namespace Automattic\WooCommerce\Blocks\BlockTypes;

use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Assets;
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;

/**
 * Cart class.
 *
 * @internal
 */
class Cart extends AbstractBlock {
	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'cart';

	/**
	 * Get the editor script handle for this block type.
	 *
	 * @param string $key Data to get, or default to everything.
	 * @return array|string;
	 */
	protected function get_block_type_editor_script( $key = null ) {
		$script = [
			'handle'       => 'wc-' . $this->block_name . '-block',
			'path'         => $this->asset_api->get_block_asset_build_path( $this->block_name ),
			'dependencies' => [ 'wc-blocks' ],
		];
		return $key ? $script[ $key ] : $script;
	}

	/**
	 * Get the frontend script handle for this block type.
	 *
	 * @see $this->register_block_type()
	 * @param string $key Data to get, or default to everything.
	 * @return array|string
	 */
	protected function get_block_type_script( $key = null ) {
		$script = [
			'handle'       => 'wc-' . $this->block_name . '-block-frontend',
			'path'         => $this->asset_api->get_block_asset_build_path( $this->block_name . '-frontend' ),
			'dependencies' => [],
		];
		return $key ? $script[ $key ] : $script;
	}

	/**
	 * Enqueue frontend assets for this block, just in time for rendering.
	 *
	 * @param array $attributes  Any attributes that currently are available from the block.
	 */
	protected function enqueue_assets( array $attributes ) {
		do_action( 'woocommerce_blocks_enqueue_cart_block_scripts_before' );
		parent::enqueue_assets( $attributes );
		do_action( 'woocommerce_blocks_enqueue_cart_block_scripts_after' );
	}

	/**
	 * Append frontend scripts when rendering the Cart block.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 * @return string Rendered block type output.
	 */
	protected function render( $attributes, $content ) {
		// Deregister core cart scripts and styles.
		wp_dequeue_script( 'wc-cart' );
		wp_dequeue_script( 'wc-password-strength-meter' );
		wp_dequeue_script( 'selectWoo' );
		wp_dequeue_style( 'select2' );

		return $this->inject_html_data_attributes( $content . $this->get_skeleton(), $attributes );
	}

	/**
	 * Extra data passed through from server to client for block.
	 *
	 * @param array $attributes  Any attributes that currently are available from the block.
	 *                           Note, this will be empty in the editor context when the block is
	 *                           not in the post content on editor load.
	 */
	protected function enqueue_data( array $attributes = [] ) {
		parent::enqueue_data( $attributes );

		$this->asset_data_registry->add(
			'shippingCountries',
			function() {
				return $this->deep_sort_with_accents( WC()->countries->get_shipping_countries() );
			},
			true
		);
		$this->asset_data_registry->add(
			'shippingStates',
			function() {
				return $this->deep_sort_with_accents( WC()->countries->get_shipping_country_states() );
			},
			true
		);
		$this->asset_data_registry->add(
			'countryLocale',
			function() {
				// Merge country and state data to work around https://github.com/woocommerce/woocommerce/issues/28944.
				$country_locale = wc()->countries->get_country_locale();
				$states         = wc()->countries->get_states();

				foreach ( $states as $country => $states ) {
					if ( empty( $states ) ) {
						$country_locale[ $country ]['state']['required'] = false;
						$country_locale[ $country ]['state']['hidden']   = true;
					}
				}
				return $country_locale;
			},
			true
		);
		$this->asset_data_registry->add( 'baseLocation', wc_get_base_location(), true );
		$this->asset_data_registry->add( 'isShippingCalculatorEnabled', filter_var( get_option( 'woocommerce_enable_shipping_calc' ), FILTER_VALIDATE_BOOLEAN ), true );
		$this->asset_data_registry->add( 'displayItemizedTaxes', 'itemized' === get_option( 'woocommerce_tax_total_display' ), true );
		$this->asset_data_registry->add( 'displayCartPricesIncludingTax', 'incl' === get_option( 'woocommerce_tax_display_cart' ), true );
		$this->asset_data_registry->add( 'taxesEnabled', wc_tax_enabled(), true );
		$this->asset_data_registry->add( 'couponsEnabled', wc_coupons_enabled(), true );
		$this->asset_data_registry->add( 'shippingEnabled', wc_shipping_enabled(), true );
		$this->asset_data_registry->add( 'hasDarkEditorStyleSupport', current_theme_supports( 'dark-editor-style' ), true );
		$this->asset_data_registry->register_page_id( isset( $attributes['checkoutPageId'] ) ? $attributes['checkoutPageId'] : 0 );

		// Hydrate the following data depending on admin or frontend context.
		if ( ! is_admin() && ! WC()->is_rest_api_request() ) {
			$this->hydrate_from_api();
		}

		do_action( 'woocommerce_blocks_cart_enqueue_data' );
	}

	/**
	 * Removes accents from an array of values, sorts by the values, then returns the original array values sorted.
	 *
	 * @param array $array Array of values to sort.
	 * @return array Sorted array.
	 */
	protected function deep_sort_with_accents( $array ) {
		if ( ! is_array( $array ) || empty( $array ) ) {
			return $array;
		}

		if ( is_array( reset( $array ) ) ) {
			return array_map( [ $this, 'deep_sort_with_accents' ], $array );
		}

		$array_without_accents = array_map( 'remove_accents', array_map( 'wc_strtolower', array_map( 'html_entity_decode', $array ) ) );
		asort( $array_without_accents );
		return array_replace( $array_without_accents, $array );
	}

	/**
	 * Hydrate the cart block with data from the API.
	 */
	protected function hydrate_from_api() {
		$this->asset_data_registry->hydrate_api_request( '/wc/store/cart' );
	}

	/**
	 * Render skeleton markup for the cart block.
	 */
	protected function get_skeleton() {
		return '
			<div class="wc-block-skeleton wc-block-components-sidebar-layout wc-block-cart wc-block-cart--is-loading wc-block-cart--skeleton hidden" aria-hidden="true">
				<div class="wc-block-components-main wc-block-cart__main">
					<h2 class="wc-block-components-title"><span></span></h2>
					<table class="wc-block-cart-items">
						<thead>
							<tr class="wc-block-cart-items__header">
								<th class="wc-block-cart-items__header-image"><span /></th>
								<th class="wc-block-cart-items__header-product"><span /></th>
								<th class="wc-block-cart-items__header-total"><span /></th>
							</tr>
						</thead>
						<tbody>
							<tr class="wc-block-cart-items__row">
								<td class="wc-block-cart-item__image">
									<div><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=" width="1" height="1" /></div>
								</td>
								<td class="wc-block-cart-item__product">
									<div class="wc-block-cart-item__product-name"></div>
									<div class="wc-block-cart-item__individual-price"></div>
									<div class="wc-block-cart-item__product-metadata"></div>
									<div class="wc-block-components-quantity-selector">
										<input class="wc-block-components-quantity-selector__input" type="number" step="1" min="0" value="1" />
										<button class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--minus">－</button>
										<button class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--plus">＋</button>
									</div>
								</td>
								<td class="wc-block-cart-item__total">
									<div class="wc-block-cart-item__price"></div>
								</td>
							</tr>
							<tr class="wc-block-cart-items__row">
								<td class="wc-block-cart-item__image">
									<div><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=" width="1" height="1" /></div>
								</td>
								<td class="wc-block-cart-item__product">
									<div class="wc-block-cart-item__product-name"></div>
									<div class="wc-block-cart-item__individual-price"></div>
									<div class="wc-block-cart-item__product-metadata"></div>
									<div class="wc-block-components-quantity-selector">
										<input class="wc-block-components-quantity-selector__input" type="number" step="1" min="0" value="1" />
										<button class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--minus">－</button>
										<button class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--plus">＋</button>
									</div>
								</td>
								<td class="wc-block-cart-item__total">
									<div class="wc-block-cart-item__price"></div>
								</td>
							</tr>
							<tr class="wc-block-cart-items__row">
								<td class="wc-block-cart-item__image">
									<div><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=" width="1" height="1" /></div>
								</td>
								<td class="wc-block-cart-item__product">
									<div class="wc-block-cart-item__product-name"></div>
									<div class="wc-block-cart-item__individual-price"></div>
									<div class="wc-block-cart-item__product-metadata"></div>
									<div class="wc-block-components-quantity-selector">
										<input class="wc-block-components-quantity-selector__input" type="number" step="1" min="0" value="1" />
										<button class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--minus">－</button>
										<button class="wc-block-components-quantity-selector__button wc-block-components-quantity-selector__button--plus">＋</button>
									</div>
								</td>
								<td class="wc-block-cart-item__total">
									<div class="wc-block-cart-item__price"></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="wc-block-components-sidebar wc-block-cart__sidebar">
					<div class="components-card"></div>
				</div>
			</div>
		' . $this->get_skeleton_inline_script();
	}
}
