<?php
/**
 * Site health check modifications.
 *
 * @package air-helper
 */

/**
 * We take care of multiple things, but allow dude.fi users to see the details.
 *
 * There are multiple ways to allow users to see health checks:
 *
 * 1. Using domain filter for all features:
 * add_filter('air_helper_allow_user_to_domain', function($domain) {
 *   return 'yourdomain.com'; // Changes allowed domain for all features
 * });
 *
 * 2. Using specific domain filter for health checks:
 * add_filter('air_helper_allow_user_to_health-check_domain', function($domain) {
 *   return 'yourdomain.com'; // Changes allowed domain only for health checks
 * });
 *
 * 3. Using user meta override:
 * update_user_meta($user_id, '_airhelper_admin_show_health-check', 'true');
 *
 * 4. Multiple domains using filter:
 * add_filter('air_helper_allow_user_to_health-check_domain', function($domain) {
 *   $allowed_domains = ['dude.fi', 'example.com', 'client.com'];
 *   if (in_array($domain, $allowed_domains)) {
 *     return $domain;
 *   }
 *   return 'dude.fi';
 * });
 *
 * @since  1.10.0
 */
add_filter('site_status_tests', 'air_helper_remove_status_tests');
function air_helper_remove_status_tests( $tests ) {
  // Allow dude.fi admin users and additionally allowed users to see all tests
  if ( air_helper_allow_user_to( 'health-check' ) ) {
		return $tests;
  }

  // We take care of server requirements.
  unset($tests['direct']['php_version']);
  unset($tests['direct']['sql_server']);
  unset($tests['direct']['php_extensions']);
  unset($tests['direct']['utf8mb4_support']);

  // We provide the updates.
  unset($tests['direct']['wordpress_version']);
  unset($tests['direct']['plugin_version']);
  unset($tests['direct']['theme_version']);
  unset($tests['async']['background_updates']);

  return $tests;
}

/**
 * We take care of multiple things, but allow dude.fi users to see the details.
 *
 * @since  1.10.0
 */
add_filter('debug_information', 'air_helper_remove_debug_information');
function air_helper_remove_debug_information( $debug_info ) {
  // Allow dude.fi admin users and additionally allowed users to see all debug information
  if ( air_helper_allow_user_to( 'health-check' ) ) {
		return $debug_info;
  }

  unset($debug_info['wp-server']);
  unset($debug_info['wp-paths-sizes']);
  unset($debug_info['wp-database']);
  unset($debug_info['wp-constants']);
  unset($debug_info['wp-filesystem']);
  unset($debug_info['wp-media']);

  return $debug_info;
}
