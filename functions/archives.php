<?php
/**
 * Functions to help builing customized archives.
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_posts_array' ) ) {
  /**
   *  Get posts in key=>title array.
   *
   *  @since  1.4.2
   *  @param  array  $args       Arguments passed to get_posts function.
   *  @param  string $return_key Which field to use as a key in return.
   *  @return array              {$return_key}=>post_title array of posts.
   */
  function get_posts_array( $args = [], $return_key = 'ID' ) {
    $cache_key_hash = sprintf( '%u', crc32( serialize( $args ) . $return_key ) ); // phpcs:ignore
    $cache_key = apply_filters( 'get_posts_array_cache_key', "get_posts_array_{$cache_key_hash}", $args, $return_key );

    // Check if result is cached and if, return cached version
    $return = get_transient( $cache_key );
    if ( ! empty( $return ) ) {
      return $return;
    }

    $return = [];
    $defaults = [
      'posts_per_page'  => 100,
    ];

    $args = wp_parse_args( $args, $defaults );
    $posts = get_posts( $args );

    foreach ( $posts as $post ) {
      $return[ $post->{ $return_key } ] = $post->post_title;
    }

    $return = apply_filters( 'get_posts_array', $return, $args, $return_key );

    // Save result to cache
    set_transient( $cache_key, $return, apply_filters( 'get_posts_array_cache_lifetime', MINUTE_IN_SECONDS * 30 ) );

    return $return;
  } // end get_posts_array
} // end if

if ( ! function_exists( 'get_post_years' ) ) {
  /**
   *  Get years where there are posts.
   *
   *  @since  1.6.0
   *  @param  string $post_type  post type to get post years, defaults to post.
   *  @return array              array containing years where there are posts.
   */
  function get_post_years( $post_type = 'post' ) {
    $cache_key = apply_filters( "get_{$post_type}_years_result_key", "get_{$post_type}_years_result" );

    // Check if result is cached and if, return cached version
    $return = get_transient( $cache_key );
    if ( ! empty( $return ) ) {
      return $return;
    }

    global $wpdb;
    $return = [];

    // Do database query to get years
    $years = $wpdb->get_results( $wpdb->prepare( "SELECT YEAR(post_date) FROM %1s WHERE post_status = 'publish' AND post_type = %s GROUP BY YEAR(post_date) DESC", $wpdb->posts, $post_type ), ARRAY_N ); // phpcs:ignore

    // Loop result
    if ( is_array( $years ) && count( $years ) > 0 ) {
      foreach ( $years as $year ) {
        $return[] = absint( $year[0] );
      }
    }

    $return = apply_filters( "get_{$post_type}_years_result", $return, $post_type );

    // Save result to cache for 30 minutes
    set_transient( $cache_key, $return, apply_filters( 'get_post_years_cache_lifetime', MINUTE_IN_SECONDS * 30 ) );

    return $return;
  } // end get_post_years
} // end if

if ( ! function_exists( 'get_post_months_by_year' ) ) {
  /**
   *  Get months where there are posts in spesific year.
   *
   *  @since  1.6.0
   *  @param  string $year       year to get posts, defaults to current year.
   *  @param  string $post_type  post type to get post years, defaults to post.
   *  @return array              array containing months where there are posts.
   */
  function get_post_months_by_year( $year = '', $post_type = 'post' ) {
    // Use current year if not defined
    if ( empty( $year ) ) {
      $year = date( 'Y' ); // phpcs:ignore
    }

    $cache_key = "get_{$post_type}_months_by_year_{$year}_result";

    // Check if result is cached and if, return cached version
    $return = get_transient( $cache_key );
    if ( ! empty( $return ) ) {
      return $return;
    }

    global $wpdb;
    $return = [];

    // Do database query to get years
    $months = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT MONTH(post_date) FROM %1s WHERE post_status = 'publish' AND post_type = %s AND YEAR(post_date) = %s ORDER BY post_date DESC", $wpdb->posts, $post_type, $year ), ARRAY_N ); // phpcs:ignore

    // Loop result
    if ( is_array( $months ) && count( $months ) > 0 ) {
      foreach ( $months as $month ) {
        $return[] = absint( $month[0] );
      }
    }

    // Save result to cache for 30 minutes
    set_transient( $cache_key, $return, apply_filters( 'get_post_months_by_year_cache_lifetime', MINUTE_IN_SECONDS * 30 ) );

    return $return;
  } // end get_post_months_by_year
} // end if
