<?php
/**
 * Prioritized security related actions.
 *
 * @package air-helper
 */

/**
 * Stop user enumeration via ?author=(init) URLs.
 * Idea by Davide Giunchi, from the plugin "Stop User Enumeration".
 *
 * Disable with `remove_action( 'init', 'air_helper_stop_user_enumeration' )`
 *
 * @since 1.7.4
 */
add_action( 'init', 'air_helper_stop_user_enumeration', 10 );
function air_helper_stop_user_enumeration() {
  if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
    if ( preg_match( '/(wp-comments-post)/', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) === 0 && ! empty( $_REQUEST['author'] ) ) { // phpcs:ignore
      wp_safe_redirect( home_url() );
      exit;
    }
  }
} // end air_helper_stop_user_enumeration

/**
 * Add a honeypot to the login form.
 *
 * For the login to succeed, the field value must be exactly
 * six characters long and prefixed with the correct three letters.
 * The prefix cannot be older than 30 minutes. After the prefix, the following
 * three characters can be anything. Store the prefix and generation time
 * in the options table for later use.
 *
 * Append the three characters with JavaScript and hide the field. In case
 * the user has JavaScript disabled, the label explains what the input is and
 * what to do with it. This is unlikely to happen, but better safe than sorry.
 *
 * @since 1.9.0
 */
add_action( 'login_form', 'air_helper_login_honeypot_form', 99 );
function air_helper_login_honeypot_form() {
  // Generate new prefix to honeypot if it's older than 30 minutes
  $prefix = get_option( 'air_helper_login_honeypot' );
  if ( ! $prefix || $prefix['generated'] < strtotime( '-30 minutes' ) ) {
    $prefix = air_helper_login_honeypot_reset_prefix();
  } ?>

  <p id="air_lh_name_field" class="air_lh_name_field">
    <label for="air_lh_name"><?php echo esc_html( 'Append three letters to this input', 'air-helper' ); // phpcs:ignore ?></label><br />
    <input type="text" name="air_lh_name" id="air_lh_name" class="input" value="<?php echo esc_attr( $prefix['prefix'] ); ?>" size="20" autocomplete="off" />
  </p>

  <script type="text/javascript">
    var text = document.getElementById('air_lh_name');
    text.value += '<?php echo esc_attr( wp_generate_password( 3, false ) ); ?>';
    document.getElementById('air_lh_name_field').style.display = 'none';
  </script>
<?php } // end air_helper_login_honeypot_form

/**
 * Check if the login form honeypot is valid.
 *
 * If the honeypot fails, log the attempt to the combined login log and prevent Simple History from logging it.
 *
 * @since 1.9.0
 * @param mixed  $user      If the user is authenticated. WP_Error or null otherwise.
 * @param string $username  Username or email address.
 * @param string $password  User password.
 * @return mixed            WP_User object if the honeypot passed, null otherwise.
 *
 * phpcs:disable WordPress.Security.NonceVerification.Missing
 */
add_action( 'authenticate', 'air_helper_login_honeypot_check', 29, 3 );
function air_helper_login_honeypot_check( $user, $username, $password ) { // phpcs:ignore
  // field is required
  if ( ! empty( $_POST ) ) {
    if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
      return $user;
    }

    if ( ! isset( $_POST['air_lh_name'] ) ) {
      return null;
    }

    // Field can't be empty
    if ( empty( $_POST['air_lh_name'] ) ) {
      air_helper_act_on_login_fail( 'honeypot_empty' );
      return null;
    }

    // Value needs to be exactly six charters long
    if ( 6 !== mb_strlen( sanitize_text_field( wp_unslash( $_POST['air_lh_name'] ) ) ) ) {
      air_helper_act_on_login_fail( 'honeypot_length' );
      return null;
    }

    // Check the database
    $prefix = get_option( 'air_helper_login_honeypot' );

    // The prefix is too old
    if ( $prefix['generated'] < strtotime( '-30 minutes' ) ) {
      air_helper_act_on_login_fail( 'honeypot_prefix_old' );
      return null;
    }

    // The prefix is incorrect
    if ( substr( sanitize_text_field( wp_unslash( $_POST['air_lh_name'] ) ), 0, 3 ) !== $prefix['prefix'] ) {
      air_helper_act_on_login_fail( 'honeypot_prefix_wrong' );
      return null;
    }
  }

  return $user;
} // end air_helper_login_honeypot_check
// phpcs: enable WordPress.Security.NonceVerification.Missing

/**
 *  Reset login form honeypot prefix on call and after succesfull login.
 *
 *  @since  1.9.0
 *  @return array  prexif generation time an prefix itself
 */
add_action( 'wp_login', 'air_helper_login_honeypot_reset_prefix' );
function air_helper_login_honeypot_reset_prefix() {
  $prefix = [
    'generated' => time(),
    'prefix'    => wp_generate_password( 3, false ),
  ];

  update_option( 'air_helper_login_honeypot', $prefix, false );

  return $prefix;
} // end air_helper_login_honeypot_reset_prefix

/**
 * Unify and generalize the login error message so it does not reveal any details about what went wrong.
 *
 * Disable with `remove_action( 'login_errors', 'air_helper_login_errors' )`
 *
 * @since 1.8.0
 * @return string Message to display when login fails.
 */
add_filter( 'login_errors', 'air_helper_login_errors' );
function air_helper_login_errors() {
  return __( '<b>Login failed.</b> Please contact your site admin or agency if you continue having problems.', 'air-helper' );
} // end air_helper_login_errors

/**
 * Capture certain Simple History login-related messages and redirect them
 * to the combined login log instead of the Simple History database. If no log
 * message redirects are required, disable the entire combined log with
 * `add_filter( 'air_helper_write_to_combined_login_log', '__return_false' )`.
 *
 * Modify which messages are redirected using "air_helper_simplehistory_message_keys_to_combined_login_log".
 *
 * @since 2.16.0
 * @param boolean $do_log   Whether the message should be logged. Default true.
 * @param mixed   $level    The log level. Default "info".
 * @param string  $message  The log message. Default "".
 * @param array   $context  The log context. Default empty array.
 * @return boolean          Whether the message should be logged. Defaults to $do_log.
 */
add_action( 'simple_history/log/do_log', 'air_helper_maybe_redirect_simplehistory_to_combined_log', 10, 4 );
function air_helper_maybe_redirect_simplehistory_to_combined_log( $do_log, $level, $message, $context ) {
  if ( ! isset( $context['_message_key'] ) ) {
    return $do_log;
  }

  // Allow filtering of which Simple History message keys should be redirected to the combined log.
  $message_keys_to_combined_log = apply_filters( 'air_helper_simplehistory_message_keys_to_combined_login_log', [
    'user_unknown_login_failed' => true,
  ] );

  // Check whether this type of message should go to the combined log, based on the message key's existence in the array.
  if ( ! array_key_exists( $context['_message_key'], $message_keys_to_combined_log ) ) {
    return $do_log;
  }

  // Check whether this type of message should still go to the combined log, based on the message key being enabled (true) in the array.
  if ( ! $message_keys_to_combined_log[ $context['_message_key'] ] ) {
    return $do_log;
  }

  // Maybe replace the username in the message if it exists in the context.
  // This is used for the "user_unknown_login_failed" message key.
  if ( isset( $context['failed_username'] ) && ! empty( $context['failed_username'] ) ) {
    $message = str_replace( '{failed_username}', $context['failed_username'], $message );
  }

  // maybe replace username in messge if exists in context
  // this type is used on "user_login_failed" message key
  if ( isset( $context['login'] ) && ! empty( $context['login'] ) ) {
    $message = str_replace( '{login}', $context['login'], $message );
  }

  // Try to write to a logfile
  $wrote = air_helper_write_combined_login_log( $message );

  // If write failed, let Simple History do its logging
  if ( false === $wrote ) {
    return $do_log;
  }

  // Prevent default Simple history logging
  return false;
} // end air_helper_maybe_redirect_simplehistory_to_combined_log

/**
 * Act on login failures and prevent Simple History from doing its own logging.
 *
 * Currently used only when the login honeypot fails for one reason or another.
 *
 * If Simple History should remain active, disable the entire combined log with
 * `add_filter( 'air_helper_write_to_combined_login_log', '__return_false' )`
 *
 * @since 2.16.0
 * @param string $type Type of the failure.
 */
function air_helper_act_on_login_fail( $type ) {
  $messages_by_type = [
    'honeypot_empty'        => 'failed to login (air honeypot empty)',
    'honeypot_length'       => 'failed to login (air honeypot length)',
    'honeypot_prefix_old'   => 'failed to login (air honeypot old prefix)',
    'honeypot_prefix_wrong' => 'failed to login (air honeypot wrong prefix)',
  ];

  $wrote = air_helper_write_combined_login_log( $messages_by_type[ $type ] );
  if ( true === $wrote ) {
    add_filter( 'simple_history/log/do_log/SimpleUserLogger', '__return_false' );
  }
} // end air_helper_act_on_login_fail

/**
 * Try to write login-related messages to the combined server log.
 *
 * Disable this with `add_filter( 'air_helper_write_to_combined_login_log', '__return_false' )`.
 *
 * @since 2.16.0
 * @param string  $message The log message.
 * @return boolean         Whether the write to the combined log was successful.
 */
function air_helper_write_combined_login_log( $message ) {
  if ( ! apply_filters( 'air_helper_write_to_combined_login_log', true ) ) {
    return false;
  }

  $log_file = apply_filters( 'air_helper_combined_login_log_file', '/var/log/wordpress/wp-login.log' );

  // try to create the log file if it does not exist
  if ( ! file_exists( $log_file ) ) {
    touch( $log_file ); // phpcs:ignore
  }

  // bail if file creation failed
  if ( ! file_exists( $log_file ) ) {
    return false;
  }

  // bail if file is not writable
  if ( ! is_writable( $log_file ) ) { // phpcs:ignore
    return false;
  }

  // get visitor ip
  if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    $user_ip = $_SERVER['HTTP_CLIENT_IP']; // phpcs:ignore
  } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // phpcs:ignore
  } else {
    $user_ip = $_SERVER['REMOTE_ADDR']; // phpcs:ignore
  }

  // combine the message
  $write = wp_date( 'Y-m-d H:i:s' ) . " client: {$user_ip}";
  $write .= ', ' . mb_strtolower( $message );
  $write .= ', site ' . parse_url( get_home_url() )['host']; // phpcs:ignore

  // write to log
  return file_put_contents( $log_file, $write . "\n", FILE_APPEND ); // phpcs:ignore
} // end air_helper_write_combined_login_log

/**
 * Blacklist installing certain plugins that often have malicious intent.
 *
 * To modify the list of blacklisted plugins, use the following filter hook:
 *
 * function custom_modify_blacklisted_plugins( $blacklist ) {
 *   // Add or remove plugin slugs to the blacklist array
 *   $blacklist[] = 'another-plugin-slug';
 *   return $blacklist;
 * }
 * add_filter( 'modify_blacklisted_plugins', 'custom_modify_blacklisted_plugins' );
 *
 * @since 2.19.5
 */
add_filter( 'plugins_api_result', 'modify_plugin_search_results', 10, 3 );
add_action( 'admin_enqueue_scripts', 'enqueue_inline_js_for_plugin_page' );

function get_blacklisted_plugins() {
  // Default list of blacklisted plugins
  $blacklist = [
    'insert-headers-and-footers',
    'wp-file-manager',
    'woocommerce-multilingual',
    'sitepress-multilingual-cms',
    'wordfence',
  ];

  // Allow modification of the blacklisted plugins list via a custom filter hook
  return apply_filters( 'modify_blacklisted_plugins', $blacklist );
}

function modify_plugin_search_results( $res, $action, $args ) { // phpcs:ignore
  if ( 'query_plugins' === $action ) {
    $blacklist = [
      'insert-headers-and-footers',
      'wp-file-manager',
    ];

    foreach ( $res->plugins as $key => $plugin ) {
      if ( in_array( $plugin['slug'], $blacklist, true ) ) {
        $res->plugins[ $key ]['blacklisted'] = true;
        // Add a custom compatibility notice for blacklisted plugins
        $res->plugins[ $key ]['compatibility_warning'] = 'This plugin has been blacklisted and is not compatible with your current setup.';
      }
    }
  }
  return $res;
}

function enqueue_inline_js_for_plugin_page( $hook ) {
  if ( 'plugin-install.php' !== $hook ) {
    return;
  }
  ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      processPluginCards();

      // Set up the observer
      const observer = new MutationObserver(function(mutationsList) {
        for (let mutation of mutationsList) {
          if (mutation.type === 'childList') {
            // Check if any of the added nodes are plugin cards or contain plugin cards
            for (let node of mutation.addedNodes) {
              if (node.nodeType === 1) {
                if (node.classList?.contains('plugin-card') || node.querySelector('.plugin-card')) {
                  processPluginCards();
                  break; // Process once per mutation batch
                }
              }
            }
          }
        }
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      function processPluginCards() {
        var pluginCards = document.querySelectorAll('.plugin-card');

        // If no plugin cards, bail
        if (!pluginCards.length) {
          return;
        }

        pluginCards.forEach(function(card) {
          // Skip if already processed
          if (card.dataset.processed) return;

          // Get slug based on plugin-card-<slug> class
          var slug = card.className.split(' ').find(function(className) {
            return className.indexOf('plugin-card-') === 0;
          }).replace('plugin-card-', '');

          var blacklistedPlugins = <?php echo wp_json_encode( get_blacklisted_plugins() ); ?>;

          if (blacklistedPlugins.includes(slug)) {
            // Mark as processed
            card.dataset.processed = 'true';

            var installButton = card.querySelector('a.install-now');
            var compatibilityNotice = card.querySelector('.compatibility-compatible');

            // Notice
            var notice = document.createElement('div');
            notice.className = 'notice inline notice-error notice-alt';
            notice.innerHTML = '<p>This plugin cannot be installed for security reasons.</p>';

            // Add notice right inside card once
            if (!card.querySelector('.notice')) {
              card.insertBefore(notice, card.firstChild);
            }

            // Delete all other list items but first inside plugin-action-buttons
            var pluginActionButtons = card.querySelector('.plugin-action-buttons');
            var pluginActionButtonsListItems = pluginActionButtons.querySelectorAll('li');
            pluginActionButtonsListItems.forEach(function(listItem, index) {
              if (index !== 0) {
                listItem.remove();
              }
            });

            // Remove upload button
            var uploadButton = document.querySelector('a.upload-view-toggle');
            if (uploadButton) {
              uploadButton.remove();
            }

            // Remove href inside open-plugin-details-modal link
            var openPluginDetailsModal = card.querySelector('a.open-plugin-details-modal');
            if (openPluginDetailsModal) {
              openPluginDetailsModal.removeAttribute('href');
            }

            // Add pointer-events: none; to card
            card.style.pointerEvents = 'none';

            // Disable button
            if (installButton) {
              var disabledButton = document.createElement('button');
              disabledButton.className = 'button disabled';
              disabledButton.innerHTML = 'Cannot Install';
              disabledButton.disabled = true;

              installButton.replaceWith(disabledButton);
            }

            // Add compatibility notice
            if (compatibilityNotice) {
              var compatibilityNoticeElement = document.createElement('span');
              compatibilityNoticeElement.className = 'compatibility-incompatible';
              compatibilityNoticeElement.innerHTML = '<strong>Blacklisted</strong> plugin';

              compatibilityNotice.replaceWith(compatibilityNoticeElement);
            }
          }
        });
      }
    });
  </script>
  <?php
}

/**
 * Prevent access to plugins
 * Turn off by using `remove_action( 'admin_init', 'air_helper_prevent_access_to_plugins' );`
 *
 * @since 2.19.6
 */
add_action( 'admin_init', 'air_helper_prevent_access_to_plugins' );
function air_helper_prevent_access_to_plugins() {

  if ( ! air_helper_allow_user_to( 'plugins' ) ) {

    $not_allowed_plugin_pages = [
      'plugins.php',
      'plugin-install.php',
      'update-core.php',
    ];

    // If current admin page is plugins.php, strip the last part of the URL
    if ( in_array( $GLOBALS['pagenow'], $not_allowed_plugin_pages, true ) ) {
      wp_die( 'You are not allowed to access this page.' );
    }
  }
} // end air_helper_prevent_access_to_plugins
