<?php
/**
 * WordPress update related actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:17:20
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:31:52
 *
 * @package air-helper
 */

/**
 *  Remove Update WP text from admin footer.
 *
 *  @since  1.3.0
 */
add_filter( 'update_footer', '__return_empty_string', 11 );

/**
 * Hide WP updates nag.
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_wphidenag' )`
 *
 * @since  0.1.0
 */
add_action( 'admin_menu', 'air_helper_wphidenag' );
function air_helper_wphidenag() {
  remove_action( 'admin_notices', 'update_nag', 3 );
} // end air_helper_wphidenag
