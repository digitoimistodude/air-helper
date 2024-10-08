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
    'surge/surge.php',
  ];
}

/**
 * Flush all caches - admin bar item
 *
 * Add general flush all caches button to admin bar.
 *
 * @since 3.1.0
 */
add_action( 'admin_bar_menu', 'air_helper_adminbar_flush_all_caches', 999 );
function air_helper_adminbar_flush_all_caches( $wp_admin_bar ) {
  global $wp_admin_bar;

  // If none of the cache plugins we use have been activated, do not show the button
  $cache_plugins = air_helper_cache_plugins();
  $active_plugins = get_option( 'active_plugins' );

  if ( ! array_intersect( $cache_plugins, $active_plugins ) ) {
    return;
  }

  $remove_items = [
    'autoptimize',
    'cache_enabler_clear_cache',
    'redis-cache',
    'wp-super-cache',
    'nginx-helper-purge-all',
  ];

  foreach ( $remove_items as $item ) {
    $wp_admin_bar->remove_menu( $item );
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
 * Hide other cache buttons
 *
 * Hide those cache buttons in admin bar that are called wrong and cannot be removed via a hook.
 *
 * @since 3.1.0
 */
add_action( 'admin_head', 'air_helper_adminbar_hide_via_styles', 999 );
add_action( 'wp_head', 'air_helper_adminbar_hide_via_styles', 999 );
function air_helper_adminbar_hide_via_styles() { ?>
  <style>
    body #wp-admin-bar-wpfc-toolbar-parent {
      display: none !important;
    }
  </style>
<?php } // end air_helper_adminbar_hide_via_styles

/**
 * Flush all caches
 *
 * Actions to flush all caches, including rewrites.
 *
 * @since 3.1.0
 */
add_action( 'admin_post_flush_all_caches', 'air_helper_flush_all_caches' );
function air_helper_flush_all_caches() {
  global $wp_fastest_cache;

  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // Flush rewrites
  flush_rewrite_rules();

  // Flush Autoptimize
  if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
    if ( class_exists( 'autoptimizeCache' ) ) {
      autoptimizeCache::clearall();
    }
  }

  // Flush Cache Enabler
  if ( is_plugin_active( 'cache-enabler/cache-enabler.php' ) ) {
    if ( class_exists( 'Cache_Enabler' ) ) {
      Cache_Enabler::clear_total_cache();
    }
  }

  // Flush Object Cache Pro and Redis Cache
  if ( is_plugin_active( 'object-cache-pro/object-cache-pro.php' || is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
    // Completely clear object cache and send a message to redis monitor log
    if ( function_exists( 'wp_cache_flush' ) ) {
      wp_cache_flush();
    }
  }

  // Flush W3 Total Cache
  if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    w3tc_pgcache_flush();
  }

  // Flush WP Super Cache
  if ( function_exists( 'wp_cache_clean_cache' ) ) {
    global $file_prefix, $supercachedir;

    if ( empty( $supercachedir ) && function_exists( 'get_supercache_dir' ) ) {
        $supercachedir = get_supercache_dir();
    }

    wp_cache_clean_cache( $file_prefix );
  }

  // Flush WP Fastest Cache
  if ( method_exists( 'WpFastestCache', 'deleteCache' ) && ! empty( $wp_fastest_cache ) ) {
    $wp_fastest_cache->deleteCache( true );
  }

  // Flush Surge
  if ( is_plugin_active( 'surge/surge.php' ) ) {
    // Remove cache dir via WP Filesystem
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

    $fs = new WP_Filesystem_Direct( new StdClass() );
    $r = $fs->rmdir( WP_CONTENT_DIR . '/cache/surge/', true );
  }

  // Flush nginx fastcgi cache
  if ( is_plugin_active( 'nginx-helper/nginx-helper.php' ) ) {
    // phpcs:disable
    // Use: $this->loader->add_action( 'rt_nginx_helper_purge_all', $nginx_purger, 'purge_all' );
    // phpcs:enable
    do_action( 'rt_nginx_helper_purge_all' );
  }

  // Redirect back with parameters to show notice
  wp_safe_redirect( add_query_arg( 'action', 'flush_all_caches', wp_get_referer() ) );
  exit;
}

function air_helper_flush_all_caches_notice() {
  // WordPress provides these GET parameters, so we can safely check for them
  if ( ! isset( $_GET['action'] ) || 'flush_all_caches' !== $_GET['action'] ) { // phpcs:ignore
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
