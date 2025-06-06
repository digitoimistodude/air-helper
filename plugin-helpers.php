<?php
/**
 * Helper functions to use in this plugin.
 *
 * @package air-helper
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 *  Get the version at where plugin was activated.
 *
 *  @since  1.6.0
 *  @return integer  version where plugin was activated
 */
function air_helper_activated_at_version() {
  return absint( apply_filters( 'air_helper_activated_at_version', get_option( 'air_helper_activated_at_version' ) ) );
} // end air_helper_activated_at_version

/**
 *  Wrapper function to get real base path for this plugin.
 *
 *  @since  0.1.0
 *  @return string  Path to this plugin
 */
function air_helper_base_path() {
  return untrailingslashit( plugin_dir_path( __FILE__ ) );
} // end air_helper_base_path

/**
 *  Wrapper function to get real url path for this plugin.
 *
 *  @since  0.1.0
 *  @return string  Url to this plugin
 */
function air_helper_base_url() {
  return untrailingslashit( plugin_dir_url( __FILE__ ) );
} // end air_helper_base_url

/**
 * Get server hostnames that indicate that the site is in care plan.
 *
 * @since  5.0.0
 */
function air_helper_get_care_plan_hostnames() {
  return apply_filters( 'air_helper_care_plan_hostnames', [
    'craft' => true,
    'ghost' => true,
    'slash' => true,
    'soen'  => true,
    'messa' => true,
  ] );
} // end air_helper_get_care_plan_hostnames

/**
 * Check if site belongs to care plan.
 *
 * @return boolean True if site has care plan, otherwise false.
 * @since  5.0.0
 */
function air_helper_site_has_care_plan() {
  $hostnames = air_helper_get_care_plan_hostnames();

  if ( 'development' !== wp_get_environment_type() && ! array_key_exists( php_uname( 'n' ), $hostnames ) ) {
    return false;
  }

  return apply_filters( 'air_helper_site_has_care_plan', true );
} // end air_helper_site_has_care_plan

/**
 *  Remove deactivate from air helper plugin actions.
 *  Modify actions with `air_helper_plugin_action_links` filter.
 *
 *  @since  1.5.0
 */
add_filter( 'plugin_action_links', 'air_helper_remove_deactivation_link', 10, 4 );
function air_helper_remove_deactivation_link( $actions, $plugin_file, $plugin_data, $context ) { // phpcs:ignore
  if ( plugin_basename( __FILE__ ) === $plugin_file && array_key_exists( 'deactivate', $actions ) ) {
    unset( $actions['deactivate'] );
  }

  return apply_filters( 'air_helper_plugin_action_links', $actions, $plugin_file );
} // end air_helper_remove_deactivation_link

/**
 *  Remove delete and deactivate from plugin bulk actions.
 *  Modify actions with `air_helper_plugins_bulk_actions` filter.
 *
 *  @since  1.5.0
 */
add_filter( 'bulk_actions-plugins', 'air_helper_modify_plugins_bulk_actions' );
function air_helper_modify_plugins_bulk_actions( $actions ) {
  unset( $actions['delete-selected'] );
  unset( $actions['deactivate-selected'] );

  return apply_filters( 'air_helper_plugins_bulk_actions', $actions );
} // end air_helper_modify_plugins_bulk_actions

/**
 *  Check if active theme is based on Air.
 *
 *  @since  0.1.0
 */
add_action( 'after_setup_theme', 'air_helper_are_we_airless' );
function air_helper_are_we_airless() {
  if ( ! defined( 'AIR_VERSION' ) && ! defined( 'AIR_LIGHT_VERSION' ) ) {
    add_action( 'admin_notices', 'air_helper_we_are_airless' );
  }
} // end air_helper_are_we_airless

/**
 *  Show warning notice when current theme is not based on Air.
 *
 *  @since  0.1.0
 */
function air_helper_we_are_airless() {
  $class = 'notice notice-warning is-dismissible';
  $message = __( 'Active theme seems not to be Air based. Some functionality of Air helper plugin may not work, since it\'s intended to use with Air based themes.', 'air-helper' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
} // end air_helper_we_are_airless

/**
 * Check if current user is allowed to see / enter certain views or
 * functionalities based on their user account email domain or
 * override in user meta.
 *
 * @param  string $allow View / functionality to allow
 * @return boolean       boolean true if allowed
 * @since  2.17.0
 */
function air_helper_allow_user_to( $allow = null ) {
  $current_user = get_current_user_id();
  $user = new WP_User( $current_user );

  $domain = apply_filters( 'air_helper_allow_user_to_domain', 'dude.fi' );
  $domain = apply_filters( "air_helper_allow_user_to_{$allow}_domain", $domain );

  // Backwards compatibility
  if ( 'acf' === $allow ) {
    $domain = apply_filters( 'air_helper_dont_hide_acf_from_domain', $domain );
  }

  // Backwards compatibility
  if ( 'plugins' === $allow ) {
    $domain = apply_filters( 'air_helper_dont_remove_plugins_admin_menu_link_from_domain', $domain );
  }

  if ( strpos( $user->user_email, "@{$domain}" ) !== false ) {
    return true;
  }

  $meta_override = get_user_meta( $user->ID, "_airhelper_admin_show_{$allow}", true );
  if ( 'true' === $meta_override ) {
    return true;
  }

  return false;
} // end air_helper_allow_user_to
