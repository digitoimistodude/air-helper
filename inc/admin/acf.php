<?php
/**
 * Advanced Custom Fields plugin modifications.
 *
 * @package air-helper
 */

/**
 *  Hide ACF for all users, execpt for users with spesific domain or override in user meta.
 *
 *  Turn off by using `remove_filter( 'acf/settings/show_admin', 'air_helper_maybe_hide_acf' )`
 *
 *  @since  1.12.0
 */
add_filter( 'acf/settings/show_admin', 'air_helper_maybe_hide_acf' );
function air_helper_maybe_hide_acf() {
  // Bail if user is allowed to enter acf
  if ( air_helper_allow_user_to( 'acf' ) ) {
    return true;
  }

  return false;
} // end air_helper_maybe_hide_acf

/**
 * If ACF Pro license key is defined in .env file, try to load the key from there.
 *
 * Turn off by using `remove_action( 'admin_init', 'air_helper_define_acf_pro_license' )`
 *
 * @since 2.12.0
 */
add_action( 'admin_init', 'air_helper_define_acf_pro_license' );
function air_helper_define_acf_pro_license() {
  if ( empty( getenv( 'ACF_PRO_KEY' ) ) ) {
    return;
  }

  define( 'ACF_PRO_LICENSE', getenv( 'ACF_PRO_KEY' ) );
} // end air_helper_define_acf_pro_license

/**
 * Show warning if ACF has field groups that are not saved in the local json.
 * With this, we try to ensure that all field groups do get saved and stored in the
 * repository when developing sites.
 *
 * @since 2.19.0
 */
add_action( 'admin_notices', 'air_helper_warn_if_not_all_acf_local_json_not_saved' );
function air_helper_warn_if_not_all_acf_local_json_not_saved() {
  // Bail if ACF not installed
  if ( ! function_exists( 'acf_get_field_groups' ) ) {
    return;
  }

  // Bail in production
  if ( 'production' === wp_get_environment_type() ) {
    return;
  }

  $not_saved = [];

  // Get all ACF field groups
  $groups = acf_get_field_groups();
  $groups = apply_filters( 'air_helper_acf_groups_to_warn_about', $groups );

  // Get ACF field groups saved as an JSON file
  $json = acf_get_local_json_files();

  // Bail if no field groups
  if ( empty( $groups ) ) {
    return;
  }

  // Loop field groups and test if group is in local json
  foreach ( $groups as $group ) {
    if ( isset( $json[ $group['key'] ] ) ) {
      continue;
    }

    // Group not in local json, add to error array
    $not_saved[] = mb_strtolower( $group['title'] );
  }

  // Bail if all field groups were in local json
  if ( empty( $not_saved ) ) {
    return;
  }

  // Show the actual error
  $class = 'notice notice-error';

  $message = __( '<b>ACF field group local files missing!</b>', 'air-helper' ); // phpcs:ignore
  $message .= ' ' . wp_sprintf( __( 'Make sure following field groups are saved: %1$s.', 'air-helper' ), implode( ', ', $not_saved ) );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); // phpcs:ignore

  do_action( 'qm/critical', esc_html( $message ) ); // phpcs:ignore
} // end air_helper_warn_if_not_all_acf_local_json_not_saved
