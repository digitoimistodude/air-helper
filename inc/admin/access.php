<?php
/**
 * Limit access to certaing parts of dashboard.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:15:47
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2022-11-09 16:10:05
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
      'mailgun-lists'
    ]
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
  // remove_submenu_page( 'themes.php', 'nav-menus.php' );
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
  add_action( 'admin_menu', function() {
    add_menu_page( __( 'Menus' ), __( 'Menus' ), 'edit_theme_options', 'nav-menus.php', '' , 'dashicons-menu-alt3', 60 );
  } );

  // Maybe hide themes.php from user
  add_filter( 'air_helper_helper_remove_admin_menu_links', function( $items ) {
    if ( air_helper_allow_user_to( 'themes' ) ) {
      return $items;
    }

    $items[] = 'themes.php';
    return $items;
  } );
} // end air_helper_move_nav_menus_toplevel
