<?php
/**
 * Explanation.
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-05-20 17:54:57
 * @Last Modified by: Niku Hietanen
 * @Last Modified time: 2021-05-28 14:34:18
 *
 * @package air-helper
 */

if ( ! function_exists( 'get_custom_setting' ) ) {
  /**
   *  Get singular setting field from defined setting group.
   *  Setting groups are posts in settings CPT and post for
   *  each group is assigned via filter.
   *
   *  @since  2.9.0
   *  @param  string $key   setting to get.
   *  @param  string $group in which group the setting is.
   *  @return mixed         boolean false if setting group or setting is not found, otherwise it's value.
   */
  function get_custom_setting( $key, $group ) {
    $post_id = get_custom_settings_post_id( $group );
    if ( empty( $post_id ) ) {
      return false;
    }

    $value = get_field( $key, $post_id );
    if ( empty( $value ) ) {
      $value = get_field( "{$group}_{$key}", $post_id );
    }

    return $value;
  } // end get_custom_setting
} // end if

if ( ! function_exists( 'get_custom_settings_post_id' ) ) {
  /**
   * Get the custom settings group post id.
   *
   * Post id's are usually defined in the theme and air-light handles this automatically.
   *
   * @since  2.9.0
   * @param  string $group group key.
   * @return mixed         boolean false if settings post do not exist, otherwise integer post id.
   */
  function get_custom_settings_post_id( $group ) {
    $group_post_ids = apply_filters( 'air_helper_custom_settings_post_ids', [] );

    if ( ! isset( $group_post_ids[ $group ] ) ) {
      return false;
    }

    $post_id = pll_get_post( $group_post_ids[ $group ] ); // plugin backs us up if Polylang is not installed, no function check needed

    if ( empty( $post_id ) ) {
      // Maybe fallback to settings on main language if post is not translated
      $polylang_fallback_to_main = apply_filters( 'air_helper_custom_settings_polylang_fallback_main', false, $group, $group_post_ids );
      if ( ! $polylang_fallback_to_main ) {
        return false;
      }

      $post_id = $group_post_ids[ $group ];
    }

    return $post_id;
  } // end get_custom_settings_post_id
} // end if

if ( ! function_exists( 'use_block_editor_in_custom_setting_group' ) ) {

  /**
   * Check if to use block editor in setting group post.
   */
  function use_block_editor_in_custom_setting_group( $post_id ) {
    $setting_group_post_ids = apply_filters( 'air_helper_custom_settings_post_ids', [] );

    if ( ! in_array( $post_id, $setting_group_post_ids, true ) ) {
      return false;
    }

    $block_editor_prefix = apply_filters( 'air_helper_custom_settings_block_editor_prefix', 'block-editor/' );

    $setting_group_post_ids_with_block_editor = array_filter(
      $setting_group_post_ids,
      function( $key ) use ( $block_editor_prefix ) {
        return false !== strpos( $key, $block_editor_prefix );
      },
      ARRAY_FILTER_USE_KEY
    );

    if ( in_array( $post_id, $setting_group_post_ids_with_block_editor, true ) ) {
      return true;
    }

    return false;
  }
}