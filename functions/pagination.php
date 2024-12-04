<?php
/**
 * Custom functions related to pagination.
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

    $next_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages(
      [
        'post_type'   => $post->post_type,
        'child_of'    => $post->post_parent,
        'parent'      => $post->post_parent,
        'sort_column' => 'menu_order,post_title',
        'sort_order'  => 'ASC',
      ]
    );

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
   *  @param  integer $id Page which previous to get, defaults to current page.
   *  @return mixed       False if no previous page, id if there is.
   */
  function get_prev_page_id( $id = 0 ) {
    if ( empty( $id ) ) {
      $id = get_the_id();
    }

    $prev_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages(
      [
        'post_type'   => $post->post_type,
        'child_of'    => $post->post_parent,
        'parent'      => $post->post_parent,
        'sort_column' => 'menu_order,post_title',
        'sort_order'  => 'ASC',
      ]
    );

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

if ( ! function_exists( 'get_first_page_id' ) ) {
  /**
   *  Get ID of first page.
   *
   *  @since  2.13.0
   *  @param  integer $id Page from which to count first to get, defaults to current page.
   *  @return mixed       False if no first page, id if there is.
   */
  function get_first_page_id( $id = 0 ) {
    if ( empty( $id ) ) {
      $id = get_the_id();
    }

    $first_page_id = false;

    // Get all pages under this section
    $post = get_post( $id );
    $get_pages = get_pages(
      [
        'post_type'   => $post->post_type,
        'child_of'    => $post->post_parent,
        'parent'      => $post->post_parent,
        'sort_column' => 'menu_order,post_title',
        'sort_order'  => 'ASC',
      ]
    );

    if ( is_array( $get_pages ) ) {
      $first_page_id = $get_pages[0]->ID;
    }

    return $first_page_id;
  } // end get_first_page_id
} // end if
