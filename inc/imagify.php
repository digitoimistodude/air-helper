<?php
/**
 * Imagify default settings.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-02-11 15:18:11
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 16:35:16
 *
 * @package air-helper
 */

// Disable some features
add_filter( 'get_imagify_option_admin_bar_menu', '__return_false' );
add_filter( 'get_imagify_option_convert_to_webp', '__return_false' );

/**
 * Get Imagify API key from .env
 *
 * @since 5.0.0
 */
add_filter( 'get_imagify_option_api_key', 'air_helper_imagify_api_key' );
function air_helper_imagify_api_key() {
  return getenv( 'IMAGIFY_API_KEY' );
} // end air_helper_imagify_api_key

/**
 * Resize large images and set maximum width.
 *
 * Disable with `add_filter( 'get_imagify_option_resize_larger', '__return_false' )` and `remove_filter( 'get_imagify_option_resize_larger_w', 'air_helper_imagify_resize_larger_w' )`
 * Modify the maximum width with `air_helper_imagify_resize_larger_w` filter
 *
 * @since  5.0.0
 */
add_filter( 'get_imagify_option_resize_larger', '__return_true' );
add_filter( 'get_imagify_option_resize_larger_w', 'air_helper_imagify_resize_larger_w' );
function air_helper_imagify_resize_larger_w() {
  return apply_filters( 'air_helper_imagify_resize_larger_w', '2048' );
} // end air_helper_imagify_resize_larger_w

/**
 * Set optimization level to normal.
 *
 * Disable with `remove_filter( 'get_imagify_option_optimization_level', 'air_helper_imagify_optimization_level' )`
 * Modify the level with `air_helper_imagify_optimization_level` filter. 0 = normal, 1 = aggressive, 2 = ultra.
 *
 * @since  5.0.0
 */
add_filter( 'get_imagify_option_optimization_level', 'air_helper_imagify_optimization_level' );
function air_helper_imagify_optimization_level( $level ) {
  return (string) apply_filters( 'air_helper_imagify_optimization_level', '0' );
} // end air_helper_imagify_optimization_level
