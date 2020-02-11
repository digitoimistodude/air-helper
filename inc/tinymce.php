<?php
/**
 * TinyMCE (old) editor actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:35:34
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:59:08
 *
 * @package air-helper
 */

/**
 *  Show TinyMCE second editor tools row by default.
 *
 *  Turn off by using `remove_filter( 'tiny_mce_before_init', 'air_helper_show_second_editor_row' )`
 *
 *  @since  1.3.0
 *  @param  array $tinymce tinymce options.
 */
add_filter( 'tiny_mce_before_init', 'air_helper_show_second_editor_row' );
function air_helper_show_second_editor_row( $tinymce ) {
  $tinymce['wordpress_adv_hidden'] = false;
  return $tinymce;
} // end air_helper_show_second_editor_row

/**
 *  Remove some Tiny MCE formats from editor.
 *
 *  Turn off by using `remove_action( 'tiny_mce_before_init', 'air_helper_tinymce_remove_formats' )`
 *
 *  @since  1.7.0
 */
add_filter( 'tiny_mce_before_init', 'air_helper_tinymce_remove_formats' );
function air_helper_tinymce_remove_formats( $init ) {
  // Do not try to do this if we don't have function to check version where plugin was activated.
  if ( ! function_exists( 'air_helper_activated_at_version' ) ) {
    return $init;
  }

  // If plugin vas activated before version 1.7.0, do NOT do this.
  if ( air_helper_activated_at_version() < 170 ) {
    return $init;
  }

  $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
  return $init;
} // end air_helper_tinymce_remove_formats
