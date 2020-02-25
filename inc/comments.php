<?php
/**
 * Commenting and pingback related.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:22:06
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:23:11
 *
 * @package air-helper
 */

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 * Turn off by using `remove_action( 'wp_head', 'air_helper_pingback_header' )`
 *
 * @since  0.1.0
 */
add_action( 'wp_head', 'air_helper_pingback_header' );
function air_helper_pingback_header() {
  if ( is_singular() && pings_open() ) {
    echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
  }
} // end air_helper_pingback_heade
