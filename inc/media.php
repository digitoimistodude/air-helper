<?php
/**
 * Media library and file actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:42:40
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-01-10 16:43:05
 *
 * @package air-helper
 */

/**
 * Custom uploads folder media/ instead of default content/uploads/.
 * Turn off by using filter `add_filter( 'air_helper_change_uploads_path', '__return_false' )`
 *
 * @since 0.1.0
 */
if ( apply_filters( 'air_helper_change_uploads_path', true ) ) {
  update_option( 'upload_path', untrailingslashit( str_replace( 'wp', 'media', ABSPATH ) ) );
  update_option( 'upload_url_path', untrailingslashit( str_replace( 'wp', 'media', get_site_url() ) ) );
  define( 'uploads', '' . 'media' );
  add_filter( 'option_uploads_use_yearmonth_folders', '__return_false', 100 );
}
