<?php
/**
 * Dashboard
 *
 * Customize wp-admin dashboard (index.php).
 *
 * @package air-helper
 */

/**
 * Remove welcome panel
 *
 * @since  1.7.0
 */
add_action( 'admin_init', function () {
  if ( is_admin() ) {
    remove_action( 'welcome_panel', 'wp_welcome_panel' );
  }
}, 999 );

/**
 *  Remove some boxes from dashboard.
 *
 *  Turn off completely by using:
 *  remove_action( 'wp_dashboard_setup', 'air_helper_clear_admin_dashboard' );
 *
 *  Modify which boxes are hidden/shown using:
 *  add_filter( 'air_helper_clear_admin_dashboard_boxes', function( $remove_boxes ) {
 *    // Show Yoast SEO widget
 *    $key = array_search( 'wpseo-dashboard-overview', $remove_boxes['normal'] );
 *    if ( false !== $key ) {
 *        unset( $remove_boxes['normal'][$key] );
 *    }
 *
 *    // Hide additional widget
 *    $remove_boxes['normal'][] = 'my_custom_widget';
 *
 *    return $remove_boxes;
 *  } );
 *
 *  @since 1.7.0
 */
add_action( 'wp_dashboard_setup', 'air_helper_clear_admin_dashboard', 99 );
function air_helper_clear_admin_dashboard() {
	$remove_boxes = [
		'normal' => [
      'dashboard_right_now',
      'dashboard_recent_comments',
      'dashboard_incoming_links',
      'dashboard_activity',
      'dashboard_plugins',
      'dashboard_site_health',
      'sendgrid_statistics_widget',
      'wpseo-dashboard-overview', // Yoast
      'rg_forms_dashboard', // Gravity forms
      'dashboard_rediscache',
      'dashboard_objectcache',
      'dashboard_php_nag',
      'yith_dashboard_products_news', // YITH plugins
      'yith_dashboard_blog_news', // YITH plugins
      'wpseo-wincher-dashboard-overview',
      'tinypng_dashboard_widget',
      'themeisle', // Optimole
      'wpforms_reports_widget_pro', // WP Forms reports
		],
		'side' => [
			'dashboard_quick_press',
			'dashboard_recent_drafts',
			'dashboard_primary',
			'dashboard_secondary',
		],
	];

	// Allow filtering which boxes to hide or show
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
