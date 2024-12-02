<?php
/**
 * Media library and file actions.
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
  $update_option = true;

  if ( 'production' === wp_get_environment_type() && get_option( 'air_helper_changed_uploads_path' ) ) {
    $update_option = false;
  }

  // Don't update options if development environment is used on staging DB
  if ( 'development' === wp_get_environment_type() && 'gunship.dude.fi' === getenv( 'DB_HOST' ) ) {
    $update_option = false;
  }

  if ( $update_option ) {
    update_option( 'upload_path', untrailingslashit( preg_replace( '/\bwp\b/u', 'media', ABSPATH ) ) );
    update_option( 'upload_url_path', untrailingslashit( preg_replace( '/\bwp\b/u', 'media', get_site_url() ) ) );
    update_option( 'air_helper_changed_uploads_path', date_i18n( 'Y-m-d H:i:s' ) );
  } // end option update

  define( 'uploads', 'media' ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
  add_filter( 'option_uploads_use_yearmonth_folders', '__return_false', 100 );
} // end filter if
