<?php
/**
 * Collection of miscellaneous functions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:53:45
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-13 15:22:50
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_icons_for_user' ) ) {
  /**
   *  Get list of icons which are available for user.
   *
   *  @since  1.4.0
   *  @return array  Array of icons available.
   */
  function get_icons_for_user() {
    $icons = [];
    $files = glob( get_template_directory() . '/svg/foruser/*.svg' );

    foreach ( $files as $file ) {
      $raw_filename = explode( '/', $file );
      $raw_filename = end( $raw_filename );
      $filename = strstr( $raw_filename, '.', true );
      $filename = str_replace( '-', ' ', $filename );
      $filename = str_replace( '_', ' ', $filename );
      $icons[ $raw_filename ] = ucfirst( $filename );
    }

    return $icons;
  } // end get_icons_for_user
} // end if

if ( ! function_exists( 'wp_parse_args_dimensional' ) ) {
  /**
   *  Similar to wp_parse_args() just extended to work with multidimensional arrays.
   *
   *  @since  1.7.0
   *  @param  array $a  Value to merge with $defaults.
   *  @param  array $b  Optional, array that serves as the defaults. Defaults to empty.
   *  @return array     Merged user defined values with defaults.
   */
  function wp_parse_args_dimensional( &$a, $b = '' ) {
    $a = (array) $a;
    $b = (array) $b;
    $result = $b;

    foreach ( $a as $k => &$v ) {
      if ( is_array( $v ) && isset( $result[ $k ] ) ) {
        $result[ $k ] = ilokivi_wp_parse_args( $v, $result[ $k ] );
      } else {
        $result[ $k ] = $v;
      }
    }

    return $result;
  } // end wp_parse_args_dimensional
} // end if

if ( ! function_exists( 'get_the_sentence_excerpt' ) ) {
  /**
   *  Get excerpt with custom length of three sentences.
   *
   *  @since  1.10.0
   *  @param  integer $length  how many sentences to return. Default three.
   *  @param  string  $excerpt The excerpt. Default excerpt of global $post.
   *  @return string           Excerpt.
   */
  function get_the_sentence_excerpt( $length = 3, $excerpt = null ) {
    if ( ! $excerpt ) {
      $excerpt = get_the_excerpt();
    }

    $split = preg_split( '/(\. |\!|\?)/', $excerpt, $length, PREG_SPLIT_DELIM_CAPTURE );
    $new_excerpt = implode( '', array_slice( $split, 0, $length + 1 ) );

    return $new_excerpt;
  } // end get_the_sentence_excerpt
} // end if
