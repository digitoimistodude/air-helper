<?php
/**
 * Customizer actions.
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
