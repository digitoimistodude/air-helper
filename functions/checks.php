<?php
/**
 * Collection of functions to make different kind of checks.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:51:13
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 11:35:31
 *
 * @package air-helper
 */

if ( ! function_exists( 'post_exists_id' ) ) {
  /**
   *  Check if post exists by ID.
   *
   *  @since  1.4.0
   *  @param  integer $post_id post ID.
   *  @return boolean          true / false whether the post exists.
   */
  function post_exists_id( $post_id ) {
    if ( empty( $post_id ) ) {
      return false;
    }

    return is_string( get_post_status( $post_id ) );
  } // end post_exists_id
} // end if

if ( ! function_exists( 'has_content' ) ) {
  /**
   *  Check if post has main content.
   *
   *  @since  1.4.2
   *  @param  integer $post_id post ID.
   *  @return boolean          true / false whether the post content exists.
   */
  function has_content( $post_id = null ) {
    if ( isset( $post_id ) ) {
      $content = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
    } else {
      $content = get_the_content();
    }

    return ! empty( $content );
  } // end has_content
} // end if

if ( ! function_exists( 'has_children' ) ) {
  /**
   *  Check if post has child pages.
   *
   *  @since  1.4.2
   *  @param  integer $post_id  post ID, defaults to current post.
   *  @param  string  $post_type post type, defaults to page or current post type.
   *  @return boolean            true / false whether post has childs.
   */
  function has_children( $post_id = null, $post_type = 'page' ) {
    if ( null === $post_id ) {
      global $post;
      $post_id = $post->ID;
      $post_type = $post->post_type;
    }

    $query = new WP_Query( [
      'post_parent' => $post_id,
      'post_type'   => $post_type,
    ] );

    return $query->have_posts();
  } // end has_children
} // end if
