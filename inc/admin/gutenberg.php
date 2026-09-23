<?php
/**
 * Gutenberg editor related hooks.
 *
 * Typically all changes should be done in the theme,
 * but sometimes we need to distribute global fixes.
 * In those situations, this file comes handy.
 *
 * @package air-helper
 */

/**
 * Make compatibility changes to allowed blocks.
 *
 * @since  2.17.0
 */
add_filter( 'allowed_block_types_all', 'air_helper_gutenberg_allowed_blocks', 50 );
function air_helper_gutenberg_allowed_blocks( $allowed_blocks ) {
  // If all blocks are allowed no need to add anything
  if ( true === $allowed_blocks ) {
    return $allowed_blocks;
  }

  // After WP 6.1 you cannot add new list items without core/list-item block that was introduced
  if ( is_array( $allowed_blocks ) && in_array( 'core/list', $allowed_blocks, true ) ) {
    $allowed_blocks[] = 'core/list-item';
  }

  return $allowed_blocks;
} // end air_helper_gutenberg_allowed_blocks

/**
 * Disable gutenberg block assets so users can't intall plugins by enabling new blocks.
 *
 * @since 3.0.4
 */
add_action( 'init', 'air_helper_disable_gutenberg_block_directory_assets', 100 );
function air_helper_disable_gutenberg_block_directory_assets() {
  remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
} // end air_helper_disable_gutenberg_block_directory_assets

/**
 * Open the block patterns on a new, empty page.
 *
 * A blank canvas with the block inserter only shows single blocks, and
 * users rarely find the finished sections under the Patterns tab.
 * On a fresh page we open the inserter straight onto all patterns.
 *
 * Disable using `add_filter( 'air_helper_enable_page_patterns_onboarding', '__return_false' )`
 *
 * @since 3.3.0
 */
add_action( 'enqueue_block_editor_assets', 'air_helper_enqueue_page_patterns_onboarding' );
function air_helper_enqueue_page_patterns_onboarding() {
  if ( ! apply_filters( 'air_helper_enable_page_patterns_onboarding', true ) ) {
    return;
  }

  $screen = get_current_screen();
  if ( ! $screen || 'page' !== $screen->post_type ) {
    return;
  }

  // An empty Patterns tab teaches nothing, so stay out of the way on sites without any
  if ( ! air_helper_site_has_block_patterns() ) {
    return;
  }

  wp_enqueue_script( 'air-helper-page-patterns-onboarding', air_helper_base_url() . '/assets/js/page-patterns-onboarding.js', [ 'wp-data', 'wp-dom-ready', 'wp-editor', 'wp-i18n' ], air_helper_version(), true );
} // end air_helper_enqueue_page_patterns_onboarding

/**
 * Whether the site has any user patterns or registered patterns.
 *
 * @since 3.3.0
 * @return boolean
 */
function air_helper_site_has_block_patterns() {
  $user_patterns = wp_count_posts( 'wp_block' );

  if ( ! empty( $user_patterns->publish ) ) {
    return true;
  }

  return ! empty( WP_Block_Patterns_Registry::get_instance()->get_all_registered() );
} // end air_helper_site_has_block_patterns
