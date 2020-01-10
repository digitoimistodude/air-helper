<?php
/**
 * Collection of miscellaneous prioritized actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:03:27
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:04:23
 *
 * @package air-helper
 */

// Add our image size for lazyload
add_action( 'init', 'air_helper_add_lazyload_image_sizes' );
function air_helper_add_lazyload_image_sizes() {
  add_image_size( 'tiny-lazyload-thumbnail', 20, 20 );
} // end air_helper_add_lazyload_image_sizes
