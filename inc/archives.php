<?php
/**
 * Archive related actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:20:24
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:20:55
 *
 * @package air-helper
 */

/**
 * Remove archive title prefix.
 * Turn off by using `remove_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' )`
 *
 * @since  0.1.0
 * @param  string $title Default title.
 * @return string Title without prefix
 */
add_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' );
function air_helper_helper_remove_archive_title_prefix( $title ) {
  return preg_replace( '/^\w+: /', '', $title );
} // end air_helper_helper_remove_archive_title_prefix
