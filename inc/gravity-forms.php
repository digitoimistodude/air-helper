<?php
/**
 * Gravity forms related actions.
 *
 * @package air-helper
 */

/**
 * Allow Gravity Forms to hide labels to add placeholders.
 *
 * Turn off by using `add_filter( 'gform_enable_field_label_visibility_settings', '__return_false' )`
 *
 * @since  0.1.0
 */
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

/**
 * Force anti-spam honeypot on all forms.
 *
 * @since 2.17.0
 */
add_filter( 'gform_form_post_get_meta', 'air_helper_gravity_forms_force_honeypot' );
function air_helper_gravity_forms_force_honeypot( $form ) {
  $form['enableHoneypot'] = true;
  return $form;
} // end air_helper_gravity_forms_force_honeypot
