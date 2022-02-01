<?php
/**
 * Image native lazyload.
 *
 * @Author:		Elias Kautto
 * @Date:   		2022-01-28 12:33:30
 * @Last Modified by:   Elias Kautto
 * @Last Modified time: 2022-02-01 10:24:12
 *
 * @package air-helper
 */

/**
 * Echo image in lazyloading tag for native-lazyload.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  string  $args['fallback'] Url for fallback image. Defaults to theme settings default featured image.
 * @param  array   $args['sizes']    Custom sizes for lazyloading. Optional.
 * @param  string  $args['class']    Class name to give for img tag. Optional.
 * @since  2.3.1
 */
if ( ! function_exists( 'native_lazyload_tag' ) ) {
  function native_lazyload_tag( $image_id = 0, $args = [] ) {
    echo get_native_lazyload_tag( $image_id, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  } // end native_lazyload_tag
} // end if

/**
 * Get image lazyloading tag for native-lazyload.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  string  $args['fallback'] Url for fallback image. Defaults to theme settings default featured image.
 * @param  array   $args['sizes']    Custom sizes for lazyloading. Optional.
 * @param  string  $args['class']    Class name to give for img tag. Optional.
 * @return string            String containing lazyloading tag.
 * @since  2.3.1
 */
if ( ! function_exists( 'get_native_lazyload_tag' ) ) {
  function get_native_lazyload_tag( $image_id = 0, $args = [] ) {
    $args = wp_parse_args( $args, [
      'fallback' => false,
      'sizes' => [],
      'class' => null,
    ] );

  // Get image
    $image_urls = air_helper_get_image_lazyload_sizes( $image_id, $args['sizes'] );

    // Check if we have image
    if ( ! $image_urls || ! is_array( $image_urls ) ) {
      return get_native_lazyload_tag_fallback( $args['fallback'] );
    }

    // Possibility to add optional styles for the image
    $styles = '';
    $styles_array = apply_filters( 'air_helper_lazyload_tag_styles', [], $image_id );
    $styles_array = apply_filters( 'air_helper_native_lazyload_tag_styles', $styles_array, $image_id );
    foreach ( $styles_array as $key => $value ) {
      $styles .= ' ' . $key . ': ' . $value . ';';
    }

    // Get alt
    $alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

    // Get the img tag
    ob_start(); ?>
    <img loading="lazy"
      alt="<?php echo esc_attr( $alt ); ?>"
      src="<?php echo esc_url( $image_urls['big'] ); ?>"
      <?php if ( ! empty( $styles ) ) : ?>
        style="<?php echo esc_attr( $styles ); ?>"
      <?php endif; ?>
      <?php if ( ! empty( $args['class'] ) ) : ?>
        class="<?php echo esc_attr( $args['class'] ); ?>"
      <?php endif; ?>
    />
    <?php

    return ob_get_clean();
  } // end get_native_lazyload_tag
} // end if

/**
 * Fallback for lazyload tag.
 *
 * @param string $args['fallback'] Url for fallback image. Defaults to theme settings default featured image.
 * @param  string  $args['class']    Class name to give for img tag. Optional.
 * @return string            String containing lazyloading tag.
 * @since 2.4.0
 */
if ( ! function_exists( 'get_native_lazyload_tag_fallback' ) ) {
  function get_native_lazyload_tag_fallback( $args ) {
    // Default to theme default featured image if no fallback specified
    if ( empty( $args['fallback'] ) ) {
      if ( apply_filters( 'air_helper_image_lazyload_fallback_from_theme_settings', true ) && defined( 'THEME_SETTINGS' ) ) {
        $args['fallback'] = THEME_SETTINGS['default_featured_image'];
      }
    }

    // No fallback, bail
    if ( empty( $args['fallback'] ) ) {
      return;
    }

    // Get the img tag
    ob_start(); ?>
    <img loading="lazy"
      alt=""
      src="<?php echo esc_url( $args['fallback'] ); ?>"
      <?php if ( ! empty( $args['class'] ) ) : ?>
        class="<?php echo esc_attr( $args['class'] ); ?>"
      <?php endif; ?>
    />
    <?php

    return ob_get_clean();
  } // end get_native_lazyload_tag_fallback
} // end if
