<?php
/**
 * Image lazyload helpers.
 *
 * @Author:             Timi Wahalahti, Digitoimisto Dude Oy (https://dude.fi)
 * @Date:               2019-08-07 14:38:34
 * @Last Modified by:   Roni Laukkarinen
 * @Last Modified time: 2020-05-12 16:17:30
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
      if ( $fallback ) {
        return get_image_lazyload_div_fallback( $fallback );
      }

      return;
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
<div aria-hidden="true" class="background-image preview lazyload" style="background-image: url('<?php echo esc_url( $image_urls['tiny'] ); ?>');" data-src="<?php echo esc_url( $image_urls['big'] ); ?>" data-src-mobile="<?php echo esc_url( $image_urls['mobile'] ); ?>"></div>

<?php // Div for full image, hack for browsers that don't support our js well ?>
<div aria-hidden="true" class="background-image full-image" <?php if ( $browser_hack ) : ?> style="background-image: url('<?php echo esc_url( $image_urls['big'] ); ?>');" <?php endif; ?>></div>

<?php // Div with full image for browsers without js ?>
<noscript>
  <div aria-hidden="true" class="background-image full-image" style="background-image: url('<?php echo esc_url( $image_urls['big'] ); ?>');"></div>
</noscript>

<?php

    return ob_get_clean();
  } // end get_image_lazyload_div
} // end if

if ( ! function_exists( 'get_image_lazyload_div_fallback' ) ) {
  function get_image_lazyload_div_fallback( $fallback = false ) {
    if ( empty( $fallback ) ) {
      return;
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
<div aria-hidden="true" class="background-image preview lazyload" style="background-image: url('<?php echo esc_url( $fallback ); ?>');" data-src="<?php echo esc_url( $fallback ); ?>" data-src-mobile="<?php echo esc_url( $fallback ); ?>"></div>

<?php // Div for full image, hack for browsers that don't support our js well ?>
<div aria-hidden="true" class="background-image full-image" <?php if ( $browser_hack ) : ?> style="background-image: url('<?php echo esc_url( $fallback ); ?>');" <?php endif; ?>></div>

<?php // Div with full image for browsers without js ?>
<noscript>
  <div aria-hidden="true" class="background-image full-image" style="background-image: url('<?php echo esc_url( $fallback ); ?>');"></div>
</noscript>

<?php

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
      if ( $fallback ) {
        return get_image_lazyload_tag_fallback( $fallback );
      }

      return;
    }

    // Get dimensions
    $dimensions = air_helper_get_image_lazyload_dimensions( $image_id, $sizes );

    if ( ! $dimensions ) {
      return;
    }

    // Get the img tag
    ob_start(); ?>
<img aria-hidden="true" class="lazyload" src="<?php echo esc_url( $image_urls['tiny'] ); ?>" data-src="<?php echo esc_url( $image_urls['big'] ); ?>" data-src-mobile="<?php echo esc_url( $image_urls['mobile'] ); ?>" width="<?php echo esc_attr( $dimensions['width'] ); ?>" height="<?php echo esc_attr( $dimensions['height'] ); ?>" alt="" />
<?php

    return ob_get_clean();
  } // end get_image_lazyload_tag
} // end if

if ( ! function_exists( 'get_image_lazyload_tag_fallback' ) ) {
  function get_image_lazyload_tag_fallback( $fallback = false ) {
    if ( empty( $fallback ) ) {
      return;
    }

    // Get the img tag
    ob_start(); ?>
<img aria-hidden="true" class="lazyload" src="<?php echo esc_url( $fallback ); ?>" data-src="<?php echo esc_url( $fallback ); ?>" data-src-mobile="<?php echo esc_url( $fallback ); ?>" alt="" />
<?php

    return ob_get_clean();
  } // end get_image_lazyload_tag_fallback
} // end if

/**
 * Get image urls for each size needed on lazyloading.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  array   $sizes    Custom sizes for lazyloading. Optional.
 * @return mixed             Boolean false if image or sizes do not exist, otherwise array size=>image url
 * @since  1.11.0
 */
function air_helper_get_image_lazyload_sizes( $image_id = 0, $sizes = [] ) {
  $image_id = intval( $image_id );

  if ( ! $image_id ) {
    return false;
  }

  // Bail if ID is not attachment
  if ( 'attachment' !== get_post_type( $image_id ) ) {
    return false;
  }

  // Default image sizes for use cases
  $default_sizes = [
    'tiny'    => 'tiny-lazyload-thumbnail',
    'mobile'  => 'large',
    'big'     => 'full',
  ];

  $sizes = wp_parse_args( $sizes, $default_sizes );
  $intermediate_sizes = get_intermediate_image_sizes();

  // Loop sizes to get corresponding image url
  foreach ( $sizes as $size_for => $size ) {
    // Check that asked image size exists and fallback to full size
    if ( ! in_array( $size, $intermediate_sizes ) ) {
      $size = 'full';
    }

    // Get image url
    $url = wp_get_attachment_image_url( $image_id, $size );

    // Thumbnail fallback
    if ( ! $url && 'tiny-lazyload-thumbnail' === $size ) {
      $url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
    }

    // For some reason, we don't have image so unset the size
    if ( ! $url ) {
      unset( $sizes[ $size_for ] );
    }

    // Replace the image size name with url to image
    $sizes[ $size_for ] = esc_url( $url );
  }

  // Check that all required default images exists
  if ( ! array_key_exists( 'tiny', $sizes ) ) {
    return false;
  }

  if ( ! array_key_exists( 'mobile', $sizes ) ) {
    return false;
  }

  if ( ! array_key_exists( 'big', $sizes ) ) {
    return false;
  }

  // Fallback to thumbnail if tiny is same as big
  if ( $sizes['tiny'] === $sizes['big'] ) {
    $url = wp_get_attachment_image_url( $image_id, 'thumbnail' );

    if ( $url ) {
      $sizes['tiny'] = esc_url( $url );
    }
  }

  return $sizes;
} // end function air_helper_get_image_lazyload_sizes

/**
 * Get dimensions of attachment image.
 *
 * @param  integer $image_id Image attachment id to lazyload.
 * @param  array   $sizes    Custom sizes for lazyloading. Optional.
 * @return mixed             Boolean false if image do not exist, otherwise array with width and height.
 * @since  1.11.0
 */
function air_helper_get_image_lazyload_dimensions( $image_id = 0, $sizes = [] ) {
  $image_id = intval( $image_id );

  if ( ! $image_id ) {
    return false;
  }

  // Bail if ID is not attachment
  if ( 'attachment' !== get_post_type( $image_id ) ) {
    return false;
  }

  // Default image sizes for use cases
  $default_sizes = [
    'tiny'    => 'tiny-lazyload-thumbnail',
    'mobile'  => 'large',
    'big'     => 'full',
  ];

  $sizes = wp_parse_args( $sizes, $default_sizes );

  // Get image data
  $dimensions = wp_get_attachment_image_src( $image_id, $sizes['big'] );

  if ( ! $dimensions ) {
    return false;
  }

  return [
    'width'   => $dimensions[1],
    'height'  => $dimensions[2],
  ];
} // end air_helper_get_image_lazyload_dimensions
