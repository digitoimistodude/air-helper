<?php
/**
 *  Localization related functions.
 *
 *  Big thanks to Aucor Oy and escpecially Teemy Suoranta for the
 *  original (GPL-2.0 licensed) code, which this file is based on.
 *  https://github.com/aucor/aucor-starter/blob/master/inc/localization.php
 *
 *  @package air-helper
 */

/**
 * Get localized string by key.
 *
 * @since  1.4.0
 * @param  string $key  Unique identifier of string.
 * @param  string $lang 2 character language code (defaults to current language).
 * @return string       Translated value or key if not registered string.
 */
function ask__( $key, $lang = null ) {
  $strings = apply_filters( 'air_helper_pll_register_strings', [] );

  if ( isset( $strings[ $key ] ) ) {
    if ( null === $lang ) {
      return pll__( $strings[ $key ] );
    } else {
      return pll_translate_string( $strings[ $key ], $lang );
    }
  }

  // Debug missing strings.
  if ( WP_DEBUG === true ) {
    // init warning to get source.
    $e = new Exception( 'Localization error - Missing string by key {' . $key . '}' );

    // find file and line for problem.
    $trace_line = '';

    foreach ( $e->getTrace() as $trace ) {
      if ( in_array( $trace['function'], [ 'ask__', 'ask_e' ], true ) ) {
        $trace_line = ' in ' . $trace['file'] . ':' . $trace['line'];
      }
    }

    // compose error message.
    $error_msg = $e->getMessage() . $trace_line;

    // trigger errors.
    trigger_error( esc_html( $error_msg ), E_USER_WARNING );
    error_log( $error_msg );
  }

  return $key;
}
/**
 * Echo localized string by key.
 *
 * @since 1.4.0
 * @param string $key   Unique identifier of string.
 * @param string $lang  2 character language code (defaults to current language).
 */
function ask_e( $key, $lang = null ) {
  echo ask__( $key, $lang ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Get localized string by value.
 *
 * @since 1.4.0
 * @param string $value   String.
 * @param string $lang    2 character language code (defaults to current language).
 * @return string         Translated value or key if not registered string.
 */
function asv__( $value, $lang = null ) {
  // debug missing strings.
  if ( WP_DEBUG === true ) {
    $strings = apply_filters( 'air_helper_pll_register_strings', [] );

    if ( array_search( $value, $strings ) === false ) {
      // init warning to get source.
      $e = new Exception( 'Localization error - Missing string by key {' . $key . '}' );

      // find file and line for problem.
      $trace_line = '';

      foreach ( $e->getTrace() as $trace ) {
        if ( in_array( $trace['function'], [ 'asv__', 'asv_e' ], true ) ) {
          $trace_line = ' in ' . $trace['file'] . ':' . $trace['line'];
        }
      }

      // compose error message.
      $error_msg = $e->getMessage() . $trace_line;

      // trigger errors.
      trigger_error( esc_html( $error_msg ), E_USER_WARNING );
      error_log( $error_msg );
    }
  }

  if ( null === $lang ) {
    return pll__( $value );
  } else {
    return pll_translate_string( $value, $lang );
  }
}

/**
 * Echo localized string by value.
 *
 * @since 1.4.0
 * @param string $value   String.
 * @param string $lang    2 character language code (defaults to current language).
 */
function asv_e( $value, $lang = null ) {
  echo asv__( $value, $lang ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 *  Fallbacks for Polylang.
 *
 *  @since 1.4.0
 */
if ( ! function_exists( 'pll__' ) ) {
  function pll__( $string ) {
    return $string;
  }

  function pll_e( $string ) {
    echo $string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  }

  function pll_current_language() {
    return 'fi';
  }

  function pll_default_language( $value = 'slug' ) {
    return 'fi';
  }

  function pll_get_post_language( $id ) {
    return 'fi';
  }

  function pll_get_post( $post_id, $slug = '' ) {
    return $post_id;
  }

  function pll_get_term( $term_id, $slug = '' ) {
    return $term_id;
  }

  function pll_translate_string( $str, $lang = '' ) {
    return $str;
  }

  function pll_home_url( $slug = '' ) {
    return get_home_url();
  }
} // end if
