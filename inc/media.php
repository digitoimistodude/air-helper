<?php
/**
 * Media library and file actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:42:40
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-14 11:16:54
 *
 * @package air-helper
 */

/**
 * Custom uploads folder media/ instead of default content/uploads/.
 *
 * Turn off by using filter `add_filter( 'air_helper_change_uploads_path', '__return_false' )`
 *
 * @since 0.1.0
 */
if ( apply_filters( 'air_helper_change_uploads_path', true ) ) {
  if ( ! get_option( 'air_helper_changed_uploads_path' ) ) {
    update_option( 'upload_path', untrailingslashit( str_replace( 'wp', 'media', ABSPATH ) ) );
    update_option( 'upload_url_path', untrailingslashit( str_replace( 'wp', 'media', get_site_url() ) ) );
    update_option( 'air_helper_changed_uploads_path', date_i18n( 'Y-m-d H:i:s' ) );
  } // end option update

  define( 'uploads', 'media' ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
  add_filter( 'option_uploads_use_yearmonth_folders', '__return_false', 100 );
} // end filter if
