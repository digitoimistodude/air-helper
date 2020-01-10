<?php
/**
 * Custom functions related to pagination.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:47:21
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 15:49:15
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_next_page_id' ) ) {
  /**
   *  Get ID of next page.
   *
   *  @since  1.5.1
   *  @param  integer $id page which next to get.
   *  @return mixed       false if no next page, id if there is.
   */
  function get_next_page_id( $id = 0 ) {
    $next_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages( array(
      'post_type'   => $post->post_type,
      'child_of'    => $post->post_parent,
      'parent'      => $post->post_parent,
      'sort_column' => 'menu_order',
      'sort_order'  => 'asc',
    ) );

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

    return $next_page_id;
  } // end get_next_page_id
} // end if

if ( ! function_exists( 'get_prev_page_id' ) ) {
  /**
   *  Get ID of previous page.
   *
   *  @since  1.5.1
   *  @param  integer $id page which previous to get.
   *  @return mixed       false if no previous page, id if there is.
   */
  function get_prev_page_id( $id = 0 ) {
    $prev_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages( array(
      'post_type'   => $post->post_type,
      'child_of'    => $post->post_parent,
      'parent'      => $post->post_parent,
      'sort_column' => 'menu_order',
      'sort_order'  => 'asc',
    ) );

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

    return $prev_page_id;
  } // end get_prev_page_id
} // end if
