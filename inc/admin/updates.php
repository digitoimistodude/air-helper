<?php
/**
 * WordPress update related actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:17:20
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:34:03
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
 *
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_wphidenag' )`
 *
 * @since  0.1.0
 */
add_action( 'admin_menu', 'air_helper_wphidenag' );
function air_helper_wphidenag() {
  remove_action( 'admin_notices', 'update_nag' );
} // end air_helper_wphidenag

/**
 * Hide all WP update nags with styles.
 *
 * Turn off by using `remove_action( 'admin_head', 'air_helper_hide_nag_styles' )`
 *
 * @since  5.0.0
 */
add_action( 'admin_head', 'air_helper_hide_nag_styles' );
function air_helper_hide_nag_styles() { ?>
  <style>
    .update-nag,
    #wp-admin-bar-updates,
    #menu-plugins .update-plugins,
    .theme-info .notice,
    .wp-heading-inline .theme-count,
    table.plugins .update-message,
    .theme-browser .update-message,
    body.plugins-php ul.subsubsub li.upgrade {
      display: none;
      visibility: hidden;
    }

    .plugin-update-tr {
      height: 1px;
    }
  </style>
<?php } // end air_helper_hide_nag_styles
