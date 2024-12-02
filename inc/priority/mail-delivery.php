<?php
/**
 * Mail delivery related actions.
 *
 * @package air-helper
 */

/**
 *  Force essential mail delivery service settings.
 *
 *  Turn off by using `add_filter( 'air_helper_mail_delivery', '__return_false' )`,
 *  the SendGrid specific hook is there for legacy support reasons.
 *
 *  @since 0.1.0
 */
if ( apply_filters( 'air_helper_mail_delivery', true ) && apply_filters( 'air_helper_sendgrid', true ) ) {
  add_action( 'admin_init', 'air_helper_mail_delivery_check' );

  // Mailgun support.
  if ( class_exists( 'Mailgun' ) && ( defined( 'MAILGUN_USEAPI' ) && MAILGUN_USEAPI ) ) {
    define( 'MAILGUN_APIKEY', getenv( 'MAILGUN_API_KEY' ) );
    define( 'MAILGUN_REGION', ! empty( getenv( 'MAILGUN_REGION' ) ) ? getenv( 'MAILGUN_REGION' ) : 'eu' ); // default to eu region
    define( 'MAILGUN_DOMAIN', ! empty( getenv( 'MAILGUN_DOMAIN' ) ) ? getenv( 'MAILGUN_DOMAIN' ) : str_replace( [ 'https://', 'http://', 'www.', '/wp' ], '', get_site_url() ) );
  }

  // SendGrid for legacy support.
  if ( class_exists( 'Sendgrid_Tools' ) && getenv( 'SENDGRID_API_KEY' ) ) {
    define( 'SENDGRID_API_KEY', getenv( 'SENDGRID_API_KEY' ) );
    define( 'SENDGRID_CATEGORIES', sanitize_title( get_option( 'blogname' ) ) );
    define( 'SENDGRID_STATS_CATEGORIES', sanitize_title( get_option( 'blogname' ) ) );
  }

  // Mailhog support for development enviroments.
  if ( 'development' === wp_get_environment_type() ) {
    add_action( 'phpmailer_init', function ( $phpmailer ) {
      $phpmailer->Host = ! empty( getenv( 'WP_MAILHOG_HOST' ) ) ? getenv( 'WP_MAILHOG_HOST' ) : '127.0.0.1'; // phpcs:ignore
      $phpmailer->Port = ! empty( getenv( 'WP_MAILHOG_POST' ) ) ? getenv( 'WP_MAILHOG_POST' ) : '1025'; // phpcs:ignore
      $phpmailer->SMTPAuth = false; // phpcs:ignore
      $phpmailer->isSMTP();
    } );
  }
} // end if

/**
 *  Check if mail delivery service is not active or configured.
 *
 *  @since  1.5.3
 */
function air_helper_mail_delivery_check() {
  if ( class_exists( 'Mailgun' ) && getenv( 'MAILGUN_API_KEY' ) && ( defined( 'MAILGUN_USEAPI' ) && MAILGUN_USEAPI ) ) {
    return true;
  }

  // SendGrid for legacy support.
  if ( class_exists( 'Sendgrid_Tools' ) && getenv( 'SENDGRID_API_KEY' ) ) {
    return true;
  }

  // Do not show the notice in dev.
  if ( 'development' === wp_get_environment_type() ) {
    return true;
  }

  add_action( 'admin_notices', 'air_helper_mail_delivery_not_configured_notice' );
  return false;
} // end air_helper_mail_delivery_check

/**
 *  Show notice if mail delivery service is not active or configured.
 *
 *  @since  1.5.3
 */
function air_helper_mail_delivery_not_configured_notice() {
  $class = 'notice notice-error';
  $message = __( 'Email delivery is not active or configured. Please contact your agency to fix this issue.', 'air-helper' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
} // end air_helper_mail_delivery_not_configured_notice
