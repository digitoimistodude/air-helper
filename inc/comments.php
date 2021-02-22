<?php
/**
 * Commenting and pingback related.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:22:06
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-02-22 19:41:47
 *
 * @package air-helper
 */

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 * Turn off by using `remove_action( 'wp_head', 'air_helper_pingback_header' )`
 *
 * @since  0.1.0
 */
add_action( 'wp_head', 'air_helper_pingback_header' );
function air_helper_pingback_header() {
  if ( is_singular() && pings_open() ) {
    echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
  }
} // end air_helper_pingback_heade


/**
 * Remove unnecessary WordPress injected .recentcomments
 *
 * @since 2.6.0
 */
add_action( 'widgets_init', 'air_helper_remove_recent_comments_style' );
function air_helper_remove_recent_comments_style() {
  global $wp_widget_factory;
  remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
} // end air_helper_remove_recent_comments_style
