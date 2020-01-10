<?php
/**
 * Modify admin bar.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:14:34
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:15:13
 *
 * @package air-helper
 */

/**
 * Clean up admin bar.
 * Turn off by using `remove_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_bar_links', 'myprefix_override_air_helper_helper_remove_admin_bar_links' )`
 *
 * @since  0.1.0
 */
add_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' );
function air_helper_helper_remove_admin_bar_links() {
  global $wp_admin_bar;

  $remove_items = apply_filters( 'air_helper_helper_remove_admin_bar_links', array(
    'about',
    'wporg',
    'documentation',
    'support-forums',
    'feedback',
    'updates',
    'comments',
  ) );

  foreach ( $remove_items as $item ) {
    $wp_admin_bar->remove_menu( $item );
  }
} // end air_helper_helper_remove_admin_bar_links
