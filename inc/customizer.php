<?php
/**
 * Customizer actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:26:35
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:57:34
 *
 * @package air-helper
 */

/**
 * Remove the additional CSS section from the Customizer.
 *
 * Add back by using `remove_action( 'customize_register', 'air_helper_customizer_remove_css_section' )`
 *
 * @param object $wp_customize WP_Customize_Manager.
 * @since  1.3.0
 */
add_action( 'customize_register', 'air_helper_customizer_remove_css_section', 15 );
function air_helper_customizer_remove_css_section( $wp_customize ) {
  $wp_customize->remove_section( 'custom_css' );
} // end air_helper_customizer_remove_css_section
