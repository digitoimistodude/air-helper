<?php
/**
 * Archive related actions.
 *
 * @package air-helper
 */

/**
 * Remove archive title prefix.
 *
 * Turn off by using `remove_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' )`
 *
 * @since  0.1.0
 * @param  string $title Default title.
 * @return string Title without prefix
 */
add_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' );
function air_helper_helper_remove_archive_title_prefix( $title ) {
  return preg_replace( '#^[\w\d\s]+:\s*#', '', wp_strip_all_tags( $title ) );
} // end air_helper_helper_remove_archive_title_prefix

/**
 *  Disable some views by default.
 *  archives: tag, category, date, author
 *  other: search
 *
 *  Turn off by using `remove_action( 'template_redirect', 'air_helper_disable_views' )`
 *  or spesific views, for example tag archive, with `add_filter( 'air_helper_disable_views_tag', '__return_false' )`
 *
 *  @since  1.6.0
 */
add_action( 'template_redirect', 'air_helper_disable_views' );
function air_helper_disable_views() {
  // Do not try to disable views if we don't have function to check version where plugin was activated.
  if ( ! function_exists( 'air_helper_activated_at_version' ) ) {
    return;
  }

  // If plugin vas activated before version 1.5.7, do NOT disable views.
  if ( air_helper_activated_at_version() < 157 ) {
    return;
  }

  // Views to disable, each can be enabled back with
  // `add_filter( 'air_helper_disable_views_{$view}', '__return_false' )`
  $views = [
    'tag'      => 'is_tag',
    'category' => 'is_category',
    'date'     => 'is_date',
    'author'   => 'is_author',
    'search'   => 'is_search',
  ];

  global $wp_query;

  foreach ( $views as $view => $conditional ) {
    if ( ! apply_filters( "air_helper_disable_views_{$view}", true ) ) {
      continue;
    }

    if ( ! call_user_func( $conditional ) ) {
      continue;
    }

    $wp_query->set_404();
    status_header( 404 );
  }
} // end air_helper_disable_views
