<?php
/**
 * Actions related to sending mail in development and staging environments.
 *
 * @package air-helper
 */

/**
 *  Force to address in wp_mail function so that test emails wont go to client.
 *
 *  Disabled by default. Enable by using:
 *  `add_filter( 'air_helper_enable_force_mail_to', '__return_true' );`
 *
 *  @since  0.1.0
 *  @since  3.2.12  Changed to opt-in instead of opt-out. Closes #67, Ref: DEV-420
 */
if ( wp_get_environment_type() === 'development' ) {
  if ( apply_filters( 'air_helper_enable_force_mail_to', false ) ) {
    add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
  }
}

if ( wp_get_environment_type() === 'staging' ) {
  if ( apply_filters( 'air_helper_enable_force_mail_to', false ) ) {
    add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
  }
  add_filter( 'wp_mail_from', 'air_helper_staging_wp_mail_from' );
}

/**
 *  Prevent email leaks to unwanted recipients. Remove all email addresses which domain
 *  is not explicitly allowed. If no allowed recipient addresses, email will not be sent.
 *
 *  @since  0.1.0
 *  @param  array $args Default wp_mail agruments.
 *  @return array         New wp_mail agruments with filtered addresses
 */
function air_helper_helper_force_mail_to( $args ) {
  $original_to_is_array = true;
  $original_to = $args['to'];

  // If addresses are string, try to explode for easier handling
  if ( ! is_array( $original_to ) ) {
    $original_to_is_array = false;
    $to = explode( ',', $original_to );
    $to = array_map( 'trim', $to );
  }

  // Allow sending emails to all addresses in these domains
  $allowed_domains = apply_filters( 'air_helper_mail_to_allowed_domains', [ 'dude.fi' ] );

  // Check all to addresses and if their domains are allowed
  foreach ( $to as $key => $email ) {
    $email_parts = explode( '@', $email, 2 );
    $domain = $email_parts[1] ?? '';
    if ( ! in_array( $domain, $allowed_domains ) ) {
      unset( $to[ $key ] );
    }
  }

  // If original to field was string, return it as a string
  if ( ! $original_to_is_array ) {
    $to = implode( ',', $to );
  }

  $args['to'] = $to;

  return $args;
} // end air_helper_helper_force_mail_to

/**
 *  Do not force to address when sending notification to new user created.
 *
 *  Turn off by using `remove_action( 'edit_user_created_user', 'air_helper_dont_force_created_user_mail' )`
 *
 *  @since  1.2.0
 *  @param  string $user_id ID of new user.
 *  @param  string $notify  Who to notify about user registration.
 */
add_action( 'edit_user_created_user', 'air_helper_dont_force_created_user_mail', 10, 2 );
function air_helper_dont_force_created_user_mail( $user_id, $notify ) {
  remove_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
  wp_send_new_user_notifications( $user_id, $notify );

  // Re-add the filter if it was enabled and we're not in production
  if ( wp_get_environment_type() !== 'production' && apply_filters( 'air_helper_enable_force_mail_to', false ) ) {
    add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
  }
} // end air_helper_dont_force_created_user_mail

/**
 *  Force from address in staging to fix some oddness.
 *
 *  @since  1.8.1
 *  @return string  Email address
 */
function air_helper_staging_wp_mail_from() {
  return 'wordpress@' . str_replace( [ 'http://', 'https://', '/wp' ], '', get_site_url() );
} // end air_helper_staging_wp_mail_from
