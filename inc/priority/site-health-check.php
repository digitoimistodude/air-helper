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
    ini_get( 'error_log' ),            // PHP configured error log
  ];

  // Filter log paths
  $possible_log_paths = apply_filters( 'air_helper_error_log_paths', $possible_log_paths );

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

  } catch ( Exception $e ) {
    return new WP_Error(
      'log_parse_failed',
      $e->getMessage()
    );
  }

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
    '<p style="margin-top: 0; padding-top: 0;">%s %s</p>',
    sprintf(
      __( 'Found <b>%1$d fatal errors</b>, <b>%2$d warnings</b>, and <b>%3$d notices</b> in the last two weeks. Analyzed <b>%4$d log entries</b>, with <b>%5$d from the last two weeks</b>.', 'air-helper' ),
      $error_data['fatal'],
      $error_data['warning'],
      $error_data['notice'],
      $error_data['analyzed_entries'],
      isset( $error_data['entries_in_period'] ) ? $error_data['entries_in_period'] : 0
    ),
    __( 'Errors and warnings from the last two weeks:', 'air-helper' )
  );

  // Instead of just showing 5 errors, get all errors and warnings from the temp_errors array
  if ( ! empty( $error_data['recent_errors'] ) ) {
    $description .= '<ol>';

    // Extract and display all warnings and fatal errors, sorted by newest first
    $displayed_errors = [];
    foreach ( $error_data['recent_errors'] as $error ) {
      // Skip notices, only show warnings and fatal errors
      if ( 'Notice' === $error['type'] ) {
        continue;
      }

      // Extract date components for better formatting
      $timestamp = '';
      $date_formatted = '';
      $time_ago = '';
      if ( ! empty( $error['timestamp'] ) ) {
        // Parse timestamp like "2025/04/25 22:25:20"
        $timestamp = $error['timestamp'];
        if ( preg_match( '/^(\d{4})\/(\d{2})\/(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/', $timestamp, $date_parts ) ) {
          $year = $date_parts[1];
          $month = $date_parts[2];
          $day = $date_parts[3];
          $hour = $date_parts[4];
          $minute = $date_parts[5];
          $second = $date_parts[6];

          // Convert month number to name
          $month_names = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
          ];

          $month_name = isset( $month_names[ $month ] ) ? $month_names[ $month ] : $month;

          // Format as "25. April, 2025"
          $date_formatted = sprintf( '%d. %s, %s', (int) $day, $month_name, $year );

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
            $minutes = floor( ( $time_diff % 3600 ) / 60 );

            if ( $minutes > 0 ) {
              $time_ago = sprintf(
                // Translators: %1$d = number of hours, %2$s = 's' for plural or '' for singular, %3$d = number of minutes, %4$s = 's' for plural or '' for singular
                __( '%1$d hour%2$s and %3$d minute%4$s ago', 'air-helper' ),
                $hours,
                1 === $hours ? '' : 's',
                $minutes,
                1 === $minutes ? '' : 's'
              );
            } else {
              $time_ago = sprintf(
                _n( '%d hour ago', '%d hours ago', $hours, 'air-helper' ),
                $hours
              );
            }
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

      // Format with message, file and line first
      $error_details = esc_html( $error['message'] );

      if ( ! empty( $error['file'] ) && ! empty( $error['line'] ) ) {
        // Fix linter error by using ordered placeholders
        $error_details .= '<br><span class="error-location">' .
          sprintf(
            __( 'in %1$s on line %2$s', 'air-helper' ),
            '<code>' . esc_html( $error['file'] ) . '</code>',
            '<code>' . esc_html( $error['line'] ) . '</code>'
          ) . '</span>';
      }

      // Show count and date at the end
      $occurrence_info = '';
      if ( isset( $error['count'] ) && $error['count'] > 1 ) {
        $occurrence_info = sprintf(
          _n(
            'Occurred %d time',
            'Occurred %d times',
            $error['count'],
            'air-helper'
          ),
          $error['count']
        );
      }

      if ( ! empty( $time_ago ) ) {
        if ( ! empty( $occurrence_info ) ) {
          $occurrence_info .= ', last time ' . $time_ago;
        } else {
          $occurrence_info = $time_ago;
        }
      }

      if ( ! empty( $date_formatted ) && ! empty( $timestamp ) ) {
        $occurrence_info .= sprintf(
          __( ' (%1$s, exact timestamp: %2$s)', 'air-helper' ),
          $date_formatted,
          substr( $timestamp, 11 ) // Just the time part HH:MM:SS
        );
      }

      if ( ! empty( $occurrence_info ) ) {
        $error_details .= '<br><em>' . $occurrence_info . '</em>';
      }

      $description .= sprintf(
        '<li>%s</li>',
        $error_details
      );

      $displayed_errors[] = $error;
    }
    $description .= '</ol>';
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
  $error_data = air_helper_parse_nginx_error_log();

  if ( is_wp_error( $error_data ) ) {
		echo '<p class="error">' . esc_html( $error_data->get_error_message() ) . '</p>';
		return;
  }

  // Display recent errors if any
  if ( ! empty( $error_data['recent_errors'] ) ) {
		echo '<h4>' . esc_html__( 'Latest PHP messages', 'air-helper' ) . '</h4>';

    // Display error explanation
    printf(
      '<p>%s</p>',
      esc_html__( 'PHP errors can indicate problems with your website\'s code. While some notices are normal, frequent warnings or fatal errors should be investigated.', 'air-helper' )
    );

    // Display error counts
    printf(
      '<p><strong>%s:</strong> %d<br><strong>%s:</strong> %d<br><strong>%s:</strong> %d</p>',
      esc_html__( 'Fatal errors', 'air-helper' ),
      esc_html( $error_data['fatal'] ),
      esc_html__( 'Warnings', 'air-helper' ),
      esc_html( $error_data['warning'] ),
      esc_html( $error_data['notice'] )
    );

		echo '<h4>' . esc_html__( 'Found warnings:', 'air-helper' ) . '</h4>';
		echo '<ol class="recent-errors">';
		foreach ( $error_data['recent_errors'] as $error ) {
		  printf(
			'<li class="error-type-%s">
          <span class="error-time">%s</span>
          <code>%s</code>
        </li>',
			sanitize_html_class( strtolower( $error['type'] ) ),
			esc_html( $error['timestamp'] ),
			esc_html( $error['message'] )
		  );
			}
		echo '</ol>';
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
    .recent-errors {
      margin: 0;
    }

    .recent-errors li {
      margin-bottom: 15px;
      padding: 10px;
      background: #f8f9fa;
    }

    .recent-errors .error-time {
      display: block;
      color: #646970;
      font-size: 0.9em;
      margin-bottom: 5px;
    }

    .recent-errors code {
      display: block;
      margin: 10px 0 0;
      padding: 10px;
      max-height: 150px;
      overflow-y: auto;
      background: #f1f1f1;
      border: 1px solid #ddd;
      word-wrap: break-word;
    }

    .error-type-fatal {
      border-left: 4px solid #d63638;
    }

    .error-type-warning {
      border-left: 4px solid #dba617;
    }

    .error-type-notice {
      border-left: 4px solid #72aee6;
    }

    .error-details-link {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #ddd;
    }
  </style>
  <?php
});

