<?php

function air_helper_woocommerce_locate_template( $template, $template_name, $template_path ) {
	global $woocommerce;

	// Store default template.
	$_template = $template;

	if ( ! $template_path ) {
		$template_path = $woocommerce->template_url;
	}

	$plugin_path = air_helper_base_path() . '/woocommerce/';

	// Look within passed path within the theme - this is priority.
	$template = locate_template( array(
		$template_path . $template_name,
		$template_name,
	) );

	// Modification: Get the template from this plugin, if it exists.
	if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
		$template = $plugin_path . $template_name;
	}

	// Use default template.
	if ( ! $template ) {
		$template = $_template;
	}

	// Return what we found.
	return $template;
}
add_filter( 'woocommerce_locate_template', 'air_helper_woocommerce_locate_template', 10, 3 );

add_filter( 'woocommerce_show_page_title', '__return_false' );

add_action( 'woocommerce_shop_loop_item_title', 'air_wc_shop_loop_item_title', 5 );
function air_wc_shop_loop_item_title() {
  echo '<div class="content">';
}

add_action( 'woocommerce_after_shop_loop_item', 'air_wc_after_shop_loop_item_title', 15 );
function air_wc_after_shop_loop_item_title() {
  echo '</div>';
}


add_action( 'woocommerce_after_shop_loop', 'air_wc_after_shop_loop_pagination_wrap_start', 5 );
function air_wc_after_shop_loop_pagination_wrap_start() {
  echo '<div class="wc-pagination-wrap">';
}

add_action( 'woocommerce_after_shop_loop', 'air_wc_after_shop_loop_pagination_wrap_stop', 20 );
function air_wc_after_shop_loop_pagination_wrap_stop() {
  echo '</div>';
}

add_filter( 'woocommerce_breadcrumb_defaults', 'air_wc_change_breadcrumb_delimiter' );
function air_wc_change_breadcrumb_delimiter( $defaults ) {
	$defaults['home'] = '';
	$defaults['delimiter'] = ' <span class="sep">&gt;</span> ';
	return $defaults;
}

add_action( 'init', 'air_wc_clear_cart_url' );
function air_wc_clear_cart_url() {
  global $woocommerce;
	if ( isset( $_GET['empty-cart'] ) ) {
		$woocommerce->cart->empty_cart();
	}
}
