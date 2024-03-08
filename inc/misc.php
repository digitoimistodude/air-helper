<?php
/**
 * Collection of miscellaneous actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:03:27
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2022-06-22 10:59:38
 *
 * @package air-helper
 */

/**
 * Remove unnecessary type attributes to suppress HTML validator messages.
 *
 * Turn off by using `add_filter( 'style_loader_tag', 'air_helper_remove_type_attr' )`
 * Turn off by using `add_filter( 'script_loader_tag', 'air_helper_remove_type_attr' )`
 * Turn off by using `add_filter( 'autoptimize_html_after_minify', 'air_helper_remove_type_attr' )`
 *
 * @since  2.3.0
 */
add_filter( 'style_loader_tag', 'air_helper_remove_type_attr', 10, 2 );
add_filter( 'script_loader_tag', 'air_helper_remove_type_attr', 10, 2 );
add_filter( 'autoptimize_html_after_minify', 'air_helper_remove_type_attr', 10, 2 );
function air_helper_remove_type_attr( $tag, $handle = '' ) { // phpcs:ignore
  return preg_replace( "/type=['\"]text\/(javascript|css)['\"]/", '', $tag ); // phpcs:ignore
} // end air_helper_remove_type_attr

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
} // end air_helper_strip_tags_menu_item

/**
 * Add instant.page just-in-time preloading script to footer.
 *
 * Disable using `remove_action( 'wp_enqueue_scripts', 'air_helper_enqueue_instantpage_script', 50 )`
 *
 * @since 5.0.0
 */
add_action( 'wp_enqueue_scripts', 'air_helper_enqueue_instantpage_script' );
function air_helper_enqueue_instantpage_script() {
  wp_enqueue_script( 'instantpage', air_helper_base_url() . '/assets/js/instantpage.js', [], '5.1.0', true );
} // end air_helper_enqueue_instantpage_script

/**
 * Disable cache for Relevanssi related posts output on development environment for easier development.
 *
 * @since 2.15.0
 */
add_filter( 'relevanssi_disable_related_cache', 'air_helper_disable_relevanssi_related_cache_on_dev' );
function air_helper_disable_relevanssi_related_cache_on_dev( $cache ) {
  if ( 'development' === wp_get_environment_type() ) {
    return true;
  }

  return $cache;
} // end air_helper_disable_relevanssi_related_cache_on_dev
