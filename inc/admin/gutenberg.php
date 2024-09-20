<?php
/**
 * Gutenberg editor related hooks.
 *
 * Typically all changes should be done in the theme,
 * but sometimes we need to distribute global fixes.
 * In those situations, this file comes handy.
 *
 * @Author: Timi Wahalahti
 * @Date:   2022-11-09 15:03:40
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2022-11-09 15:09:25
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
  if ( is_array( $allowed_blocks ) && in_array( 'core/list', $allowed_blocks ) ) {
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
