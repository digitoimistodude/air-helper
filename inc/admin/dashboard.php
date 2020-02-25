<?php
/**
 * Customize wp-admin dashboard (index.php).
 *
 * @Author: 						Timi Wahalahti, Digitoimisto Dude Oy (https://dude.fi)
 * @Date:   						2018-11-13 18:06:44
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 12:42:42
 *
 * @package air-helper
 */

/**
 * Remove welcome panel
 *
 * @since  1.7.0
 */
remove_action( 'welcome_panel', 'wp_welcome_panel' );

/**
 *  Remove some boxes from dashboard.
 *
 *  Turn off by using `remove_action( 'wp_dashboard_setup', 'air_helper_clear_admin_dashboard' )`
 *  Modify with `add_filter( 'air_helper_clear_admin_dashboard_boxes', 'myprefix_air_helper_clear_admin_dashboard_boxes' )`
 *
 *  @since 1.7.0
 */
add_action( 'wp_dashboard_setup', 'air_helper_clear_admin_dashboard', 99 );
function air_helper_clear_admin_dashboard() {
	$remove_boxes = [
		'normal'	=> [
			'dashboard_right_now',
			'dashboard_recent_comments',
			'dashboard_incoming_links',
			'dashboard_activity',
			'dashboard_plugins',
			'sendgrid_statistics_widget',
			'wpseo-dashboard-overview', // yoast
			'rg_forms_dashboard', // gravity forms
		],
		'side'		=> [
			'dashboard_quick_press',
			'dashboard_recent_drafts',
			'dashboard_primary',
			'dashboard_secondary',
		],
	];

	// Allow filtering which boxes to hide
	$remove_boxes = apply_filters( 'air_helper_clear_admin_dashboard_boxes', $remove_boxes );

	if ( ! empty( $remove_boxes ) ) {

		// Hide normal boxes
		if ( isset( $remove_boxes['normal'] ) ) {
			foreach ( $remove_boxes['normal'] as $box ) {
				remove_meta_box( $box, 'dashboard', 'normal' );
			}
		}

		// Hide side boxes
		if ( isset( $remove_boxes['side'] ) ) {
			foreach ( $remove_boxes['side'] as $box ) {
				remove_meta_box( $box, 'dashboard', 'side' );
			}
		}
	}
} // end air_helper_clear_admin_dashboard
