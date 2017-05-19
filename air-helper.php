<?php
/**
 * Plugin Name: Air helper
 * Plugin URI: https://github.com/digitoimistodude/air-helper
 * Description: This plugin extends themes based on Air by adding useful hooks, functions and basic things for WooCommerce.
 * Version: 0.1.0
 * Author: Digitoimisto Dude Oy, Timi Wahalahti
 * Author URI: https://www.dude.fi
 * Requires at least: 4.7
 * Tested up to: 4.7.5
 *
 * Text Domain: air-helper
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' )  ) {
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

require_once plugin_dir_path( __FILE__ ) . '/inc/hooks.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/misc.php';
