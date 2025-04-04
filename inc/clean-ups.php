<?php
/**
 * Clean ups.
 *
 * @package air-helper
 */

/**
 * Remove unnecessary type attributes to suppress HTML validator messages.
 *
 * Turn off with:
 *
 * add_action( 'init', function() {
 *  remove_filter( 'style_loader_tag', 'air_helper_remove_type_attr' );
 *  remove_filter( 'script_loader_tag', 'air_helper_remove_type_attr' );
 *  remove_filter( 'autoptimize_html_after_minify', 'air_helper_remove_type_attr' );
 * }, 999 );
 *
 * @since  2.3.0
 */
add_filter( 'style_loader_tag', 'air_helper_remove_type_attr', 10, 2 );
add_filter( 'script_loader_tag', 'air_helper_remove_type_attr', 10, 2 );
add_filter( 'autoptimize_html_after_minify', 'air_helper_remove_type_attr', 10, 2 );
function air_helper_remove_type_attr( $tag, $handle = '' ) { // phpcs:ignore
  $tag = str_replace( " type='text/javascript'", '', $tag );
  $tag = str_replace( ' type="text/javascript"', '', $tag );
  $tag = str_replace( " type='text/css'", '', $tag );
  $tag = str_replace( ' type="text/css"', '', $tag );

  return $tag;
}

/**
 *  Strip unwanted html tags from titles
 *
 *  Turn off by using `remove_filter( 'nav_menu_item_title', 'air_helper_strip_tags_menu_item' )`
 *  Turn off by using `remove_filter( 'the_title', 'air_helper_strip_tags_menu_item' )`
 *
 *  @since  1.4.1
 *  @param  string $title title to strip.
 *  @param  mixed  $arg_2 whatever filter can pass.
 *  @param  mixed  $arg_3 whatever filter can pass.
 *  @param  mixed  $arg_4 whatever filter can pass.
 */
add_filter( 'nav_menu_item_title', 'air_helper_strip_tags_menu_item', 10, 4 );
add_filter( 'the_title', 'air_helper_strip_tags_menu_item', 10, 2 );
function air_helper_strip_tags_menu_item( $title, $arg_2 = null, $arg_3 = null, $arg_4 = null ) { // phpcs:ignore
  return strip_tags( $title, apply_filters( 'air_helper_allowed_tags_in_title', '<br><em><b><strong>' ) );
}

/**
 * Remove unnecessary WordPress default styles in front end (T-13957)
 *
 * Turn off completely with:
 * add_action( 'init', function() {
 *  remove_action( 'wp_enqueue_scripts', 'air_helper_dequeue_default_styles' );
 * }, 999 );
 *
 * Modify styles to deregister:
 * add_filter( 'air_helper_styles_to_deregister', function( $styles ) {
 *   // Remove a style from default list
 *   $styles = array_diff( $styles, ['dashicons'] );
 *
 *   // Or add new ones to deregister
 *   $styles[] = 'wp-block-library';
 *
 *   return $styles;
 * });
 *
 * @since 3.1.2
 */
add_action( 'wp_enqueue_scripts', 'air_helper_dequeue_default_styles', 100 );
function air_helper_dequeue_default_styles() {
  // Only disable dashicons for non-admin, non-logged-in users
  if ( ! is_admin() && ! is_user_logged_in() ) {
    wp_deregister_style( 'dashicons' );
  }

  // Always disable these styles regardless of user status
  $styles_to_deregister = apply_filters( 'air_helper_styles_to_deregister', [] );

  foreach ( $styles_to_deregister as $style ) {
    wp_deregister_style( $style );
  }
}
