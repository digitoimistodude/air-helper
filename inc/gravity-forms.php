<?php
/**
 * Gravity forms related actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:28:59
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:57:47
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
