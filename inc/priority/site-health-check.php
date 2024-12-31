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
 * Parse nginx error log file
 *
 * @since 3.2.0
 * @return array|WP_Error Array of error data or WP_Error if file not accessible
 */
function air_helper_parse_nginx_error_log() {
  $log_path = '/var/log/nginx/error.log';

  if ( ! file_exists($log_path) || ! is_readable($log_path) ) {
		return new WP_Error(
		'log_not_accessible',
		__('Nginx error log is not accessible', 'air-helper')
		  );
  }

  // Get current domain from WP_HOME
  $current_domain = wp_parse_url(WP_HOME)['host'];

  $errors = [
    'notice' => 0,
    'warning' => 0,
    'fatal' => 0,
    'recent_errors' => [],
  ];

  // Read last 1000 lines of log file
  $lines = array_slice(file($log_path), -1000);

  foreach ($lines as $line ) {
		// Skip if line doesn't contain current domain
		if (strpos($line, $current_domain) === false ) {
		  continue;
			}

		if (strpos($line, 'PHP Notice') !== false ) {
		  ++$errors['notice'];
			} elseif (strpos($line, 'PHP Warning') !== false ) {
		  ++$errors['warning'];
			} elseif (strpos($line, 'PHP Fatal error') !== false ) {
		  ++$errors['fatal'];
			}

		// Store last 5 errors for display
		if (preg_match('/PHP (Notice|Warning|Fatal error):\s*(.+?)\s+in\s+/', $line, $matches) ) {
	  if (count($errors['recent_errors']) < 5 ) {
				  $errors['recent_errors'][] = [
					'type' => $matches[1],
					'message' => $matches[2],
					'timestamp' => preg_match('/^\[(.*?)\]/', $line, $time_match) ? $time_match[1] : '',
				  ];
		  }
			}
  }

  return $errors;
}

/**
 * Add PHP error log health checks
 *
 * @since  3.2.0
 */
add_filter('site_status_tests', 'air_helper_add_error_log_health_checks');
function air_helper_add_error_log_health_checks( $tests ) {
  $tests['direct']['php_error_log'] = [
    'label' => __('PHP Error Log', 'air-helper'),
    'test' => 'air_helper_error_log_health_test',
  ];

  return $tests;
}

/**
 * Perform the error log health test
 */
function air_helper_error_log_health_test() {
  $result = [
    'label' => __('PHP Error Log Status', 'air-helper'),
    'status' => 'good',
    'badge' => [
      'label' => __('Performance', 'air-helper'),
      'color' => 'blue',
    ],
    'description' => '',
    'actions' => '',
    'test' => 'php_error_log',
  ];

  $error_data = air_helper_parse_nginx_error_log();

  if (is_wp_error($error_data) ) {
		$result['status'] = 'recommended';
		$result['label'] = __('Cannot access PHP error log', 'air-helper');
		$result['description'] = sprintf(
		'<p>%s</p>',
		$error_data->get_error_message()
		  );
		  return $result;
  }

  // Set status based on error counts
  if ($error_data['fatal'] > 0 ) {
		$result['status'] = 'critical';
  } elseif ($error_data['warning'] > 10 ) {
		$result['status'] = 'recommended';
  }

  // Build description
  $description = sprintf(
    '<p>%s</p>',
    sprintf(
      __('Found: %1$d fatal errors, %2$d warnings, and %3$d notices', 'air-helper'),
      $error_data['fatal'],
      $error_data['warning'],
      $error_data['notice']
    )
  );

  if ( ! empty($error_data['recent_errors']) ) {
		$description .= '<p>' . __('Recent errors:', 'air-helper') . '</p><ul>';
		foreach ($error_data['recent_errors'] as $error ) {
		  $description .= sprintf(
			'<li><strong>%s:</strong> %s</li>',
			esc_html($error['type']),
			esc_html($error['message'])
		  );
			}
		$description .= '</ul>';
  }

  $result['description'] = $description;
  return $result;
}

/**
 * Add project development errors dashboard widget (T-24040)
 *
 * @since  3.2.0
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
 * Render PHP Errors dashboard widget (T-24040)
 *
 * @since  3.2.0
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
      esc_html__( 'Notices', 'air-helper' ),
      esc_html( $error_data['notice'] )
    );

		echo '<ul class="recent-errors">';
		foreach ( $error_data['recent_errors'] as $error ) {
		  printf(
			'<li class="error-type-%s">
          <span class="error-time">%s</span>
          <strong>%s:</strong>
          <code>%s</code>
        </li>',
			sanitize_html_class( strtolower( $error['type'] ) ),
			esc_html( $error['timestamp'] ),
			esc_html( $error['type'] ),
			esc_html( $error['message'] )
		  );
			}
		echo '</ul>';
  }

  // Add link to Site Health
  printf(
    '<p class="error-details-link"><a href="%s">%s</a></p>',
    esc_url( admin_url( 'site-health.php' ) ),
    esc_html__( 'View detailed error information in Site Health', 'air-helper' )
  );
}

/**
 * Store error log statistics in database
 *
 * @since  3.2.0
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
 * @since  3.2.0
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

