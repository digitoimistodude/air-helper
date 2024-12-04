<?php
/**
 * WordPress REST API actions.
 *
 * @package air-helper
 */

/**
 *  Disable REST API users endpoint.
 *
 *  Turn off by using `remove_filter( 'rest_endpoints', 'air_helper_disable_rest_endpoints' )`
 *
 *  @since  0.1.0
 */
add_filter( 'rest_endpoints', 'air_helper_disable_rest_endpoints' );
function air_helper_disable_rest_endpoints( $endpoints ) {
  if ( current_user_can( 'edit_posts' ) ) {
    return $endpoints;
  }

  if ( isset( $endpoints['/wp/v2/users'] ) ) {
    unset( $endpoints['/wp/v2/users'] );
  }

  if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
    unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
  }

  return $endpoints;
} // end air_helper_disable_rest_endpoints
