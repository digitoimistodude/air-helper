<?php
/**
 * Collection of miscellaneous actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:03:27
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-12 14:28:57
 *
 * @package air-helper
 */

/**
 * Disable emojicons.
 *
 * Turn off by using `remove_action( 'init', 'air_helper_helper_disable_wp_emojicons' )`
 *
 * @since  0.1.0
 * @link http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
 */
add_action( 'init', 'air_helper_helper_disable_wp_emojicons' );
function air_helper_helper_disable_wp_emojicons() {
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

  // Disable classic smilies
  add_filter( 'option_use_smilies', '__return_false' );
  add_filter( 'tiny_mce_plugins', 'air_helper_helper_disable_emojicons_tinymce' );
} // end air_helper_helper_disable_wp_emojicons

/**
 * Disable emojicons.
 *
 * @since 0.1.0
 * @param array $plugins Plugins.
 */
function air_helper_helper_disable_emojicons_tinymce( $plugins ) {
  if ( is_array( $plugins ) ) {
    return array_diff( $plugins, array( 'wpemoji' ) );
  } else {
    return [];
  }
} // end air_helper_helper_disable_emojicons_tinymce

/**
 *  Strip unwanted html tags from titles
 *
 *  Turn off by using `remove_filter( 'nav_menu_item_title', 'air_helper_strip_tags_menu_item' )`
 *  Turn off by using `remove_filter( 'the_title', 'air_helper_strip_tags_menu_item' )`
 *
 *  @since  1.4.1
 *  @param  string $title title to strip.
 *  @param  mixed  $arg_2 whatever filter can pass.
 *  @param  mixed  $arg_3 whatever filter can pass.
 *  @param  mixed  $arg_4 whatever filter can pass.
 */
add_filter( 'nav_menu_item_title', 'air_helper_strip_tags_menu_item', 10, 4 );
add_filter( 'the_title', 'air_helper_strip_tags_menu_item', 10, 2 );
function air_helper_strip_tags_menu_item( $title, $arg_2 = null, $arg_3 = null, $arg_4 = null ) {
  return strip_tags( $title, apply_filters( 'air_helper_allowed_tags_in_title', '<br><em><b><strong>' ) );
} // end air_helper_strip_tags_menu_item

/**
 * Add support for correct UTF8 orderby for post_title and term name (äöå).
 *
 * Turn off by using `remove_filter( 'init', 'air_helper_orderby_fix' )`
 * Props Teemu Suoranta https://gist.github.com/TeemuSuoranta/2174f78f37248aeef483526d1c5d176f
 *
 *  @since  1.5.0
 *  @return string ordering clause for query
 */
add_filter( 'init', 'air_helper_orderby_fix' );
function air_helper_orderby_fix() {
  /**
   * Add support for correct UTF8 orderby for post_title and term name (äöå).
   *
   *  @since  1.5.0
   *  @param string $orderby ordering clause for query
   *  @return string ordering clause for query
   */
  add_filter( 'posts_orderby', function( $orderby ) use ( $wpdb ) {
    if ( strstr( $orderby, 'post_title' ) ) {
      $order        = ( strstr( $orderby, 'post_title ASC' ) ? 'ASC' : 'DESC' );
      $old_orderby  = $wpdb->posts . '.post_title ' . $order;
      $utf8_orderby = 'CONVERT ( LCASE(' . $wpdb->posts . '.post_title) USING utf8) COLLATE utf8_bin ' . $order;

      // replace orderby clause in $orderby.
      $orderby = str_replace( $old_orderby, $utf8_orderby, $orderby );
    }

    return $orderby;
  } );

  /**
   * Add support for correct UTF8 orderby for term name (äöå).
   *
   *  @since  1.5.0
   *  @param string $orderby ordering clause for terms query
   *  @param array  $this_query_vars an array of terms query arguments
   *  @param array  $this_query_vars_taxonomy an array of taxonomies
   *  @return string ordering clause for terms query
   */
  add_filter( 'get_terms_orderby', function( $orderby, $this_query_vars, $this_query_vars_taxonomy ) {
    if ( strstr( $orderby, 't.name' ) ) {
      $old_orderby  = 't.name';
      $utf8_orderby = 'CONVERT ( LCASE(t.name) USING utf8) COLLATE utf8_bin ';

      // replace orderby clause in $orderby.
      $orderby = str_replace( $old_orderby, $utf8_orderby, $orderby );
    }

    return $orderby;
  }, 10, 3);
} // end air_helper_orderby_fix

/**
 * Add instant.page just-in-time preloading script to footer.
 *
 * Disble using `remove_action( 'wp_footer', 'air_helper_enqueue_instantpage_script', 50 )`
 *
 * @since 5.0.0
 * phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
 */
add_action( 'wp_footer', 'air_helper_enqueue_instantpage_script', 50 );
function air_helper_enqueue_instantpage_script() { ?>
  <script src="//instant.page/3.0.0" type="module" defer integrity="sha384-OeDn4XE77tdHo8pGtE1apMPmAipjoxUQ++eeJa6EtJCfHlvijigWiJpD7VDPWXV1"></script>
<?php } // end air_helper_enqueue_instantpage_script
// phpcs:enable
