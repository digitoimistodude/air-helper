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

/**
 * Show warning notice in development environment when accessing media library
 *
 * Turn off by using `remove_action( 'admin_notices', 'air_helper_dev_media_library_notice' )`
 *
 * @since 3.1.2
 */
function air_helper_dev_media_library_notice() {
  if ( ! air_helper_prevent_dev_uploads_enabled() ) {
    return;
  }

  // Only show if DB is not localhost and contains staging URLs or if staging URLs are found
  $db_name = defined( 'DB_NAME' ) ? DB_NAME : '';
  $staging_url = apply_filters( 'air_helper_staging_url', 'vaiheessa.fi' );

  if ( 'localhost' === $db_name && ! air_helper_has_staging_media( $staging_url ) ) {
    return;
  }

  // Only show on media library pages
  $screen = get_current_screen();
  if ( ! $screen || ! in_array( $screen->base, [ 'upload', 'media' ], true ) ) {
    return;
  }

  $class = 'notice notice-warning';
  $message = __( 'You are in development environment. Media uploads are disabled in development environment. Please use staging or production environment for uploading media.', 'air-helper' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}
add_action( 'admin_notices', 'air_helper_dev_media_library_notice' );

/**
 * Prevent media uploads in development environment
 *
 * Turn off by using `remove_filter( 'wp_handle_upload_prefilter', 'air_helper_prevent_dev_media_upload' )`
 *
 * @since 3.1.2
 */
function air_helper_prevent_dev_media_upload( $file ) {
  $db_name = defined( 'DB_NAME' ) ? DB_NAME : '';
  $staging_url = apply_filters( 'air_helper_staging_url', '*.vaiheessa.fi' );

  if ( air_helper_prevent_dev_uploads_enabled() &&
       ( 'localhost' !== $db_name || air_helper_has_staging_media( $staging_url ) ) ) {
    $file['error'] = __( 'Media uploads are disabled in development environment. Please use staging or production environment for uploading media.', 'air-helper' );
  }

  return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'air_helper_prevent_dev_media_upload' );

/**
 * Check if media upload prevention is enabled
 *
 * Turn off by using filter `add_filter( 'air_helper_prevent_dev_uploads', '__return_false' )`
 *
 * @since 3.1.2
 * @return boolean
 */
function air_helper_prevent_dev_uploads_enabled() {
  return apply_filters( 'air_helper_prevent_dev_uploads', true );
}

/**
 * Check if database contains staging media URLs
 *
 * @since 3.1.2
 * @param string $staging_url The staging URL pattern to check for
 * @return boolean
 */
function air_helper_has_staging_media( $staging_url ) {
  global $wpdb;

  $staging_url = str_replace( '*', '%', $staging_url );
  $has_staging_media = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare(
      "SELECT COUNT(*) FROM {$wpdb->posts}
      WHERE post_type = 'attachment'
      AND guid LIKE %s",
      '%' . $wpdb->esc_like( $staging_url ) . '%'
    )
  );

  return intval( $has_staging_media ) > 0;
}
