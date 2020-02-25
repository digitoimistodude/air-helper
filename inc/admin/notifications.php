<?php
/**
 * Remove, modify and add notifications on dashbard.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:10:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-20 10:50:37
 *
 * @package air-helper
 */

// Disable Try Gutenberg notification in dashboard added on 4.8.9.
remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );

/**
 *  Remove some notices from dashboard.
 *
 *  Turn off by using `remove_action( 'admin_init', 'air_helper_clean_admin_notices' )`
 *
 *  @since  1.7.1
 */
add_action( 'admin_init', 'air_helper_clean_admin_notices', 999 );
function air_helper_clean_admin_notices() {
  $remove_notices = [
    'sg_subscription_widget_admin_notice', // sendgrid
    'eae_page_scanner_notice', // email encoder
  ];

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

  // Imagify upsell ads needs to be disabled by user
  update_user_meta( get_current_user_id(), '_imagify_ignore_ads', [ 'wp-rocket' ] );

  // Hide always all redis object cache notifications
  define( 'WP_REDIS_DISABLE_BANNERS', true );

  // GADWP version 6.0.0 update onboarding
  add_action( 'exactmetrics_enable_onboarding_wizard', '__return_false' );

  // GADWP version 6.0.0 update notices
  if ( ! get_option( 'exactmetrics_frontend_tracking_notice_viewed' ) ) {
    update_option( 'exactmetrics_frontend_tracking_notice_viewed', true );
  }

  // GADWP version 6.0.0 new auth method notice
  if ( ! get_option( 'exactmetrics_notices' ) ) {
    update_option( 'exactmetrics_notices', [ 'exactmetrics_auth_not_manual' => true ] );
  }
} // end air_helper_clean_admin_notices

