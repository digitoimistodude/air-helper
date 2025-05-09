<?php
/**
 * WordPress update related actions.
 *
 * @package air-helper
 */

/**
 *  Remove Update WP text from admin footer.
 *
 *  @since 1.3.0
 */
add_filter( 'update_footer', '__return_empty_string', 11 );

/**
 * Hide WP updates nag.
 *
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_wphidenag' )`
 *
 * @since 0.1.0
 */
add_action( 'admin_menu', 'air_helper_wphidenag' );
function air_helper_wphidenag() {
  remove_action( 'admin_notices', 'update_nag' );
} // end air_helper_wphidenag

/**
 * Hide all WP update nags with styles.
 *
 * Turn off by using `remove_action( 'admin_head', 'air_helper_hide_nag_styles' )`
 *
 * @since 1.0.0
 */
add_action( 'admin_head', 'air_helper_hide_nag_styles' );
function air_helper_hide_nag_styles() { // phpcs:ignore Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace ?>
  <style>
    .cookiebot-admin-notice-container,
    .update-nag,
    #yoast-first-time-configuration-notice,
    #wp-admin-bar-updates,
    #menu-plugins .update-plugins,
    .theme-info .notice,
    .wp-heading-inline .theme-count,
    table.plugins .update-message,
    .theme-browser .update-message,
    body.plugins-php ul.subsubsub li.upgrade {
      display: none !important;
      visibility: hidden !important;
    }

    .plugin-update-tr {
      height: 1px;
    }
  </style>
<?php } // end air_helper_hide_nag_styles

/**
 * Add styles for locked plugins
 *
 * @since 3.2.0
 */
add_action( 'admin_head', 'air_helper_locked_plugins_styles' );
function air_helper_locked_plugins_styles() {
  global $pagenow;
  if ( 'plugins.php' !== $pagenow ) {
    return;
  }

  $locked_plugins = air_helper_get_locked_plugins();
  if ( empty( $locked_plugins ) ) {
    return;
  }
  ?>
  <style>
    .plugins .locked th,
    .plugins .locked td {
      opacity: 0.6;
    }

    .plugins .locked .check-column {
      border-left: 4px solid #000 !important;
    }

    .plugins .locked a {
      color: #222 !important;
    }

    .plugins .locked .toggle-auto-update {
      display: none !important;
    }

    .plugins .locked .plugin-title strong::before {
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>');
      content: '';
      display: inline-block;
      height: 14px;
      margin-right: 5px;
      margin-top: 2px;
      background-size: contain;
      background-repeat: no-repeat;
      position: absolute;
      left: 20px;
      vertical-align: -1px;
      width: 14px;
    }

    @media screen and (max-width: 782px) {
      #wpbody-content .wp-list-table.plugins .locked .plugin-title.column-primary {
        position: unset;
      }

      .plugins .locked .plugin-title strong::before {
        height: 22px;
        width: 22px;
      }
    }

    /* Disable actions for locked plugins */
    .plugins .locked .row-actions,
    .plugins .locked .check-column input {
      display: none !important;
    }
  </style>
  <?php
}

/**
 * Add JavaScript enhancements for locked plugins
 *
 * @since 3.2.0
 */
add_action( 'admin_footer', 'air_helper_locked_plugins_js' );
function air_helper_locked_plugins_js() {
  global $pagenow;
  if ( 'plugins.php' !== $pagenow ) {
    return;
  }

  $locked_plugins = air_helper_get_locked_plugins();
  if ( empty( $locked_plugins ) ) {
    return;
  }
  ?>
  <script>
    window.addEventListener('load', function() {
      try {
        var lockedPlugins = <?php echo wp_json_encode( $locked_plugins ); ?>;

        if (lockedPlugins && lockedPlugins.length) {
          lockedPlugins.forEach(function(plugin) {
            var row = document.querySelector('tr[data-plugin="' + plugin + '"]');
            if (row) {
              row.classList.add('locked');
              var titleEl = row.querySelector('.plugin-title strong');
              if (titleEl) {
                titleEl.setAttribute('data-locked-text', '<?php esc_attr_e( 'Locked', 'air-helper' ); ?>');
              }
            }
          });
        }
      } catch (error) {
        console.error('Error in locked plugins script:', error);
      }
    });
  </script>
  <?php
}

/**
 * Remove locked plugins from update-core.php list
 *
 * @since 3.2.0
 * @param object $transient The pre-saved value of the update_plugins transient
 * @return object Modified transient value
 */
add_filter( 'site_transient_update_plugins', 'air_helper_filter_locked_plugin_updates' );
function air_helper_filter_locked_plugin_updates( $transient ) {
  if ( empty( $transient ) || ! is_object( $transient ) ) {
    return $transient;
  }

  $locked_plugins = air_helper_get_locked_plugins();

  // Remove locked plugins from updates list
  if ( ! empty( $transient->response ) ) {
    foreach ( $locked_plugins as $plugin ) {
      if ( isset( $transient->response[ $plugin ] ) ) {
        unset( $transient->response[ $plugin ] );
      }
    }
  }

  // Also remove from no_update list to hide them completely
  if ( ! empty( $transient->no_update ) ) {
    foreach ( $locked_plugins as $plugin ) {
      if ( isset( $transient->no_update[ $plugin ] ) ) {
        unset( $transient->no_update[ $plugin ] );
      }
    }
  }

  return $transient;
}
