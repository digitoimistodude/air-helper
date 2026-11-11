<?php
/**
 * Send nginx-helper full cache purges through nginx.
 *
 * The nginx-helper plugin empties the FastCGI cache by deleting the files in PHP.
 * nginx still remembers those files and logs an unlink() crit line for
 * each one it later tries to expire. A PURGE to the local /purgeall
 * location lets nginx empty the cache itself. When nginx does not answer
 * 200, nginx-helper deletes the files as before.
 *
 * @package air-helper
 */

/**
 * Ask nginx to empty this site's cache.
 *
 * @since 3.2.19
 * @return bool True when nginx emptied the cache.
 */
function air_helper_nginx_purge_all() {
  $response = wp_remote_request( 'http://127.0.0.1/purgeall', [
    'method'      => 'PURGE',
    'headers'     => [ 'Host' => wp_parse_url( home_url(), PHP_URL_HOST ) ],
    'timeout'     => 10,
    'redirection' => 0,
  ] );

  return 200 === wp_remote_retrieve_response_code( $response );
} // end air_helper_nginx_purge_all

/**
 * Swap nginx-helper's FastCGI purger for one that purges everything through nginx.
 *
 * The admin bar buttons, WP-CLI and themes call the global $nginx_purger,
 * other plugins call the rt_nginx_helper_purge_all action.
 *
 * Turn off by using `add_filter( 'air_helper_nginx_purgeall', '__return_false' )`
 *
 * @since 3.2.19
 */
add_action( 'plugins_loaded', 'air_helper_replace_nginx_purger' );
function air_helper_replace_nginx_purger() {
  global $nginx_purger;

  if ( ! $nginx_purger || 'FastCGI_Purger' !== get_class( $nginx_purger ) || ! apply_filters( 'air_helper_nginx_purgeall', true ) ) {
    return;
  }

  $old = $nginx_purger;

  $nginx_purger = new class() extends FastCGI_Purger { // phpcs:ignore Universal.Classes.DisallowAnonClassParentheses.Found
    public function purge_all() {
      if ( ! air_helper_nginx_purge_all() ) {
        parent::purge_all();
        return;
      }

      $this->log( '* * * * *' );
      $this->log( '* Purged Everything via nginx /purgeall!' );
      $this->log( '* * * * *' );

      do_action( 'rt_nginx_helper_after_fastcgi_purge_all' );
    }
  };

  if ( false !== has_action( 'rt_nginx_helper_purge_all', [ $old, 'purge_all' ] ) ) {
    remove_action( 'rt_nginx_helper_purge_all', [ $old, 'purge_all' ] );
    add_action( 'rt_nginx_helper_purge_all', [ $nginx_purger, 'purge_all' ] );
  }
} // end air_helper_replace_nginx_purger
