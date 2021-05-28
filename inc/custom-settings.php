<?php
/**
 * Custom settings related
 *
 * @Author: Niku Hietanen
 * @Date: 2021-05-28 14:16:00
 * @Last Modified by: Niku Hietanen
 * @Last Modified time: 2021-05-28 14:34:02
 *
 * @package air-helper
 */

/**
 * Add post type support for editor depending on whether
 * to use block editor in current setting group
 *
 * @since  2.9.0
 */
add_action( 'admin_init', 'air_helper_editor_support_for_setting_group_post', 99, 1 );
function air_helper_editor_support_for_setting_group_post() {

  // Try find out which post id we are loading in admin
  $post_id = false;
  if ( isset( $_GET['post'] ) ) { // phpcs:ignore
    $post_id = intval( $_GET['post'] ); // phpcs:ignore
  } elseif ( isset( $_POST['post_ID'] ) ) { // phpcs:ignore
    $post_id = intval( $_POST['post_ID'] ); // phpcs:ignore
  }

  if ( ! $post_id ) {
    return;
  }

  if ( use_block_editor_in_custom_setting_group( $post_id ) ) {
    // Post type support 'editor' is needed for block editor
    add_post_type_support( 'settings', 'editor' );
  } else {
    remove_post_type_support( 'settings', 'editor' );
  }
} // end air_helper_set_editor_type_for_setting_group_post

/**
 * Check whether to use classic or block editor
 * for a certain post type as defined in the settings
 */
add_filter( 'use_block_editor_for_post', __NAMESPACE__ . '\air_helper_use_block_editor_in_custom_setting_group', 10, 2 );
function air_helper_use_block_editor_in_custom_setting_group( $use_block_editor, $post ) {
  $settings_post_types = apply_filters( 'air_helper_custom_settings_post_types', [ 'settings' ] );

  // Use block editor if settings page is a block editor type
  if ( in_array( $post->post_type, $settings_post_types, true ) ) {
    return use_block_editor_in_custom_setting_group( $post->ID );
  }
  return $use_block_editor;
} // end air_helper_use_block_editor_in_custom_setting_group