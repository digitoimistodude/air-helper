<?php
/**
 * Functions to help builing customized archives.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:49:34
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 15:50:57
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_posts_array' ) ) {
  /**
   *  Get posts in key=>value array.
   *
   *  @since  1.4.2
   *  @param  array  $args       Arguments passed to get_posts function.
   *  @param  string $return_key Which field to use as a key in return.
   *  @return array              {$return_key}=>post_title array of posts.
   */
  function get_posts_array( $args = array(), $return_key = 'ID' ) {
    $return = array();
    $defaults = array(
      'posts_per_page'  => 100,
      'orderby'         => 'title',
      'order'           => 'DESC',
    );

    $args = wp_parse_args( $args, $defaults );
    $posts = get_posts( $args );

    foreach ( $posts as $post ) {
      $return[ $post->{ $return_key } ] = $post->post_title;
    }

    return $return;
  } // end get_posts_array
} // end if

if ( ! function_exists( 'get_post_years' ) ) {
  /**
   *  Get years where there are posts.
   *
   *  @since  1.6.0
   *  @param  string  $post_type post type to get post years, defaults to post.
   *  @return array              array containing years where there are posts.
   */
  function get_post_years( $post_type = 'post' ) {
    $cache_key = apply_filters( "get_{$post_type}_years_result_key", "get_{$post_type}_years_result" );

    // Check if result is cached and if, return cached version.
    $result = get_transient( $cache_key );
    if ( ! empty( $result ) ) {
      return $result;
    }

    global $wpdb;
    $result = array();

    // Do databse query to get years.
    $years = $wpdb->get_results( "SELECT YEAR(post_date) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$post_type}' GROUP BY YEAR(post_date) DESC", ARRAY_N );

    // Loop result.
    if ( is_array( $years ) && count( $years ) > 0 ) {
      foreach ( $years as $year ) {
        $result[] = $year[0];
      }
    }

    $result = apply_filters( "get_{$post_type}_years_result", $result, $post_type );

    // Save result to cache for 30 minutes.
    set_transient( $cache_key, $result, MINUTE_IN_SECONDS * 30 );

    return $result;
  } // end get_post_years
} // end if

if ( ! function_exists( 'get_post_months_by_year' ) ) {
  /**
   *  Get months where there are posts in spesific year.
   *
   *  @since  1.6.0
   *  @param  string  $year      year to get posts, defaults to current year.
   *  @param  string  $post_type post type to get post years, defaults to post.
   *  @return array              array containing months where there are posts.
   */
  function get_post_months_by_year( $year = '', $post_type = 'post' ) {
    // Use current year if not defined.
    if ( empty( $year ) ) {
      $year = date( 'Y' );
    }

    // Check if result is cached and if, return cached version.
    $result = get_transient( "get_{$post_type}_months_by_year_{$year}_result" );
    if ( ! empty( $result ) ) {
      return $result;
    }

    global $wpdb;
    $result = array();

    // Do databse query to get years.
    $months = $wpdb->get_results( "SELECT DISTINCT MONTH(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = '{$post_type}' AND YEAR(post_date) = '".$year."' ORDER BY post_date DESC", ARRAY_N );

    // Loop result.
    if ( is_array( $months ) && count( $months ) > 0 ) {
      foreach ( $months as $month ) {
        $result[] = $month[0];
      }
    }

    // Save result to cache for 30 minutes.
    set_transient( "get_{$post_type}_months_by_year_{$year}_result", $result, MINUTE_IN_SECONDS * 30 );

    return $result;
  } // end get_post_months_by_year
} // end if
