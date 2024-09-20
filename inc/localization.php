<?php
/**
 *  Localization related hooks
 *
 *  @package air-helper
 */

/**
 *  Override default translation strings with ones set in admin
 */
add_filter( 'pre_air_helper_ask_string', 'air_helper_localization_strings_override', 10, 2 );
function air_helper_localization_strings_override( $string, $key ) { // phpcs:ignore
  $overrides = get_option( 'air_helper_localization_string_overrides', [] );
  $overrride_key = sanitize_title( $key );

  if ( isset( $overrides[ $overrride_key ] ) ) {
    $string = $overrides[ $overrride_key ];
  }

  return $string;
} // end air_helper_localization_strings_override
