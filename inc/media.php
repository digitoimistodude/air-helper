<?php
/**
 * Media library and file actions.
 *
 * @package air-helper
 */

// Helper function to get staging URL
function air_helper_get_staging_url() {
  // Check for constant first
  if ( defined( 'STAGING_URL' ) ) {
		return STAGING_URL;
  }

  // Then check for environment variable
  elseif ( getenv( 'STAGING_URL' ) ) {
		return getenv( 'STAGING_URL' );
  }

  // Finally fall back to default
  return apply_filters( 'air_helper_staging_url', 'vaiheessa.fi' );
}

/**
 * Custom uploads folder media/ instead of default content/uploads/.
 *
 * Turn off by using filter `add_filter( 'air_helper_change_uploads_path', '__return_false' )`
 *
 * @since 0.1.0
 */
if ( apply_filters( 'air_helper_change_uploads_path', true ) ) {
  $update_option = false;

  // Check if there are staging URLs in the media files
  $has_staging_media = air_helper_has_staging_media( air_helper_get_staging_url() );

  // Get the project root directory
  $project_root_path = dirname( ABSPATH );

  // Get the project name
  $project_name = basename( dirname( ABSPATH ) );

  // If we do not have media folder in project root, do not continue with this filter
  if ( ! file_exists( $project_root_path . '/media' ) ) {
		return;
  }

  // If project root path contains /Users, replace it with /var/www, first ensuring /var/www/project_name exists
  if ( strpos( $project_root_path, '/Users' ) !== false && file_exists( '/var/www/' . $project_name ) ) {
		$project_root_path = '/var/www/' . $project_name;
  }

  if ( $has_staging_media && wp_get_environment_type() === 'staging' ) {
		// Get the project name and path from ABSPATH
		$current_path = dirname(ABSPATH);
		$releases_path = dirname($current_path);
		$project_root = dirname($releases_path);

		// Force staging path and URL for staging environment
		$upload_path = $project_root . '/shared/media';
		$upload_url = str_replace( [ 'http://', 'https://' ], '', home_url());
		$upload_url = 'https://' . $upload_url . '/media';

		// Disable media options in admin
		add_filter( 'pre_option_upload_path', function () use ( $upload_path ) {
		  return $upload_path;
		} );

		add_filter( 'pre_option_upload_url_path', function () use ( $upload_url ) {
			return $upload_url;
		} );

		// Helper function to replace test domain with staging domain
		function air_helper_replace_test_domain( $url ) {
			return str_replace('.test', '.' . air_helper_get_staging_url(), $url);
		}

    // Add filter to replace .test domain with staging domain in attachment URLs
    add_filter('wp_get_attachment_url', 'air_helper_replace_test_domain');

    // Filter the srcset URLs
    add_filter('wp_calculate_image_srcset', function ( $sources ) {
      foreach ($sources as &$source ) {
        $source['url'] = air_helper_replace_test_domain( $source['url'] );
      }
      return $sources;
    });

    // Filter all image URLs in our lazyload functions
    add_filter('air_helper_get_image_lazyload_sizes', function ( $sizes ) {
      if ( is_array($sizes) ) {
        foreach ( $sizes as $key => $url ) {
            $sizes[ $key ] = air_helper_replace_test_domain( $url );
        }
      }
			return $sizes;
		});
  } else {
		// Get media directory path
		$upload_path = $project_root_path . '/media';

		// Ensure the URL path is relative to the site root
		$upload_url = home_url( '/media' );
  }

  // If staging media found or we are in dev, disable media options in admin
  if ( $has_staging_media || wp_get_environment_type() === 'development' ) {
		// Add JavaScript to disable fields in admin
		add_action( 'admin_head', function () {
		  echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
          var uploadPathField = document.querySelector("input[name=\'upload_path\']");
          var uploadUrlPathField = document.querySelector("input[name=\'upload_url_path\']");
          var yearMonthField = document.querySelector("input[name=\'uploads_use_yearmonth_folders\']");
          if (uploadPathField) uploadPathField.setAttribute("disabled", "disabled");
          if (uploadUrlPathField) uploadUrlPathField.setAttribute("disabled", "disabled");
          if (yearMonthField) yearMonthField.setAttribute("disabled", "disabled");
        });
      </script>';
			});
  }

  // Update the options
  update_option( 'upload_path', untrailingslashit( $upload_path ) );
  update_option( 'upload_url_path', untrailingslashit( $upload_url ) );
  update_option( 'air_helper_changed_uploads_path', date_i18n( 'Y-m-d H:i:s' ) );

  // Add upload_dir filter to set correct paths
  add_filter('upload_dir', function ( $uploads ) use ( $upload_path, $upload_url ) {
		$uploads['basedir'] = $upload_path;
		$uploads['baseurl'] = $upload_url;
		$uploads['path'] = $upload_path;
		$uploads['url'] = $upload_url;

		return $uploads;
  });

  // These should always be set regardless of environment
  define( 'uploads', 'media' ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
  add_filter( 'option_uploads_use_yearmonth_folders', '__return_false', 100 );
}

/**
 * Show warning notice in development environment when accessing media library
 *
 * Turn off by using `remove_action( 'admin_notices', 'air_helper_dev_media_library_notice' )`
 *
 * @since 3.1.2
 */
function air_helper_dev_media_library_notice() {
  // Only prevent uploads in development environment
  if ( wp_get_environment_type() !== 'development' ) {
		return;
  }

  if ( ! air_helper_prevent_dev_uploads_enabled() ) {
		return;
  }

  // Check if there are staging URLs in the media files
  $has_staging_media = air_helper_has_staging_media( air_helper_get_staging_url() );

  if ( ! $has_staging_media ) {
		return;
  }

  // Only show if DB is not localhost and contains staging URLs or if staging URLs are found
  $db_name = defined( 'DB_NAME' ) ? DB_NAME : '';
  if ( 'localhost' === $db_name && ! air_helper_has_staging_media( air_helper_get_staging_url() ) ) {
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
  // Only prevent uploads in development environment
  if ( wp_get_environment_type() !== 'development' ) {
		return $file;
  }

  if ( ! air_helper_prevent_dev_uploads_enabled() ) {
		return $file;
  }

  // Check if there are staging URLs in the media files
  $has_staging_media = air_helper_has_staging_media( air_helper_get_staging_url() );

  if ( ! $has_staging_media ) {
		return $file;
  }

  // Only prevent uploads if we have staging media
  $db_name = defined( 'DB_NAME' ) ? DB_NAME : '';
  if ( 'localhost' !== $db_name || air_helper_has_staging_media( air_helper_get_staging_url() ) ) {
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

/**
 * Get clean media URL regardless of environment
 *
 * @since 3.1.8
 * @param string $url Original URL that might contain problems
 * @return string Clean URL with correct structure
 */
function air_helper_get_clean_media_url( $url ) {
  // Respect the existing filter to disable functionality
  if ( ! apply_filters( 'air_helper_change_uploads_path', true ) ) {
    return $url;
  }

  // Get just the filename from the URL or path, regardless of what's in front of it
  $filename = basename($url);

  // Always return clean URL with correct structure
  return home_url('/media/' . $filename);
}

/**
 * Clean all media URLs
 *
 * @since 3.1.8
 * @return boolean
 */
add_filter('wp_get_attachment_url', function ( $url ) {
  return air_helper_get_clean_media_url( $url );
}, 99);

add_filter('wp_calculate_image_srcset', function ( $sources ) {
  // Respect the existing filter to disable functionality
  if ( ! apply_filters( 'air_helper_change_uploads_path', true ) ) {
    return $sources;
  }

  foreach ( $sources as &$source ) {
    $source['url'] = air_helper_get_clean_media_url( $source['url'] );
  }
  return $sources;
}, 99);
