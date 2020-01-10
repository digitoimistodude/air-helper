<?php
/**
 * Plugin Name: Air helper
 * Plugin URI: https://github.com/digitoimistodude/air-helper
 * Description: This plugin extends themes based on Air by adding useful hooks, functions and basic things for WooCommerce.
 * Version: 1.12.1
 * Author: Digitoimisto Dude Oy, Timi Wahalahti
 * Author URI: https://www.dude.fi
 * Requires at least: 4.7
 * Tested up to: 5.2
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Text Domain: air-helper
 * Domain Path: /languages
 *
 * @package air-helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 *  Get current version of plugin. Version is semver without extra marks, so it can be used as a int.
 *
 *  @since  1.6.0
 *  @return integer  current version of plugin
 */
function air_helper_version() {
	return 1121;
}

/**
 * Require helpers for this plugin.
 *
 * @since  2.0.0
 */
require 'inc/plugin-helpers.php';

/**
 *  Github updater for this plugin.
 *
 *  @since  0.1.0
 */
require 'plugin-update-checker/plugin-update-checker.php';
$update_checker = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/digitoimistodude/air-helper', __FILE__, 'air-helper' );

/**
 *  Load localization helpers and Polylang fallbacks.
 *  Turn off by using `remove_action( 'init', 'air_helper_localization_helpers' )`
 *
 *  @since  1.4.0
 */
add_action( 'init', 'air_helper_localization_helpers' );
function air_helper_localization_helpers() {
	require_once air_helper_base_path() . '/inc/localization.php';
} // end air_helper_localization_helpers

/**
 *  Require priority files.
 */
add_action( 'init', 'air_helper_priority_fly', 5 );
function air_helper_priority_fly() {
  // Load textdomain for few translations in this plugin
	load_plugin_textdomain( 'air-helper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

  // Hook & filter files
	require_once air_helper_base_path() . '/inc/priority/security.php';
  require_once air_helper_base_path() . '/inc/priority/site-health-check.php';
  require_once air_helper_base_path() . '/inc/priority/misc.php';
} // end air_helper_priority_fly

/**
 *  Require files containing our preferences.
 */
add_action( 'init', 'air_helper_fly', 998 );
function air_helper_fly() {
  // Function files
  require_once air_helper_base_path() . '/functions/archives.php';
  require_once air_helper_base_path() . '/functions/checks.php';
  require_once air_helper_base_path() . '/functions/pagination.php';
  require_once air_helper_base_path() . '/functions/misc.php';

  // Hook & filter files
	require_once air_helper_base_path() . '/inc/hooks.php';
	require_once air_helper_base_path() . '/inc/misc.php';
	require_once air_helper_base_path() . '/inc/image-lazyload.php';

  require_once air_helper_base_path() . '/inc/mail.php';
  require_once air_helper_base_path() . '/inc/archive.php';
} // end air_helper_fly

/**
 * Require files needed on admin side of the site.
 */
add_action( 'admin_init', 'air_helper_admin_fly' );
function air_helper_admin_fly() {
  require_once air_helper_base_path() . '/inc/admin/notifications.php';
  require_once air_helper_base_path() . '/inc/admin/access.php';
  require_once air_helper_base_path() . '/inc/admin/acf.php';
  require_once air_helper_base_path() . '/inc/admin/adminbar.php';
  require_once air_helper_base_path() . '/inc/admin/dashboard.php';
  require_once air_helper_base_path() . '/inc/admin/help-widget.php';
  require_once air_helper_base_path() . '/inc/admin/updates.php';
} // end air_helper_admin_fly

/**
 *  Plugin activation hook to save current version for reference in what version activation happened.
 *  Check if deactivation without version option is apparent, then do not save current version for
 *  maintaining backwards compatibility.
 *
 *  @since  1.6.0
 */
register_activation_hook( __FILE__, 'air_helper_activate' );
function air_helper_activate() {
  $deactivated_without = get_option( 'air_helper_deactivated_without_version' );

  if ( 'true' !== $deactivated_without ) {
    update_option( 'air_helper_activated_at_version', air_helper_version() );
  }
} // end air_helper_activate

/**
 *  Maybe add option if activated version is not yet saved.
 *  Helps to maintain backwards compatibility.
 *
 *  @since  1.6.0
 */
register_deactivation_hook( __FILE__, 'air_helper_deactivate' );
add_action( 'admin_init', 'air_helper_deactivate' );
function air_helper_deactivate() {
  $activated_version = get_option( 'air_helper_activated_at_version' );

  if ( ! $activated_version ) {
    update_option( 'air_helper_deactivated_without_version', 'true', false );
  }
} // end air_helper_deactivate
