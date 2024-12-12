<?php
/**
 * Collection of miscellaneous actions.
 *
 * @package air-helper
 */

/**
 * Add instant.page just-in-time preloading script to footer.
 *
 * Disable using `remove_action( 'wp_enqueue_scripts', 'air_helper_enqueue_instantpage_script', 50 )`
 *
 * @since 5.0.0
 */
add_action( 'wp_enqueue_scripts', 'air_helper_enqueue_instantpage_script' );
function air_helper_enqueue_instantpage_script() {
  wp_enqueue_script( 'instantpage', air_helper_base_url() . '/assets/js/instantpage.min.js', [], '5.2.0', true );
} // end air_helper_enqueue_instantpage_script

/**
 * Disable cache for Relevanssi related posts output on development environment for easier development.
 *
 * @since 2.15.0
 */
add_filter( 'relevanssi_disable_related_cache', 'air_helper_disable_relevanssi_related_cache_on_dev' );
function air_helper_disable_relevanssi_related_cache_on_dev( $cache ) {
  if ( 'development' === wp_get_environment_type() ) {
    return true;
  }

  return $cache;
} // end air_helper_disable_relevanssi_related_cache_on_dev
