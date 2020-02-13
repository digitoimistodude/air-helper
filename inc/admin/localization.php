<?php
/**
 *  Register string for Polylang for localization.
 *
 *  Big thanks to Aucor Oy and escpecially Teemy Suoranta for the
 *  original (GPL-2.o licensed) code, which this file is based on.
 *  https://github.com/aucor/aucor-starter/blob/master/inc/localization.php
 *
 *  @package air-helper
 */

/**
 *  Register Polylang translatable strings.
 *
 *  @since 1.4.0
 */
if ( function_exists( 'pll_register_string' ) ) {
	$strings = apply_filters( 'air_helper_pll_register_strings', [] );

	if ( is_array( $strings ) ) {
		foreach ( $strings as $key => $value ) {
			pll_register_string( $key, $value, apply_filters( 'air_helper_pll_register_string_group', 'Theme' ) );
		}
	}
}
