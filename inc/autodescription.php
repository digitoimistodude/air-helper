<?php
/**
 * Modify The SEO Framework (autodescription) behaviour.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-02-11 16:49:04
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 17:02:58
 *
 * @package air-helper
 */

/**
 * Modify The SEO Framework (autodescription) default settings.
 *
 * Disable with `remove_filter( 'the_seo_framework_default_site_options', 'air_helper_the_seo_framework_default_site_options' )`
 *
 * @since  5.0.0
 */
add_filter( 'the_seo_framework_default_site_options', 'air_helper_the_seo_framework_default_site_options' );
function air_helper_the_seo_framework_default_site_options( $options = [] ) {
  $options['display_seo_bar_tables'] = false;
  $options['display_pixel_counter'] = false;
  $options['display_character_counter'] = false;
  $options['title_rem_prefixes'] = true;

  return $options;
} // end air_helper_the_seo_framework_default_site_options
