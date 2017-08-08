<?php
/**
 * Plugin Name: Air helper
 * Plugin URI: https://github.com/digitoimistodude/air-helper
 * Description: This plugin extends themes based on Air by adding useful hooks, functions and basic things for WooCommerce.
 * Version: 1.0.3
 * Author: Digitoimisto Dude Oy, Timi Wahalahti
 * Author URI: https://www.dude.fi
 * Requires at least: 4.7
 * Tested up to: 4.7.5
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
 *  Custom GitHub updater for this plugin.
 *
 *  @since  0.1.0
 */
require 'plugin-update-checker/plugin-update-checker.php';
$update_checker = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/digitoimistodude/air-helper', __FILE__, 'air-helper' );

/**
 *  Wrapper function to get real base path for this plugin.
 *
 *  @since  0.1.0
 *  @return string  Path to this plugin
 */
function air_helper_base_path() {
	return untrailingslashit( plugin_dir_path( __FILE__ ) );
}

/**
 *  Check if active theme is based on Air.
 *
 *  @since  0.1.0
 */
function air_helper_are_we_airless() {
	if ( ! defined( 'AIR_VERSION' ) ) {
		add_action( 'admin_notices', 'air_helper_we_are_airless' );
	}
}
add_action( 'after_setup_theme', 'air_helper_are_we_airless' );

/**
 *  Show warning notice when current theme is not based on Air.
 *
 *  @since  0.1.0
 */
function air_helper_we_are_airless() {
	$class = 'notice notice-warning is-dismissible';
	$message = __( 'Active theme seems not to be Air based. Some functionality of Air helper plugin may not work, since it\'s intended to use with Air based themes.', 'air-helper' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 *  Test if current theme support WooCommerce and require WC spesific
 *  things if so.
 *
 *  @since  0.1.0
 */
function air_helper_maybe_woocommerce() {
	if ( current_theme_supports( 'woocommerce' ) ) {
		require_once air_helper_base_path() . '/inc/woocommerce.php';
	}
}
add_action( 'init', 'air_helper_maybe_woocommerce' );

/**
 *  Require files containing our preferences.
 */
require_once air_helper_base_path() . '/inc/hooks.php';
require_once air_helper_base_path() . '/inc/misc.php';
