<?php
/**
 * Modify admin bar.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:14:34
 * @Last Modified by:   Roni Laukkarinen
 * @Last Modified time: 2024-03-08 17:46:09
 *
 * @package air-helper
 */

// Hide always all email address encoder notifications
define( 'EAE_DISABLE_NOTICES', apply_filters( 'air_helper_remove_eae_admin_bar', true ) );

/**
 * Clean up admin bar.
 *
 * Turn off by using `remove_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_bar_links', 'myprefix_override_air_helper_helper_remove_admin_bar_links' )`
 *
 * @since  0.1.0
 */
add_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' );
function air_helper_helper_remove_admin_bar_links() {
  global $wp_admin_bar;

  $remove_items = apply_filters( 'air_helper_helper_remove_admin_bar_links', [
    'wp-logo',
    'about',
    'wporg',
    'documentation',
    'support-forums',
    'feedback',
    'updates',
    'comments',
    'customize',
    'imagify',
  ] );

  foreach ( $remove_items as $item ) {
    $wp_admin_bar->remove_menu( $item );
  }
} // end air_helper_helper_remove_admin_bar_links

/**
 * Add environment marker to adminbar.
 *
 * Turn off by using `remove_action( 'admin_bar_menu', 'air_helper_adminbar_show_env' )`
 *
 * @since  1.1.0
 */
add_action( 'admin_bar_menu', 'air_helper_adminbar_show_env', 999 );
function air_helper_adminbar_show_env( $wp_admin_bar ) {
  // Default to production env
  $env = wp_get_environment_type();
  $class = 'air-helper-env-prod';

  if ( wp_get_environment_type() === 'staging' ) {
    $class = 'air-helper-env-stage';
  } elseif ( wp_get_environment_type() === 'development' ) {
    $env .= ' (DB ' . getenv( 'DB_HOST' ) . ')'; // On dev, show database
    $class = 'air-helper-env-dev';
  }

  $wp_admin_bar->add_node( [
    'id'    => 'airhelperenv',
    'title' => wp_sprintf( __( 'Environment: %s', 'air-helper' ), $env ),
    'href'  => '#',
    'meta'  => [
      'class' => $class,
    ],
  ] );
} // end air_helper_adminbar_show_env

/**
 * Add environment marker styles.
 *
 * Turn off by using `remove_action( 'admin_head', 'air_helper_adminbar_show_env_styles' )`
 *
 * @since  1.1.0
 */
add_action( 'admin_head', 'air_helper_adminbar_show_env_styles' );
add_action( 'wp_head', 'air_helper_adminbar_show_env_styles' );
function air_helper_adminbar_show_env_styles() { ?>
  <style>
    #wp-admin-bar-airhelperenv.air-helper-env-prod > a {
      background: #00bb00 !important;
      color: black !important;
    }

    #wp-admin-bar-airhelperenv.air-helper-env-stage > a {
      background: orange !important;
      color: black !important;
    }

    #wp-admin-bar-airhelperenv.air-helper-env-dev > a {
      background: red !important;
      color: black !important;
    }
  </style>
<?php } // end air_helper_adminbar_show_env_styles

/**
 * Define cache plugins we use
 *
 * Define cache plugins we use in production.
 *
 * @since 3.1.0
 */
function air_helper_cache_plugins() {
  return [
    'autoptimize/autoptimize.php',
    'object-cache-pro/object-cache-pro.php',
    'nginx-helper/nginx-helper.php',
    'wp-fastest-cache/wpFastestCache.php',
    'cache-enabler/cache-enabler.php',
    'wp-super-cache/wp-cache.php',
    'redis-cache/redis-cache.php',
  ];
}

/**
 * Flush all caches admin bar item
 *
 * Add general flush all caches button to admin bar.
 *
 * @since 3.1.0
 */
add_action( 'admin_bar_menu', 'air_helper_adminbar_flush_all_caches', 999 );
function air_helper_adminbar_flush_all_caches( $wp_admin_bar ) {

  // If none of the cache plugins we use have been activated, do not show the button
  $cache_plugins = air_helper_cache_plugins();
  $active_plugins = get_option( 'active_plugins' );

  if ( ! array_intersect( $cache_plugins, $active_plugins ) ) {
    return;
  }

  $wp_admin_bar->add_node( [
    'id'    => 'flushallcaches',
    'title' => __( 'Flush all caches', 'air-helper' ),
    'href'  => admin_url( 'admin-post.php?action=flush_all_caches' ),
    'meta'  => [
      'class' => 'flushallcaches',
    ],
  ] );
}

/**
 * Flush all caches
 *
 * Actions to flush all caches.
 *
 * @since 3.1.0
 */
add_action( 'admin_post_flush_all_caches', 'air_helper_flush_all_caches' );
function air_helper_flush_all_caches() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
    if ( ! class_exists( 'autoptimizeCache' ) ) {
      return;
    }
    $success = autoptimizeCache::clearall();
  }

  if ( is_plugin_active( 'object-cache-pro/object-cache-pro.php' ) ) {
    // Check if Object Cache Pro class exists
    if ( ! class_exists( 'RedisCachePro\Console\Commands' ) ) {
      return;
    }

    function flushRedis( $arguments, $options ) {
      $commands = new RedisCachePro\Console\Commands();
      $commands->flush( $arguments, $options );
    }

    // Run the flush command
    flushRedis( [], [] );
  }

  // Redirect back with parameters to show notice
  wp_safe_redirect( add_query_arg( 'action', 'flush_all_caches', wp_get_referer() ) );
  exit;
}

function air_helper_flush_all_caches_notice() {
  if ( ! isset( $_GET['action'] ) || 'flush_all_caches' !== $_GET['action'] ) {
    return;
  }

  ?>
  <div class="notice notice-success is-dismissible">
    <p><?php esc_html_e( 'All caches flushed.', 'air-helper' ); ?></p>
  </div>
  <?php
}

  // Show notice
  add_action( 'admin_notices', 'air_helper_flush_all_caches_notice', 999 );
