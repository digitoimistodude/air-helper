<?php
/**
 * Custom functions related to pagination.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:47:21
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 11:54:08
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_next_page_id' ) ) {
  /**
   *  Get ID of next page.
   *
   *  @since  1.5.1
   *  @param  integer $id Page which next to get, defaults to current page.
   *  @return mixed       False if no next page, id if there is.
   */
  function get_next_page_id( $id = 0 ) {
    if ( empty( $id ) ) {
      $id = get_the_id();
    }

    $cache_key = apply_filters( 'get_next_page_id_cache_key', "get_next_page_id_for_{$id}", $id );

    // Check if result is cached and if, return cached version
    $next_page_id = get_transient( $cache_key );
    if ( ! empty( $next_page_id ) ) {
      return absint( $next_page_id );
    }

    $next_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages( [
      'post_type'   => $post->post_type,
      'child_of'    => $post->post_parent,
      'parent'      => $post->post_parent,
      'sort_column' => 'menu_order post_title',
      'sort_order'  => 'ASC',
    ] );

    // Count pages.
    $page_count = count( $get_pages );

    for ( $p = 0; $p < $page_count; $p++ ) {
      // Get the array key for our entry.
      if ( isset( $get_pages[ $p ] ) && $id === $get_pages[ $p ]->ID ) {
        break;
      }
    }

    // Assign our next key.
    $next_key = $p + 1;

    // If there isn't a value assigned for the previous key, go all the way to the end.
    if ( isset( $get_pages[ $next_key ] ) ) {
      $next_page_id = $get_pages[ $next_key ]->ID;
    }

    // Save to cache
    set_transient( $cache_key, $next_page_id, apply_filters( 'get_next_page_id_cache_lifetime', MINUTE_IN_SECONDS * 30 ) );

    return $next_page_id;
  } // end get_next_page_id
} // end if

if ( ! function_exists( 'get_prev_page_id' ) ) {
  /**
   *  Get ID of previous page.
   *
   *  @since  1.5.1
   *  @param  integer $id Page which previous to get, defaults to current page.
   *  @return mixed       False if no previous page, id if there is.
   */
  function get_prev_page_id( $id = 0 ) {
    if ( empty( $id ) ) {
      $id = get_the_id();
    }

    $cache_key = apply_filters( 'get_prev_page_id_cache_key', "get_prev_page_id_for_{$id}", $id );

    // Check if result is cached and if, return cached version
    $prev_page_id = get_transient( $cache_key );
    if ( ! empty( $prev_page_id ) ) {
      return absint( $prev_page_id );
    }

    $prev_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages( [
      'post_type'   => $post->post_type,
      'child_of'    => $post->post_parent,
      'parent'      => $post->post_parent,
      'sort_column' => 'menu_order post_title',
      'sort_order'  => 'ASC',
    ] );

    // Count pages.
    $page_count = count( $get_pages );

    for ( $p = 0; $p < $page_count; $p++ ) {
      // Get the array key for our entry.
      if ( isset( $get_pages[ $p ] ) && $id === $get_pages[ $p ]->ID ) {
        break;
      }
    }

    // Assign our next key.
    $prev_key = $p - 1;
    $last_key = $page_count - 1;

    // If there isn't a value assigned for the previous key, go all the way to the end.
    if ( isset( $get_pages[ $prev_key ] ) ) {
      $prev_page_id = $get_pages[ $prev_key ]->ID;
    }

    // Save to cache
    set_transient( $cache_key, $prev_page_id, apply_filters( 'get_prev_page_id_cache_lifetime', MINUTE_IN_SECONDS * 30 ) );

    return $prev_page_id;
  } // end get_prev_page_id
} // end if
