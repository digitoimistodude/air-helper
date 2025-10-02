<?php
/**
 * Modify admin bar.
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
    'wpforms-menu',
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
function air_helper_adminbar_show_env_styles() { // phpcs:ignore Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace ?>
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

  // Check for proper capabilities in multisite
  if ( ! ( is_multisite() && is_super_admin() ) && ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // If none of the cache plugins we use have been activated, do not show the button
  $cache_plugins = air_helper_cache_plugins();
  $active_plugins = get_option( 'active_plugins' );

  // For multisite, also check network activated plugins
  if ( is_multisite() ) {
    $network_plugins = get_site_option( 'active_sitewide_plugins' );
    if ( ! empty( $network_plugins ) ) {
      $active_plugins = array_merge( $active_plugins, array_keys( $network_plugins ) );
    }
  }

  if ( ! array_intersect( $cache_plugins, $active_plugins ) ) {
    return;
  }

  $remove_items = [
    'autoptimize',
    'cache_enabler_clear_cache',
    'redis-cache',
    'wp-super-cache',
    'nginx-helper-purge-all',
    'delete-cache',
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
function air_helper_adminbar_hide_via_styles() { // phpcs:ignore Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace ?>
  <style>
    body.network-admin #dashboard_primary,
    body.network-admin #dashboard_objectcache,
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
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // Array to store flushed caches
  $flushed_caches = [];

  // Log the start of cache flush
  $log_action = wp_get_environment_type() === 'development' ? 'simple_history_log_debug' : 'simple_history_log';
  do_action( $log_action, 'Starting cache flush for all sites' );

  // Flush rewrites
  flush_rewrite_rules();

  // If multisite, iterate over each site and switch to it
  if ( is_multisite() ) {
    $sites = get_sites();
    do_action( $log_action, sprintf( 'Found %d sites to flush', count( $sites ) ) );

    foreach ( $sites as $site ) {
      switch_to_blog( $site->blog_id );
      $site_name = get_bloginfo( 'name' );
      do_action( $log_action, sprintf( 'Flushing cache for site: %s (ID: %d)', $site_name, $site->blog_id ) );

      // Call the cache flushing functions for each site
      flush_site_caches( true, $flushed_caches );

      restore_current_blog();
    }
  } else {
    flush_site_caches( false, $flushed_caches );
  }

  // Store flushed caches in a transient to display in the notice
  set_transient( 'flushed_caches', $flushed_caches, 60 );

  // Redirect back with parameters to show notice
  wp_safe_redirect( add_query_arg( 'action', 'flush_all_caches', wp_get_referer() ) );
  exit;
}

function flush_site_caches( $is_multisite, &$flushed_caches ) {
  global $wp_fastest_cache;

  $site_name = get_bloginfo( 'name' );
  $log_action = wp_get_environment_type() === 'development' ? 'simple_history_log_debug' : 'simple_history_log';
  do_action( $log_action, sprintf( 'Starting flush_site_caches for site: %s (ID: %d)', $site_name, get_current_blog_id() ) );

  // Flush Autoptimize
  if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
    if ( class_exists( 'autoptimizeCache' ) ) {
      autoptimizeCache::clearall();
      $flushed_caches[] = 'Autoptimize';
      do_action( $log_action, 'Flushed Autoptimize cache' );
    }
  }

  // Flush Cache Enabler
  if ( is_plugin_active( 'cache-enabler/cache-enabler.php' ) ) {
    if ( class_exists( 'Cache_Enabler' ) ) {
      Cache_Enabler::clear_total_cache();
      $flushed_caches[] = 'Cache Enabler';
      do_action( $log_action, 'Flushed Cache Enabler cache' );
    }
  }

  // Flush Object Cache Pro and Redis Cache
  if ( is_plugin_active( 'object-cache-pro/object-cache-pro.php' ) || is_plugin_active( 'redis-cache/redis-cache.php' ) ) {
    if ( function_exists( 'wp_cache_flush' ) ) {

      // In multisite, only flush the current site's cache
      if ( is_multisite() ) {
        wp_cache_flush( 'site' );
      } else {
        wp_cache_flush();
      }

      // Only add to flushed_caches if it's not already there
      if ( ! in_array( 'Redis/Object Cache Pro', $flushed_caches ) ) {
        $flushed_caches[] = 'Redis/Object Cache Pro';
      }

      do_action( $log_action, 'Flushed Redis/Object Cache Pro cache' );
    }
  }

  // Flush W3 Total Cache
  if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    w3tc_pgcache_flush();
    $flushed_caches[] = 'W3 Total Cache';
    do_action( $log_action, 'Flushed W3 Total Cache' );
  }

  // Flush WP Super Cache
  if ( function_exists( 'wp_cache_clean_cache' ) ) {
    global $file_prefix, $supercachedir;

    if ( empty( $supercachedir ) && function_exists( 'get_supercache_dir' ) ) {
        $supercachedir = get_supercache_dir();
    }

    wp_cache_clean_cache( $file_prefix );
    $flushed_caches[] = 'WP Super Cache';
    do_action( $log_action, 'Flushed WP Super Cache' );
  }

  // Flush WP Fastest Cache
  if ( method_exists( 'WpFastestCache', 'deleteCache' ) && ! empty( $wp_fastest_cache ) ) {
    $wp_fastest_cache->deleteCache( true );
    $flushed_caches[] = 'WP Fastest Cache';
    do_action( $log_action, 'Flushed WP Fastest Cache' );
  }

  // Flush Surge
  if ( is_plugin_active( 'surge/surge.php' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

    $fs = new WP_Filesystem_Direct( new StdClass() );
    $fs->rmdir( WP_CONTENT_DIR . '/cache/surge/', true );
    $flushed_caches[] = 'Surge';
    do_action( $log_action, 'Flushed Surge cache' );
  }

  // Flush nginx fastcgi cache
  if ( is_plugin_active( 'nginx-helper/nginx-helper.php' ) ) {
    do_action( 'rt_nginx_helper_purge_all' );
    $flushed_caches[] = 'Nginx fastcgi';
    do_action( $log_action, 'Flushed Nginx fastcgi cache' );
  }

  // Purge PHP OpCache
  if ( function_exists( 'opcache_reset' ) && opcache_get_status() !== false ) {
    opcache_reset();
    $flushed_caches[] = 'PHP OpCache';
    do_action( $log_action, 'Purged PHP OpCache' );
  }

  // Purge APCu Cache
  if ( function_exists( 'apcu_clear_cache' ) ) {
    apcu_clear_cache();
    $flushed_caches[] = 'APCu Cache';
    do_action( $log_action, 'Purged APCu Cache' );
  }
}

function air_helper_flush_all_caches_notice() {
  if ( ! isset( $_GET['action'] ) || 'flush_all_caches' !== $_GET['action'] ) { // phpcs:ignore
    return;
  }

  // Retrieve flushed caches from transient
  $flushed_caches = get_transient( 'flushed_caches' );
  delete_transient( 'flushed_caches' );

  ?>
  <div class="notice notice-success is-dismissible">
    <p><?php esc_html_e( 'All caches flushed', 'air-helper' ); ?><?php if ( ! empty( $flushed_caches ) ) : ?>: <?php echo esc_html( implode( ', ', $flushed_caches ) ); ?><?php endif; ?></p>
  </div>
  <?php
}

// Add the admin notice hook
add_action( 'admin_notices', 'air_helper_flush_all_caches_notice' );
