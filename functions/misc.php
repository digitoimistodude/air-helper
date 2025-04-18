<?php
/**
 * Collection of miscellaneous functions.
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_icons_for_user' ) ) {
  /**
   *  Get list of icons which are available for user.
   *
   *  @since  1.4.0
   *  @param array $args Array of arguments.
   *  @return array  Array of icons available.
   */
  function get_icons_for_user( $args = [] ) {
    $default_args = [
      'show_preview' => false,
      'icon_path'    => '/svg/foruser/',
      'show_empty'   => false,
    ];

    $args = wp_parse_args( $args, $default_args );
    $icons = [];

    if ( $args['show_empty'] && $args['show_preview'] ) {
      $icons[0] = esc_html__( 'No icon', 'air-helper' ) . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#888" d="M0 10a10 10 0 1 1 20 0 10 10 0 0 1-20 0zm16.32-4.9L5.09 16.31A8 8 0 0 0 16.32 5.09zm-1.41-1.42A8 8 0 0 0 3.68 14.91L14.91 3.68z"/></svg>';
    } elseif ( $args['show_empty'] ) {
      $icons[0] = esc_html__( 'No icon', 'air-helper' );
    }

    $files = glob( get_template_directory() . '/' . $args['icon_path'] . '*.svg' );

    foreach ( $files as $file ) {
      $raw_filename = explode( '/', $file );
      $raw_filename = end( $raw_filename );
      $filename = strstr( $raw_filename, '.', true );
      $filename = str_replace( '-', ' ', $filename );
      $filename = str_replace( '_', ' ', $filename );

      // If using the ACF select2 improved UI, show preview icons
      if ( $args['show_preview'] ) {
        ob_start();
        echo esc_html( ucfirst( $filename ) );
        include get_theme_file_path( $args['icon_path'] . $raw_filename );
        $icons[ $raw_filename ] = ob_get_clean();
      } else {
        $icons[ $raw_filename ] = ucfirst( $filename );
      }
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

    $split = preg_split( '/(\. |\!|\?)/', $excerpt, $length + 1, PREG_SPLIT_DELIM_CAPTURE );
    $new_excerpt = implode( '', array_slice( $split, 0, $length * 2 ) );

    return $new_excerpt;
  } // end get_the_sentence_excerpt
} // end if

if ( ! function_exists( 'get_primary_category' ) ) {
  /**
   * Get primary category for post.
   *
   * @since  2.2.0
   * @param  integer $post_id   Which post to get the primary category for, if empty current post is used.
   * @param  string  $taxonomy  From which taxonomy to get the term from, defaults to category.
   * @return mixed              Boolean false of no category, otherwise WP_Term object.
   */
  function get_primary_category( $post_id = 0, $taxonomy = 'category' ) {
    $post_id = ! empty( $post_id ) ? $post_id : get_the_id();

    $primary_meta_keys = [
      '_yoast_wpseo_primary_' . $taxonomy, // Primary category from Yoast setting
      '_primary_term_' . $taxonomy, // Autodescription primary category setting
    ];

    $cat_id = null;

    // Try to get the primary term id from meta fields
    foreach ( $primary_meta_keys as $primary_meta_key ) {
      $maybe_cat_id = get_post_meta( $post_id, $primary_meta_key, true );
      if ( ! empty( $maybe_cat_id ) ) {
        $cat_id = $maybe_cat_id;
        break;
      }
    }

    // If primary set, try to get and return WP_Term object for it
    $term = ! empty( $cat_id ) ? get_term( $cat_id, $taxonomy ) : false;

    if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
      return $term;
    }

    // No primary category, get all post categories and return first one
    $cats = wp_get_post_terms( $post_id, $taxonomy );
    if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
      return $cats[0];
    }

    return false;
  } // end get_primary_category
} // end if

if ( ! function_exists( 'the_block_content' ) ) {
  /**
   * Outputs block content from a post
   *
   * @since  2.9.0
   * @param int     $post_id Post id to get block content from.
   * @param boolean $echo If true, echoes the block content otherwise returns.
   * @return mixed
   */
  function the_block_content( $post_id, $echo = true ) { // phpcs:ignore
    if ( ! is_int( $post_id ) ) {
      $post_id = absint( $post_id );
    }

    if ( ! has_blocks( $post_id ) ) {
      return;
    }

    /**
     * Overwrite global post to make post related
     * functions work inside block content
     */
    global $post;
    $post = get_post( $post_id ); // phpcs:ignore
    setup_postdata( $post );

    // Do the block content
    $block_content = apply_filters( 'the_content', get_the_content( '', '', $post ) );

    wp_reset_postdata();

    if ( ! $echo ) {
      return $block_content;
    }

    /**
     * Output blocks, already escaped on render function
     */
    echo $block_content; // phpcs:ignore
  } // end the_block_content
} // end if

if ( ! function_exists( 'air_helper_is_first_block' ) ) {
  function air_helper_is_first_block( $post_id, $block ) {
    if ( ! $post_id ) {
      $post_id = get_the_ID();
    }

    $post = get_post( $post_id );
    if ( ! has_blocks( $post->post_content ) ) {
      return false;
    }

    $blocks = parse_blocks( $post->post_content );
    $first_block = $blocks[0];
    if ( $first_block['blockName'] !== $block['name'] ) {
      return false;
    }

    if ( crc32( maybe_serialize( $first_block['attrs']['data'] ) ) !== crc32( maybe_serialize( $block['data'] ) ) ) {
      return false;
    }

    return true;
  } // end air_helper_get_first_block_id
} // end if
