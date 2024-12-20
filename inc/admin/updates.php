<?php
/**
 * WordPress update related actions.
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
    .cookiebot-admin-notice-container,
    .update-nag,
    #yoast-first-time-configuration-notice,
    #wp-admin-bar-updates,
    #menu-plugins .update-plugins,
    .theme-info .notice,
    .wp-heading-inline .theme-count,
    table.plugins .update-message,
    .theme-browser .update-message,
    body.plugins-php ul.subsubsub li.upgrade {
      display: none !important;
      visibility: hidden !important;
    }

    .plugin-update-tr {
      height: 1px;
    }
  </style>
<?php } // end air_helper_hide_nag_styles
