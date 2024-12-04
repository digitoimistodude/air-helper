<?php
/**
 * Commenting and pingback related.
 *
 * @package air-helper
 */

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 * Turn off by using `remove_action( 'wp_head', 'air_helper_pingback_header' )`
 *
 * @since  0.1.0
 */
add_action( 'wp_head', 'air_helper_pingback_header' );
function air_helper_pingback_header() {
  if ( is_singular() && pings_open() ) {
    echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
  }
} // end air_helper_pingback_heade


/**
 * Remove unnecessary WordPress injected .recentcomments
 *
 * @since 2.6.0
 */
add_action( 'widgets_init', 'air_helper_remove_recent_comments_style' );
function air_helper_remove_recent_comments_style() {
  if ( ! function_exists( 'air_helper_activated_at_version' ) ) {
    return;
  }

  if ( air_helper_activated_at_version() < 2600 ) {
    return;
  }

  global $wp_widget_factory;
  remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
} // end air_helper_remove_recent_comments_style

/**
 * Change comment notification recipient to admin email instead of post author. (T-18096)
 *
 * Turn off by using `remove_filter('comment_notification_recipients', 'air_helper_modify_comment_notification_recipient')`
 *
 * @since 3.1.2
 * @param array  $emails     An array of email addresses to receive a comment notification.
 * @param string $comment_id The comment ID as a numeric string.
 * @return array Modified array of notification recipients
 */
add_filter( 'comment_notification_recipients', 'air_helper_modify_comment_notification_recipient', 10, 2 );
function air_helper_modify_comment_notification_recipient( $emails, $comment_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
  // Replace all notification recipients with admin email
  return array( get_option( 'admin_email' ) );
}
