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
