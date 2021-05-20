<?php
/**
 * Explanation.
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-05-20 17:54:57
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-05-20 18:27:59
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
