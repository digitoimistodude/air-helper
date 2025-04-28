<?php
/**
 * Site health check modifications.
 *
 * @package air-helper
 */

/**
 * We take care of multiple things, but allow dude.fi users to see the details.
 *
 * There are multiple ways to allow users to see health checks:
 *
 * 1. Using domain filter for all features:
 * add_filter('air_helper_allow_user_to_domain', function($domain) {
 *   return 'yourdomain.com'; // Changes allowed domain for all features
 * });
 *
 * 2. Using specific domain filter for health checks:
 * add_filter('air_helper_allow_user_to_health-check_domain', function($domain) {
 *   return 'yourdomain.com'; // Changes allowed domain only for health checks
 * });
 *
 * 3. Using user meta override:
 * update_user_meta($user_id, '_airhelper_admin_show_health-check', 'true');
 *
 * 4. Multiple domains using filter:
 * add_filter('air_helper_allow_user_to_health-check_domain', function($domain) {
 *   $allowed_domains = ['dude.fi', 'example.com', 'client.com'];
 *   if (in_array($domain, $allowed_domains)) {
 *     return $domain;
 *   }
 *   return 'dude.fi';
 * });
 *
 * @since  1.10.0
 */
add_filter('site_status_tests', 'air_helper_remove_status_tests');
function air_helper_remove_status_tests( $tests ) {
  // Allow dude.fi admin users and additionally allowed users to see all tests
  if ( air_helper_allow_user_to( 'health-check' ) ) {
		return $tests;
  }

  // We take care of server requirements.
  unset($tests['direct']['php_version']);
  unset($tests['direct']['sql_server']);
  unset($tests['direct']['php_extensions']);
  unset($tests['direct']['utf8mb4_support']);

  // We provide the updates.
  unset($tests['direct']['wordpress_version']);
  unset($tests['direct']['plugin_version']);
  unset($tests['direct']['theme_version']);
  unset($tests['async']['background_updates']);

  return $tests;
}

/**
 * We take care of multiple things, but allow dude.fi users to see the details.
 *
 * @since  1.10.0
 */
add_filter('debug_information', 'air_helper_remove_debug_information');
function air_helper_remove_debug_information( $debug_info ) {
  // Allow dude.fi admin users and additionally allowed users to see all debug information
  if ( air_helper_allow_user_to( 'health-check' ) ) {
		return $debug_info;
  }

  unset($debug_info['wp-server']);
  unset($debug_info['wp-paths-sizes']);
  unset($debug_info['wp-database']);
  unset($debug_info['wp-constants']);
  unset($debug_info['wp-filesystem']);
  unset($debug_info['wp-media']);

  return $debug_info;
}

/**
 * Parse PHP error log file
 *
 * @since 3.2.0
 * @return array|WP_Error Array of error data or WP_Error if file not accessible
 * @throws Exception If log file cannot be read
 */
function air_helper_parse_nginx_error_log() {
  // Define possible log paths in order of preference
  $possible_log_paths = [
    '/var/log/nginx/error.log',      // Standard Nginx error log
    '/var/log/nginx/site-error.log', // Some hosts use site-specific logs
    '/var/log/php-fpm/www-error.log', // PHP-FPM log
    '/var/log/php/php_errors.log',   // General PHP error log
    '/var/log/apache2/error.log',    // Apache error log
    '/usr/local/var/log/nginx/error.log',  // Local macOS Nginx logs
    '/opt/homebrew/var/log/nginx/error.log', // Homebrew Nginx logs
    ini_get('error_log'),            // PHP configured error log
  ];

  // Filter log paths
  $possible_log_paths = apply_filters( 'air_helper_error_log_paths', $possible_log_paths );

  // Check if we have cached data from a previous run
  $cached_data = get_transient( 'air_helper_error_log_data' );
  if ( $cached_data ) {
    return $cached_data;
  }

  $log_path = false;

  // Find first readable log file
  foreach ( $possible_log_paths as $path ) {
    if ( $path && file_exists( $path ) && is_readable( $path ) ) {
      $log_path = $path;
      break;
    }
  }

  // If no log file found, create sample data for testing
  if ( ! $log_path ) {
    // For development environments, return sample data instead of error
    if ( wp_get_environment_type() === 'development' || ( defined( 'DISABLE_ERROR_LOG_CHECK' ) && DISABLE_ERROR_LOG_CHECK ) ) {
      return [
        'notice' => 2,
        'warning' => 3,
        'fatal' => 0,
        'recent_errors' => [
          [
            'type' => 'Warning',
            'message' => 'Sample warning message (no actual log file found)',
            'timestamp' => current_time( 'Y-m-d H:i:s' ),
            'file' => '/path/to/sample/file.php',
            'line' => '42',
          ],
          [
            'type' => 'Notice',
            'message' => 'Sample notice message (no actual log file found)',
            'timestamp' => current_time( 'Y-m-d H:i:s' ),
            'file' => '/path/to/sample/file.php',
            'line' => '24',
          ],
        ],
        'analyzed_entries' => 0,
        'entries_in_period' => 0,
      ];
    }

    return new WP_Error(
      'log_not_accessible',
      __( 'PHP error log is not accessible. None of the expected log paths were found or readable.', 'air-helper' )
    );
  }

  // Get current domain from WP_HOME
  $current_domain = isset( wp_parse_url( WP_HOME )['host'] ) ? wp_parse_url( WP_HOME )['host'] : '';

  $errors = [
    'notice' => 0,
    'warning' => 0,
    'fatal' => 0,
    'recent_errors' => [],
    'analyzed_entries' => 0,
    'entries_in_period' => 0,
  ];

  // Try to read the file, with error handling
  try {
    // Instead of limiting by line count, we'll read the file and filter by date
    $lines = @file( $log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // phpcs:ignore

    if ( false === $lines ) {
      throw new Exception( __( 'Could not read error log file', 'air-helper' ) );
    }

    // Calculate timestamp for two weeks ago
    $two_weeks_ago_timestamp = strtotime( '-2 weeks' );

    // Collect all PHP errors, but use a temporary array to group related log lines
    $temp_errors = [];
    $current_timestamp = '';
    $current_date = '';

    // Count how many entries we analyze
    $errors['analyzed_entries'] = count( $lines );
    $entries_in_period = 0;

    foreach ( $lines as $line ) {
      // Extract timestamp from Nginx log (e.g., 2025/04/16 15:59:57)
      if ( preg_match( '/^(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/', $line, $date_matches ) ) {
        $current_timestamp = $date_matches[1];
        // Convert to a proper timestamp for comparison
        $current_date = strtotime( str_replace( '/', '-', $current_timestamp ) );
      }

      // Skip entries older than two weeks
      if ( ! empty( $current_date ) && $current_date < $two_weeks_ago_timestamp ) {
        continue;
      }

      // Count entries in our two-week period
      $entries_in_period++; // phpcs:ignore

      // Check for PHP errors in the Nginx error log format
      // Example: [error] 500#0: *824 FastCGI sent in stderr: "PHP message: PHP Warning: Undefined array key "thumbnail_id" in /path/to/file.php on line 41
      if ( ( strpos( $line, 'PHP message: PHP Notice' ) !== false ||
             strpos( $line, 'PHP message: PHP Warning' ) !== false ||
             strpos( $line, 'PHP message: PHP Fatal' ) !== false ) &&
           strpos( $line, 'on line' ) !== false ) {

        // Detect error type
        $type = 'Notice';
        if ( strpos( $line, 'PHP Warning' ) !== false ) {
          $type = 'Warning';
          ++$errors['warning'];
        } elseif ( strpos( $line, 'PHP Fatal' ) !== false ) {
          $type = 'Fatal error';
          ++$errors['fatal'];
        } else {
          ++$errors['notice'];
        }

        // Extract error message and file info from Nginx FastCGI logs
        if ( preg_match( '/PHP message: PHP (?:Warning|Notice|Fatal error):\s*(.+?)\s+in\s+(.+?)\s+on\s+line\s+(\d+)/', $line, $error_matches ) ) {
          $message = $error_matches[1];
          $file = $error_matches[2];
          $line_num = $error_matches[3];

          // Create a unique key for this error to group duplicates
          $error_key = md5( $message . $file . $line_num );

          // Only store if we haven't seen this exact error before
          if ( ! isset( $temp_errors[ $error_key ] ) ) {
            $temp_errors[ $error_key ] = [
              'type' => $type,
              'message' => $message,
              'timestamp' => $current_timestamp,
              'file' => $file,
              'line' => $line_num,
              'count' => 1,
            ];
          } else {
            // Increment count for duplicate errors
            $temp_errors[ $error_key ]['count']++; // phpcs:ignore
          }
        }
      }
    }

    // Sort errors by timestamp (newest first)
    uasort( $temp_errors, function ( $a, $b ) {
      // If timestamps are the same, prioritize fatal errors over warnings over notices
      if ( $a['timestamp'] === $b['timestamp'] ) {
        if ( 'Fatal error' === $a['type'] && 'Fatal error' !== $b['type'] ) {
          return -1;
        }
        if ( 'Fatal error' !== $a['type'] && 'Fatal error' === $b['type'] ) {
          return 1;
        }
        if ( 'Warning' === $a['type'] && 'Notice' === $b['type'] ) {
          return -1;
        }
        if ( 'Notice' === $a['type'] && 'Warning' === $b['type'] ) {
          return 1;
        }
        return 0;
      }
      return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
    } );

    // Store all errors instead of just the first 5
    $errors['recent_errors'] = $temp_errors;

    // Store the number of entries in the two-week period
    $errors['entries_in_period'] = $entries_in_period;

    // Store which log path was used for troubleshooting
    $errors['log_path'] = $log_path;

  } catch ( Exception $e ) {
    return new WP_Error(
      'log_parse_failed',
      $e->getMessage()
    );
  }

  // Cache results for 5 minutes to avoid frequent parsing of large log files
  set_transient( 'air_helper_error_log_data', $errors, 5 * MINUTE_IN_SECONDS );

  return $errors;
}

/**
 * Add PHP error log health checks
 *
 * @since  3.2.0
 */
add_filter( 'site_status_tests', 'air_helper_add_error_log_health_checks', 20 );
function air_helper_add_error_log_health_checks( $tests ) {
  $tests['direct']['air_helper_php_error_log'] = array(
    'label' => __( 'PHP Error Log', 'air-helper' ),
    'test'  => 'air_helper_error_log_health_test',
  );

  return $tests;
}

/**
 * Perform the error log health test
 */
function air_helper_error_log_health_test() {
  $result = array(
    'label'       => __( 'Errors in log', 'air-helper' ),
    'status'      => 'good',
    'badge'       => array(
      'label' => __( 'Performance', 'air-helper' ),
      'color' => 'blue',
    ),
    'description' => '',
    'actions'     => '',
    'test'        => 'air_helper_php_error_log',
  );

  $error_data = air_helper_parse_nginx_error_log();

  if ( is_wp_error( $error_data ) ) {
    $result['status'] = 'recommended';
    $result['label'] = __( 'Cannot access PHP error log', 'air-helper' );
    $result['description'] = sprintf(
      '<p>%s</p>',
      $error_data->get_error_message()
    );
    return $result;
  }

  // Set status based on error counts
  if ( $error_data['fatal'] > 0 ) {
    $result['status'] = 'critical';
  } elseif ( $error_data['warning'] > 0 ) {
    $result['status'] = 'recommended';
  }

  // Build a single paragraph with all stats together
  $description = sprintf(
    '<p class="site-health-status" style="margin-top: 0; padding-top: 0;">%s %s</p>',
    sprintf(
      __( 'Found <b>%1$d fatal errors</b>, <b>%2$d warnings</b>, and <b>%3$d notices</b> in the last two weeks. Analyzed <b>%4$d log entries</b>.', 'air-helper' ), // phpcs:ignore
      absint( $error_data['fatal'] ),
      absint( $error_data['warning'] ),
      absint( $error_data['notice'] ),
      absint( $error_data['analyzed_entries'] )
    ),
    esc_html__( 'Errors and warnings from the last two weeks:', 'air-helper' )
  );

  // Instead of just showing 5 errors, get all errors and warnings from the temp_errors array
  if ( ! empty( $error_data['recent_errors'] ) ) {
    $description .= '<ul class="simple-error-list">';

    // Extract and display all warnings and fatal errors, sorted by newest first
    $displayed_errors = [];
    foreach ( $error_data['recent_errors'] as $error ) {
      // Skip notices, only show warnings and fatal errors
      if ( 'Notice' === $error['type'] ) {
        continue;
      }

      // Extract date components for better formatting
      $timestamp = '';
      $time_ago = '';
      if ( ! empty( $error['timestamp'] ) ) {
        // Parse timestamp like "2025/04/25 22:25:20"
        $timestamp = $error['timestamp'];
        if ( preg_match( '/^(\d{4})\/(\d{2})\/(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/', $timestamp, $date_parts ) ) {
          // Calculate time ago
          $error_time = strtotime( str_replace( '/', '-', $timestamp ) );
          $current_time = time();
          $time_diff = $current_time - $error_time;

          if ( $time_diff < 60 ) {
            // Less than a minute
            $time_ago = sprintf(
              _n( 'just now', '%d seconds ago', $time_diff, 'air-helper' ), // phpcs:ignore
              $time_diff
            );
          } elseif ( $time_diff < 3600 ) {
            // Less than an hour
            $minutes = floor( $time_diff / 60 );
            $time_ago = sprintf(
              _n( '%d minute ago', '%d minutes ago', $minutes, 'air-helper' ),
              $minutes
            );
          } elseif ( $time_diff < 86400 ) {
            // Less than a day
            $hours = floor( $time_diff / 3600 );
            $time_ago = sprintf(
              _n( '%d hour ago', '%d hours ago', $hours, 'air-helper' ),
              $hours
            );
          } else {
            // Days or more
            $days = floor( $time_diff / 86400 );
            $time_ago = sprintf(
              _n( '%d day ago', '%d days ago', $days, 'air-helper' ),
              $days
            );
          }
        }
      }

      // Create SimpleHistory-style tag for error type
      $error_type_class = strtolower( $error['type'] );
      if ( 'fatal error' === $error_type_class ) {
        $error_type_class = 'critical';
      }

      $error_type_tag = sprintf(
        '<span class="logleveltag logleveltag-%s">%s</span>',
        esc_attr( $error_type_class ),
        esc_html( $error['type'] )
      );

      // Format message
      $error_message = esc_html( $error['message'] );

      // Format file and line info with tag inside it
      $file_info = '';
      if ( ! empty( $error['file'] ) && ! empty( $error['line'] ) ) {
        $file_info = sprintf(
          ' <span class="error-location">%s %s</span>',
          sprintf(
            esc_html__( 'in %1$s on line %2$s', 'air-helper' ),
            '<code>' . esc_html( $error['file'] ) . '</code>',
            '<code>' . esc_html( $error['line'] ) . '</code>'
          ),
          $error_type_tag
        );
      } else {
        // If no file/line info, still show the tag
        $file_info = ' <span class="error-location">' . $error_type_tag . '</span>';
      }

      // Occurrence info
      $occurrence_info = '';
      if ( isset( $error['count'] ) && $error['count'] > 1 ) {
        $occurrence_info = sprintf(
          '<span class="occurrence-count">%s</span>',
          sprintf(
            esc_html_x(
              'Occurred %d times',
              'Error occurrence count',
              'air-helper'
            ),
            $error['count']
          )
        );
      }

      // Time ago display
      $time_display = '';
      if ( ! empty( $time_ago ) ) {
        $time_display = sprintf(
          '<span class="time-ago">%s</span>',
          esc_html( $time_ago )
        );
      }

      // Put it all together - tag is now inside file_info
      $error_details = sprintf(
        '%s%s %s %s',
        $error_message,
        $file_info,
        $occurrence_info,
        $time_display
      );

      $description .= sprintf(
        '<li class="simple-error-item">%s</li>',
        $error_details
      );

      $displayed_errors[] = $error;
    }
    $description .= '</ul>';
  }

  $result['description'] = $description;
  return $result;
}

/**
 * Add project development errors dashboard widget (DEV-46)
 *
 * @since  3.2.8
 */
add_action('wp_dashboard_setup', 'air_helper_add_php_errors_dashboard_widget');
function air_helper_add_php_errors_dashboard_widget() {
  if ( ! air_helper_allow_user_to('health-check') ) {
		return;
  }

  wp_add_dashboard_widget(
    'air_helper_php_errors',
    __('Project development errors overview', 'air-helper'),
    'air_helper_php_errors_dashboard_widget_callback'
  );
}

/**
 * Render PHP Errors dashboard widget (DEV-46)
 *
 * @since  3.2.8
 */
function air_helper_php_errors_dashboard_widget_callback() {
  // Allow force refreshing of error data with a query parameter for testing
  if ( isset( $_GET['refresh_errors'] ) && current_user_can( 'manage_options' ) ) {
    delete_transient( 'air_helper_error_log_data' );
  }

  $error_data = air_helper_parse_nginx_error_log();

  if ( is_wp_error( $error_data ) ) {
    echo '<p class="error">' . esc_html( $error_data->get_error_message() ) . '</p>';
    return;
  }

  // Display which log file is being read (for admin troubleshooting)
  if ( isset( $error_data['log_path'] ) && current_user_can( 'manage_options' ) ) {
    echo '<p class="log-path-info" style="font-size: 0.8em; color: #666;">Reading errors from: ' .
      esc_html( $error_data['log_path'] ) .
      ' <a href="' . esc_url( add_query_arg( 'refresh_errors', '1' ) ) . '">Refresh data</a></p>';
  }

  // Display recent errors if any
  if ( ! empty( $error_data['recent_errors'] ) ) {
    // Display error explanation
    printf(
      '<p>%s</p>',
      esc_html__( 'PHP errors can indicate problems with your website\'s code. While some notices are normal, frequent warnings or fatal errors should be investigated.', 'air-helper' )
    );

    // Build a single paragraph with all stats together like in health check
    printf(
      '<p class="site-health-status" style="margin-top: 0; padding-top: 0;">%s %s</p>',
      sprintf(
        __( 'Found <b>%1$d fatal errors</b>, <b>%2$d warnings</b>, and <b>%3$d notices</b> in the last two weeks. Analyzed <b>%4$d log entries</b>.', 'air-helper' ), // phpcs:ignore
        absint( $error_data['fatal'] ),
        absint( $error_data['warning'] ),
        absint( $error_data['notice'] ),
        absint( $error_data['analyzed_entries'] )
      ),
      esc_html__( 'Errors and warnings from the last two weeks:', 'air-helper' )
    );

    if ( ! empty( $error_data['recent_errors'] ) ) {
      echo '<ul class="simple-error-list">';
      // Extract and display all warnings and fatal errors, sorted by newest first
      $displayed_errors = [];
      foreach ( $error_data['recent_errors'] as $error ) {
        // Skip notices, only show warnings and fatal errors
        if ( 'Notice' === $error['type'] ) {
          continue;
        }

        // Extract date components for better formatting
        $timestamp = '';
        $time_ago = '';
        if ( ! empty( $error['timestamp'] ) ) {
          // Parse timestamp like "2025/04/25 22:25:20"
          $timestamp = $error['timestamp'];
          if ( preg_match( '/^(\d{4})\/(\d{2})\/(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/', $timestamp, $date_parts ) ) {
            // Calculate time ago
            $error_time = strtotime( str_replace( '/', '-', $timestamp ) );
            $current_time = time();
            $time_diff = $current_time - $error_time;

            if ( $time_diff < 60 ) {
              // Less than a minute
              $time_ago = sprintf(
                _n( 'just now', '%d seconds ago', $time_diff, 'air-helper' ), // phpcs:ignore
                $time_diff
              );
            } elseif ( $time_diff < 3600 ) {
              // Less than an hour
              $minutes = floor( $time_diff / 60 );
              $time_ago = sprintf(
                _n( '%d minute ago', '%d minutes ago', $minutes, 'air-helper' ),
                $minutes
              );
            } elseif ( $time_diff < 86400 ) {
              // Less than a day
              $hours = floor( $time_diff / 3600 );
              $time_ago = sprintf(
                _n( '%d hour ago', '%d hours ago', $hours, 'air-helper' ),
                $hours
              );
            } else {
              // Days or more
              $days = floor( $time_diff / 86400 );
              $time_ago = sprintf(
                _n( '%d day ago', '%d days ago', $days, 'air-helper' ),
                $days
              );
            }
          }
        }

        // Create SimpleHistory-style tag for error type
        $error_type_class = strtolower( $error['type'] );
        if ( 'fatal error' === $error_type_class ) {
          $error_type_class = 'critical';
        }

        $error_type_tag = sprintf(
          '<span class="logleveltag logleveltag-%s">%s</span>',
          esc_attr( $error_type_class ),
          esc_html( $error['type'] )
        );

        // Format message
        $error_message = esc_html( $error['message'] );

        // Format file and line info with tag inside it
        $file_info = '';
        if ( ! empty( $error['file'] ) && ! empty( $error['line'] ) ) {
          $file_info = sprintf(
            ' <span class="error-location">%s %s</span>',
            sprintf(
              esc_html__( 'in %1$s on line %2$s', 'air-helper' ),
              '<code>' . esc_html( $error['file'] ) . '</code>',
              '<code>' . esc_html( $error['line'] ) . '</code>'
            ),
            $error_type_tag
          );
        } else {
          // If no file/line info, still show the tag
          $file_info = ' <span class="error-location">' . $error_type_tag . '</span>';
        }

        // Occurrence info
        $occurrence_info = '';
        if ( isset( $error['count'] ) && $error['count'] > 1 ) {
          $occurrence_info = sprintf(
            '<span class="occurrence-count">%s</span>',
            sprintf(
              esc_html_x(
                'Occurred %d times',
                'Error occurrence count',
                'air-helper'
              ),
              $error['count']
            )
          );
        }

        // Time ago display
        $time_display = '';
        if ( ! empty( $time_ago ) ) {
          $time_display = sprintf(
            '<span class="time-ago">%s</span>',
            esc_html( $time_ago )
          );
        }

        // Put it all together - tag is now inside file_info
        $error_details = sprintf(
          '%s%s %s %s',
          $error_message,
          $file_info,
          $occurrence_info,
          $time_display
        );

        printf(
          '<li class="simple-error-item">%s</li>',
          wp_kses_post( $error_details )
        );

        $displayed_errors[] = $error;
      }
      echo '</ul>';

      // Add link to Site Health page for more details
      echo '<div class="error-details-link"><a href="' . esc_url( admin_url( 'site-health.php' ) ) . '" class="button button-primary">' .
        esc_html__( 'View detailed health information', 'air-helper' ) . '</a></div>';
    }
  } else {
    // No errors found
    echo '<p>' . esc_html__( 'No PHP errors detected in the last two weeks. Great job!', 'air-helper' ) . '</p>';
  }
}

/**
 * Store error log statistics in database
 *
 * @since  3.2.8
 */
function air_helper_store_error_statistics() {
  $error_data = air_helper_parse_nginx_error_log();

  if (is_wp_error($error_data) ) {
		return;
  }

  $current_week = gmdate('Y-W');
  $stats = get_option('air_helper_error_stats', []);

  // Store weekly statistics
  $stats[ $current_week ] = [
    'timestamp' => time(),
    'fatal' => $error_data['fatal'],
    'warning' => $error_data['warning'],
    'notice' => $error_data['notice'],
    'total' => $error_data['fatal'] + $error_data['warning'] + $error_data['notice'],
    'recent_errors' => array_slice($error_data['recent_errors'], 0, 5), // Store last 5 errors
  ];

  // Keep only last 4 weeks of data
  if (count($stats) > 4 ) {
		$stats = array_slice($stats, -4, 4, true);
  }

  update_option('air_helper_error_stats', $stats, false);

  // Cache the current week's total for quick access
  set_transient('air_helper_current_week_errors', $stats[ $current_week ]['total'], WEEK_IN_SECONDS);
}

// Schedule daily error statistics collection
add_action('init', function () {
  if ( ! wp_next_scheduled('air_helper_daily_error_check') ) {
		wp_schedule_event(time(), 'daily', 'air_helper_daily_error_check');
  }
});

add_action('air_helper_daily_error_check', 'air_helper_store_error_statistics');

/**
 * Add error statistics to the Site Health screen
 * Shows warning notice if error count exceeds threshold
 *
 * @since  3.2.8
 */
function air_helper_add_error_stats_to_debug_information( $info ) {
  $stats = get_option('air_helper_error_stats', []);
  $current_week = gmdate('Y-W');

  if (isset($stats[ $current_week ]) ) {
		$current_stats = $stats[ $current_week ];
		$total_errors = $current_stats['total'];

		// Only show warning if there are more than 50 errors
		if ($total_errors > 50 ) {
		  $message = sprintf(
			__('There are over %d lines of errors and warnings this week. Please check the details in the Site Health section. This usually indicates something is wrong in the programming of this website.', 'air-helper'),
			$total_errors
		  );

		  add_action('admin_notices', function () use ( $message ) {
				  printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html($message)
				  );
		  });
			}
  }

  return $info;
}
add_filter('debug_information', 'air_helper_add_error_stats_to_debug_information');

/**
 * Add styles for the PHP errors dashboard widget
 *
 * @since  3.2.0
 */
add_action( 'admin_head', function () {
  ?>
  <style>
    /* SimpleHistory-inspired styles */
    .simple-error-list {
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .simple-error-item {
      margin-bottom: 10px;
      padding: 8px 0;
      border-bottom: 1px solid #eee;
      line-height: 1.5;
    }

    .logleveltag {
      display: inline-block;
      background-color: rgba(238, 238, 238, 1);
      font-size: 10px;
      padding: 3px 4px;
      border-radius: 3px;
      vertical-align: 1px;
      line-height: 1;
      margin-right: 5px;
    }

    .logleveltag-warning {
      background-color: #f7d358;
      color: #111;
    }

    .logleveltag-critical, .logleveltag-fatal {
      background-color: #fa5858;
      color: #fff;
    }

    .logleveltag-notice {
      background-color: #72aee6;
      color: #fff;
    }

    .error-location {
      font-size: 0.9em;
      color: #646970;
      display: inline-block;
      margin-left: 3px;
      line-height: 1.8;
    }

    .simple-error-item code {
      display: inline;
      padding: 2px 4px;
      background: #f1f1f1;
      border: 1px solid #ddd;
      word-wrap: break-word;
      font-size: 0.9em;
    }

    .occurrence-count {
      display: inline-block;
      color: #646970;
      font-size: 0.85em;
      margin-left: 5px;
      font-style: italic;
    }

    .time-ago {
      display: inline-block;
      color: #646970;
      font-size: 0.85em;
      margin-left: 5px;
      font-style: italic;
    }

    /* Health Check status styles */
    .health-check-status {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 4px;
    }

    .health-check-status.good {
      background-color: #edfaef;
    }

    .health-check-status.recommended {
      background-color: #fcf9e8;
    }

    .health-check-status.critical {
      background-color: #fcf0f1;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 3px;
      font-weight: 600;
      font-size: 12px;
      margin-right: 10px;
    }

    .health-check-status.good .status-badge {
      background-color: #00a32a;
      color: #fff;
    }

    .health-check-status.recommended .status-badge {
      background-color: #dba617;
      color: #fff;
    }

    .health-check-status.critical .status-badge {
      background-color: #d63638;
      color: #fff;
    }

    .site-health-status b {
      font-weight: 600;
    }
  </style>
  <?php
});

