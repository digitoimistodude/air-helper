<?php
/**
 * Advanced Custom Fields plugin modifications.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:11:23
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 11:59:25
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
