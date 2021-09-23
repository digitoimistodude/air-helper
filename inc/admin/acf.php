<?php
/**
 * Advanced Custom Fields plugin modifications.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:11:23
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-23 14:42:43
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
  $current_user = get_current_user_id();
  $user = new WP_User( $current_user );
  $domain = apply_filters( 'air_helper_dont_hide_acf_from_domain', 'dude.fi' );
  $meta_override = get_user_meta( $user->ID, '_airhelper_admin_show_acf', true );

  if ( 'true' === $meta_override ) {
    return $menu_links;
  }

  if ( strpos( $user->user_email, "@{$domain}" ) === false ) {
    return false;
  }

  return true;
} // end air_helper_maybe_hide_acf

/**
 * If ACF Pro license key is defined in .env file, try to load the key from there
 * and activate ACF if not already activated or activation is corrupted.
 *
 * Turn off by using `remove_action( 'admin_init', 'air_helper_get_acf_pro_license_from_env' )`
 *
 * @since 2.11.0
 */
add_action( 'admin_init', 'air_helper_get_acf_pro_license_from_env' );
function air_helper_get_acf_pro_license_from_env() {
  // Bail if no ACF
  if ( ! function_exists( 'acf_pro_get_license_key' ) ) {
    return;
  }

  // Bail if no ACF
  if ( ! method_exists( 'ACF_Admin_Updates', 'activate_pro_licence' ) ) {
    return;
  }

  // Bail if no license key in .env
  if ( empty( getenv( 'ACF_PRO_KEY' ) ) ) {
    return;
  }

  // Bail if license key is valid
  if ( acf_pro_get_license_key() ) {
    return;
  }

  // Force our license key into POST data, as ACF reads it from there
  $_POST['acf_pro_licence'] = getenv( 'ACF_PRO_KEY' );

  // Run ACF function to activate the license
  ACF_Admin_Updates::activate_pro_licence();
} // end air_helper_get_acf_pro_license_from_env
