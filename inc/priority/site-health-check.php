<?php
/**
 * Site health check modifications.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:02:19
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:03:15
 *
 * @package air-helper
 */

/**
 *  We take care of multiple things, user does not need to know all the details.
 *
 *  @since  1.10.0
 */
add_filter( 'site_status_tests', 'air_helper_remove_status_tests' );
function air_helper_remove_status_tests( $tests ) {
  // We take care of server requirements.
  unset( $tests['direct']['php_version'] );
  unset( $tests['direct']['sql_server'] );
  unset( $tests['direct']['php_extensions'] );
  unset( $tests['direct']['utf8mb4_support'] );

  // We provide the updates.
  unset( $tests['direct']['wordpress_version'] );
  unset( $tests['direct']['plugin_version'] );
  unset( $tests['direct']['theme_version'] );
  unset( $tests['async']['background_updates'] );

  return $tests;
} // end air_helper_remove_status_tests

/**
 *  We take care of multiple things, user does not need to know all the details.
 *
 *  @since  1.10.0
 */
add_filter( 'debug_information', 'air_helper_remove_debug_information' );
function air_helper_remove_debug_information( $debug_info ) {
  unset( $debug_info['wp-server'] );
  unset( $debug_info['wp-paths-sizes'] );
  unset( $debug_info['wp-database'] );
  unset( $debug_info['wp-constants'] );
  unset( $debug_info['wp-filesystem'] );
  unset( $debug_info['wp-media'] );

  return $debug_info;
} // end air_helper_remove_debug_information
