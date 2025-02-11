<?php
/**
 * Limit access to certain parts of dashboard.
 *
 * @package air-helper
 */

/**
 * Clean up admin menu from stuff we usually don't need.
 *
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_helper_remove_admin_menu_links', 999 )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_menu_links', 'myprefix_override_air_helper_helper_remove_admin_menu_links' )`
 *
 * @since  0.1.0
 */
add_action( 'admin_init', 'air_helper_helper_remove_admin_menu_links' );
function air_helper_helper_remove_admin_menu_links() {
  // Core functions don't check if global $menu exists. Bail if global $menu doesn't exist. admin-post.php page for example.
  if ( ! array_key_exists( 'menu', $GLOBALS ) ) {
    return;
  }

  $remove_items = apply_filters( 'air_helper_helper_remove_admin_menu_links', [
    'edit-comments.php',
    'themes.php?page=editcss',
    'admin.php?page=jetpack',
  ] );

  foreach ( $remove_items as $item ) {
    remove_menu_page( $item );
  }

  $remove_items = apply_filters( 'air_helper_helper_remove_admin_submenu_links', [
    'index.php' => [
      'update-core.php',
    ],
    'themes.php' => [
      'widgets.php',
    ],
    'options-general.php' => [
      'mailgun-lists',
    ],
  ] );

  foreach ( $remove_items as $parent => $items ) {
    foreach ( $items as $item ) {
      remove_submenu_page( $parent, $item );
    }
  }
} // end air_helper_helper_remove_admin_menu_links

/**
 *  Remove plugins page from admin menu, execpt for users with spesific domain or override in user meta.
 *
 *  Turn off by using `remove_filter( 'air_helper_helper_remove_admin_menu_links', 'air_helper_maybe_remove_plugins_from_admin_menu' )`
 *
 *  @since  1.3.0
 *  @param  array $menu_links pages to remove from admin menu.
 */
add_filter( 'air_helper_helper_remove_admin_menu_links', 'air_helper_maybe_remove_plugins_from_admin_menu' );
function air_helper_maybe_remove_plugins_from_admin_menu( $menu_links ) {
  // Bail if user is allowed to enter plugins
  if ( air_helper_allow_user_to( 'plugins' ) ) {
    return $menu_links;
  }

  $menu_links[] = 'plugins.php';

  return $menu_links;
} // end air_helper_maybe_remove_plugins_from_admin_menu

/**
 * Remove plugins page from multisite admin menu, execpt for users with spesific domain or override in user meta.
 *
 * Turn off by using `remove_filter( 'admin_bar_menu', 'air_helper_maybe_remove_plugins_from_network_admin_menu', 999 )`
 *
 * @since 2.11.0
 */
add_action( 'admin_bar_menu', 'air_helper_maybe_remove_plugins_from_network_admin_menu', 999 );
function air_helper_maybe_remove_plugins_from_network_admin_menu() {
  // Bail if user is allowed to enter plugins
  if ( air_helper_allow_user_to( 'plugins' ) ) {
    return;
  }

  global $wp_admin_bar;
  $wp_admin_bar->remove_node( 'network-admin-p' );
} // end air_helper_maybe_remove_plugins_from_network_admin_menu

add_action( 'admin_menu', 'dashboard_remove_menu_pages' );
function dashboard_remove_menu_pages() {
  add_menu_page( __( 'Menus' ), __( 'Menus' ), 'edit_theme_options', 'nav-menus.php', '', 'dashicons-menu-alt3', 60 );
} // end dashboard_remove_menu_pages

/**
 * Move menu edit link to top level of admin menu. Also maybe hide
 * themes view link if not specifically allowed for the current user.
 *
 * @since 2.17.0
 */
add_action( 'admin_init', 'air_helper_move_nav_menus_toplevel', 8 );
function air_helper_move_nav_menus_toplevel() {
  // If plugin was activated before version 2.17.0, do not change behaviour
  if ( air_helper_activated_at_version() < 21700 ) {
    return;
  }

  // Always add nav-menus.php to toplevel
  add_action( 'admin_menu', function () {
    add_menu_page( __( 'Menus' ), __( 'Menus' ), 'edit_theme_options', 'nav-menus.php', '', 'dashicons-menu-alt3', 60 );
  } );

  // Maybe hide themes.php from user
  add_filter( 'air_helper_helper_remove_admin_menu_links', function ( $items ) {
    if ( air_helper_allow_user_to( 'themes' ) ) {
      return $items;
    }

    $items[] = 'themes.php';
    return $items;
  } );
} // end air_helper_move_nav_menus_toplevel

/**
 * Move intrusive plugin settings under Settings menu.
 *
 * @since 3.1.5
 */
add_action('admin_menu', 'air_helper_move_optimole_menu', 999);
function air_helper_move_optimole_menu() {
  // Remove the top level menu for intrusive plugins
  remove_menu_page('optimole');
  remove_menu_page('blc_dash');

  // Add Optimole back as a submenu under Settings
  add_submenu_page(
    'options-general.php', // Parent slug
    'Optimole', // Page title
    'Optimole', // Menu title
    'manage_options', // Capability
    'admin.php?page=optimole' // Menu slug
  );

  // Add Broken Link Checker back as a submenu under Settings
  add_submenu_page(
    'options-general.php', // Parent slug
    'Broken Link Checker', // Page title
    'Broken Link Checker', // Menu title
    'manage_options', // Capability
    'admin.php?page=blc_local' // Menu slug
  );
}

/**
 * Get list of locked plugins that cannot be modified.
 *
 * To lock plugins from modifications in wp-admin, add this to your theme's functions.php:
 *
 * // Lock specific plugins from modifications
 * add_filter('air_helper_locked_plugins', function($plugins) {
 *   return [
 *     'advanced-custom-fields-pro/acf.php',    // Plugin folder/file.php
 *     'air-helper/air-helper.php',             // Plugin folder/file.php
 *     'plugin-folder/plugin-file.php',         // Plugin folder/file.php
 *   ];
 * });
 *
 * Locked plugins:
 * - Cannot be deactivated
 * - Cannot be updated through wp-admin
 * - Cannot be deleted
 * - Are hidden from update-core.php
 * - Show a "locked" indicator in plugins.php
 * - Have a separate "Locked" filter view in plugins.php
 *
 * @since 3.1.12
 * @return array Array of plugin basenames that should be locked
 */
function air_helper_get_locked_plugins() {
  return apply_filters('air_helper_locked_plugins', []);
}

/**
 * Add locked status to plugins list
 *
 * @since 3.1.12
 */
add_filter( 'views_plugins', 'air_helper_add_locked_plugins_status' );
function air_helper_add_locked_plugins_status( $views ) {
  $locked_plugins = air_helper_get_locked_plugins();
  $count = count($locked_plugins);

  $class = '';
  if ( isset( $_GET['plugin_status'] ) && 'locked' === $_GET['plugin_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $class = 'current';
  }

  $views['locked'] = sprintf(
    '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
    admin_url('plugins.php?plugin_status=locked'),
    $class,
    __('Locked', 'air-helper'),
    $count
  );

  return $views;
}

/**
 * Filter plugin list to show only locked plugins when locked status is selected
 *
 * @since 3.1.12
 */
add_filter( 'all_plugins', 'air_helper_filter_locked_plugins' );
function air_helper_filter_locked_plugins( $plugins ) {
  if ( ! isset( $_GET['plugin_status'] ) || 'locked' !== $_GET['plugin_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return $plugins;
  }

  $locked_plugins = air_helper_get_locked_plugins();
  return array_intersect_key($plugins, array_flip($locked_plugins));
}

/**
 * Prevent modifications to locked plugins
 *
 * @since 3.1.12
 */
add_filter( 'plugin_action_links', 'air_helper_remove_locked_plugin_actions', 20, 4 );
function air_helper_remove_locked_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
  if ( in_array($plugin_file, air_helper_get_locked_plugins(), true) ) { // phpcs:ignore WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis
    return [];
  }

  return $actions;
}

add_filter( 'bulk_actions-plugins', 'air_helper_remove_locked_bulk_actions' );
function air_helper_remove_locked_bulk_actions( $actions ) {
  if ( isset( $_GET['plugin_status'] ) && 'locked' === $_GET['plugin_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return [];
  }
  return $actions;
}

add_filter( 'admin_body_class', 'air_helper_add_locked_plugins_body_class' );
function air_helper_add_locked_plugins_body_class( $classes ) {
  global $pagenow;

  if ( 'plugins.php' !== $pagenow ) {
    $locked_plugins = air_helper_get_locked_plugins();
    foreach ( $locked_plugins as $plugin ) {
      $classes .= ' plugin-locked-' . sanitize_html_class(basename($plugin, '.php'));
    }
  }
  return $classes;
}
