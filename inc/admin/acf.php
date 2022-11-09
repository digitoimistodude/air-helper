<?php
/**
 * Advanced Custom Fields plugin modifications.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:11:23
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2022-11-09 16:03:16
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
