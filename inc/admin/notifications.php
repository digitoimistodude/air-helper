<?php
/**
 * Remove, modify and add notifications on dashbard.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:10:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 15:15:08
 *
 * @package air-helper
 */

/**
 *  Remove some notices from dashboard.
 *
 *  Turn off by using `remove_action( 'admin_init', 'air_helper_clean_admin_notices' )`
 *
 *  @since  1.7.1
 */
add_action( 'admin_init', 'air_helper_clean_admin_notices', 999 );
function air_helper_clean_admin_notices() {
  $remove_notices = array(
    'sg_subscription_widget_admin_notice', // sendgrid
    'eae_page_scanner_notice', // email encoder
  );

  // Allow filtering which notices to remove
  $remove_notices = apply_filters( 'air_helper_clear_admin_notices', $remove_notices );

  // Remove notices
  if ( ! empty( $remove_notices ) ) {
    foreach ( $remove_notices as $notice ) {
      remove_action( 'admin_notices', $notice );
    }
  }

  // GADWP notice is better to remove by updating a option so it won't show up again
  if ( ! get_option( 'exactmetrics_tracking_notice' ) ) {
    update_option( 'exactmetrics_tracking_notice', true );
  }
} // end air_helper_clean_admin_notices
