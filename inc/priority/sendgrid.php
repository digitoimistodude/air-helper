<?php
/**
 * Sendgrid mail gateway actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:40:38
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:42:56
 *
 * @package air-helper
 */

/**
 *  Force essential SendGrid settings.
 *
 *  Turn off by using `add_filter( 'air_helper_sendgrid', '__return_false' )`
 *
 *  @since 0.1.0
 */
if ( apply_filters( 'air_helper_sendgrid', true ) ) {
  // Check that SendGrid API key is set and show warning if not
  add_action( 'admin_init', 'air_helper_sendgrid_check' );

  // Define SendGrid settings
  define( 'SENDGRID_API_KEY', getenv( 'SENDGRID_API_KEY' ) );
  define( 'SENDGRID_CATEGORIES', sanitize_title( get_option( 'blogname' ) ) );
  define( 'SENDGRID_STATS_CATEGORIES', sanitize_title( get_option( 'blogname' ) ) );
} // end if

/**
 *  Check if SendGrid is not active or configured.
 *
 *  @since  1.5.3
 */
function air_helper_sendgrid_check() {
  if ( ! getenv( 'SENDGRID_API_KEY' ) || ! is_plugin_active( 'sendgrid-email-delivery-simplified/wpsendgrid.php' ) ) {
    add_action( 'admin_notices', 'air_helper_sendgrid_not_configured_notice' );
  }
} // end air_helper_sendgrid_check

/**
 *  Show notice if SendGrid is not active or configured.
 *
 *  @since  1.5.3
 */
function air_helper_sendgrid_not_configured_notice() {
  $class = 'notice notice-error';
  $message = __( 'SendGrid email delivery plugin is not active or configured. Please contact your agency to fix this issue.', 'air-helper' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
} // end air_helper_sendgrid_not_configured_notice
