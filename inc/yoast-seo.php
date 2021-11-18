<?php
/**
 * Yoast SEO plugin actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:30:25
 * @Last Modified by:   Elias Kautto
 * @Last Modified time: 2021-11-18 15:12:27
 *
 * @package air-helper
 */

/**
 *  Set Yoast SEO plugin metabox priority to low.
 *
 *  Turn off by using `remove_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' )`
 *
 *  @since  0.1.0
 */
add_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' );
function air_helper_lowpriority_yoastseo() {
  return 'low';
} // end air_helper_lowpriority_yoastseo

add_filter( 'wpseo_enhanced_slack_data', 'air_helper_maybe_remove_author_from_enhanced_data', 10, 2 );
function air_helper_maybe_remove_author_from_enhanced_data( $data, $presentation ) {
  if ( ! isset( $presentation->source->post_author ) ) {
    return $data;
  }

  $user = new WP_User( $presentation->source->post_author );
  $domain = apply_filters( 'air_helper_maybe_remove_author_from_enhanced_data_domain', 'dude.fi' );

  if ( strpos( $user->user_email, "@{$domain}" ) === false ) {
    return $data;
  }

  unset( $data[ __( 'Written by', 'wordpress-seo' ) ] );

  return $data;
} // end air_helper_maybe_remove_author_from_enhanced_data
