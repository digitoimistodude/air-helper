<?php
/**
 * Collection of miscellaneous prioritized actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:03:27
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-20 15:51:01
 *
 * @package air-helper
 */

/**
 * Add preload thumbnail image size for lazyload.
 *
 * @since  1.11.0
 */
add_action( 'init', 'air_helper_add_lazyload_image_sizes' );
function air_helper_add_lazyload_image_sizes() {
  add_image_size( 'tiny-lazyload-thumbnail', 20, 20 );
} // end air_helper_add_lazyload_image_sizes
