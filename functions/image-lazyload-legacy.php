<?php
/**
 * Image lazyload helpers.
 *
 * @package air-helper
 */

/**
 * Echo image in lazyloading divs.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  array   $sizes    Custom sizes for lazyloading. Optional.
 * @since  1.11.0
 */
if ( ! function_exists( 'image_lazyload_div' ) ) {
  function image_lazyload_div( $image_id = 0, $sizes = [], $fallback = false ) {
    echo get_image_lazyload_div( $image_id, $sizes, $fallback ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  } // end image_lazyload_div
} // end if

/**
 * Get image lazyloading divs.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  array   $sizes    Custom sizes for lazyloading. Optional.
 * @return string            String containing lazyloading divs.
 * @since  1.11.0
 */
if ( ! function_exists( 'get_image_lazyload_div' ) ) {
  function get_image_lazyload_div( $image_id = 0, $sizes = [], $fallback = false ) {
    // Get image
    $image_urls = air_helper_get_image_lazyload_sizes( $image_id, $sizes );

    // Check if we have image
    if ( ! $image_urls || ! is_array( $image_urls ) ) {
      return get_image_lazyload_div_fallback( $fallback );
    }

    // Possibility to add optional styles for the image
    $styles = '';
    $styles_array = apply_filters( 'air_image_lazyload_div_styles', [], $image_id ); // backwards comp
    $styles_array = apply_filters( 'air_helper_image_lazyload_div_styles', $styles_array, $image_id );
    foreach ( $styles_array as $key => $value ) {
      $styles .= ' ' . $key . ': ' . $value . ';';
    }

    // Do preg match and check if we need to do browser hack
    $browser_hack = false;
    if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
      if ( preg_match( '/Windows Phone|Lumia|iPad|Safari/i', sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) ) {
        $browser_hack = true;
      }
    }

    ob_start();

    // Div for preview image and data for js to use ?>
    <div aria-hidden="true"
      class="background-image preview lazyload"
      style="background-image: url('<?php echo esc_url( $image_urls['tiny'] ); ?>'); <?php echo esc_attr( $styles ); ?>"
      data-src="<?php echo esc_url( $image_urls['big'] ); ?>"
      data-src-mobile="<?php echo esc_url( $image_urls['mobile'] ); ?>">
    </div>

    <?php // Div for full image, hack for browsers that don't support our js well ?>
    <div aria-hidden="true"
      class="background-image full-image"
      <?php if ( $browser_hack ) : ?>
        style="background-image: url('<?php echo esc_url( $image_urls['big'] ); ?>'); <?php echo esc_attr( $styles ); ?>"
      <?php endif; ?>>
    </div>

    <?php if ( apply_filters( 'air_helper_image_lazyload_enable_noscript_fallback', false ) ) : ?>
      <noscript>
        <div aria-hidden="true"
          class="background-image full-image"
          style="background-image: url('<?php echo esc_url( $image_urls['big'] ); ?>'); <?php echo esc_attr( $styles ); ?>">
        </div>
      </noscript>
    <?php endif;

    return ob_get_clean();
  } // end get_image_lazyload_div
} // end if

if ( ! function_exists( 'get_image_lazyload_div_fallback' ) ) {
  function get_image_lazyload_div_fallback( $fallback = false ) {
    if ( empty( $fallback ) ) {
      if ( apply_filters( 'air_helper_image_lazyload_fallback_from_theme_settings', true ) && defined( 'THEME_SETTINGS' ) ) {
        $fallback = THEME_SETTINGS['default_featured_image'];
      } else {
        return;
      }
    }

    // Do preg match and check if we need to do browser hack
    $browser_hack = false;
    if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
      if ( preg_match( '/Windows Phone|Lumia|iPad|Safari/i', sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) ) {
        $browser_hack = true;
      }
    }

    ob_start();

    // Div for preview image and data for js to use ?>
    <div aria-hidden="true"
      class="background-image preview lazyload"
      style="background-image: url('<?php echo esc_url( $fallback ); ?>');"
      data-src="<?php echo esc_url( $fallback ); ?>"
      data-src-mobile="<?php echo esc_url( $fallback ); ?>">
    </div>

    <?php // Div for full image, hack for browsers that don't support our js well ?>
    <div aria-hidden="true"
      class="background-image full-image"
      <?php if ( $browser_hack ) : ?>
        style="background-image: url('<?php echo esc_url( $fallback ); ?>');"
      <?php endif; ?>>
    </div>

    <?php if ( apply_filters( 'air_helper_image_lazyload_enable_noscript_fallback', false ) ) : ?>
      <noscript>
        <div aria-hidden="true"
          class="background-image full-image"
          style="background-image: url('<?php echo esc_url( $fallback ); ?>');">
        </div>
      </noscript>
    <?php endif;

    return ob_get_clean();
  } // end get_image_lazyload_div_fallback
} // end if

/**
 * Echo image in lazyloading tag.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  array   $sizes    Custom sizes for lazyloading. Optional.
 * @since  1.11.0
 */
if ( ! function_exists( 'image_lazyload_tag' ) ) {
  function image_lazyload_tag( $image_id = 0, $sizes = [], $fallback = false ) {
    echo get_image_lazyload_tag( $image_id, $sizes, $fallback ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  } // end image_lazyload_tag
} // end if

/**
 * Get image lazyloading tag.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  array   $sizes    Custom sizes for lazyloading. Optional.
 * @return string            String containing lazyloading divs.
 * @since  1.11.0
 */
if ( ! function_exists( 'get_image_lazyload_tag' ) ) {
  function get_image_lazyload_tag( $image_id = 0, $sizes = [], $fallback = false ) {
    // Get image
    $image_urls = air_helper_get_image_lazyload_sizes( $image_id, $sizes );

    // Check if we have image
    if ( ! $image_urls || ! is_array( $image_urls ) ) {
      return get_image_lazyload_tag_fallback( $fallback );
    }

    // Get dimensions
    $dimensions = air_helper_get_image_lazyload_dimensions( $image_id, $sizes );

    if ( ! $dimensions ) {
      return;
    }

    // Possibility to add optional styles for the image
    $styles = '';
    $styles_array = apply_filters( 'air_image_lazyload_div_styles', [], $image_id ); // backwards comp
    $styles_array = apply_filters( 'air_helper_image_lazyload_div_styles', $styles_array, $image_id );
    foreach ( $styles_array as $key => $value ) {
      $styles .= ' ' . $key . ': ' . $value . ';';
    }

    // Get alt
    $alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

    // Get the img tag
    ob_start(); ?>
    <img class="lazyload"
      src="<?php echo esc_url( $image_urls['tiny'] ); ?>"
      data-src="<?php echo esc_url( $image_urls['big'] ); ?>"
      data-src-mobile="<?php echo esc_url( $image_urls['mobile'] ); ?>"
      width="<?php echo esc_attr( $dimensions['width'] ); ?>"
      height="<?php echo esc_attr( $dimensions['height'] ); ?>"
      alt="<?php echo esc_attr( $alt ); ?>"
      style="<?php echo esc_attr( $styles ); ?>"
    />
    <?php

    return ob_get_clean();
  } // end get_image_lazyload_tag
} // end if

if ( ! function_exists( 'get_image_lazyload_tag_fallback' ) ) {
  function get_image_lazyload_tag_fallback( $fallback = false ) {
    if ( empty( $fallback ) ) {
      if ( apply_filters( 'air_helper_image_lazyload_fallback_from_theme_settings', true ) && defined( 'THEME_SETTINGS' ) ) {
        $fallback = THEME_SETTINGS['default_featured_image'];
      } else {
        return;
      }
    }

    // Get the img tag
    ob_start(); ?>
    <img
      loading="lazy"
      class="lazyload"
      src="<?php echo esc_url( $fallback ); ?>"
      data-src="<?php echo esc_url( $fallback ); ?>"
      data-src-mobile="<?php echo esc_url( $fallback ); ?>"
      alt=""
    />
    <?php

    return ob_get_clean();
  } // end get_image_lazyload_tag_fallback
} // end if
