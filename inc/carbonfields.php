<?php
/**
 * Carbon Fields
 *
 * CF related functions and settings.
 *
 * @Author: Timi Wahalahti
 * @Date:   2017-09-21 10:31:31
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2018-01-29 12:16:36
 *
 * @package airhelper
 */

/**
 *  Register and enqueue custom styles for CRB.
 *
 *  @since 1.5.0
 */
function air_helper_cf_styles() {
	wp_register_style( 'air-helper-cf', air_helper_base_url() . '/assets/css/cf.css', false, filemtime( air_helper_base_path() . '/assets/css/cf.css' ) );
	wp_enqueue_style( 'air-helper-cf' );
}
add_action( 'admin_enqueue_scripts', 'air_helper_cf_styles' );

/**
 * Short-circuit (override) default get_post_metadata functionality with
 * our own to implement post meta revisions support when get_post_metadata
 * function is used.
 *
 * @param check    $check Check param.
 * @param post_id  $post_id Post ID param.
 * @param meta_key $meta_key Meta key param.
 * @param single   $single Single param.
 * @since  1.1.0
 */
function air_helper_short_circuit_get_post_metadata( $check, $post_id, $meta_key, $single ) {
	/**
	 *  Bail out if we are in dashboard,
	 *  because in there we always wan't the fresh meta data.
	 */
	if ( is_admin() ) {
		return $check;
	}

	/**
	 *  Check if this spesific meta field is set to support revisions,
	 *  and return to basic wp core functionality when not.
	 */
	$meta_keys = WP_Post_Meta_Revisioning::_wp_post_revision_meta_keys( false );
	if ( ! array_key_exists( $meta_key, $meta_keys ) ) {
		return $check;
	}

	// Get post id for revision if we are viewing one.
	if ( isset( $_GET['preview_id'] ) ) {
		$autosave = wp_get_post_autosave( $_GET['preview_id'] );

		if ( $autosave && $autosave->post_parent === $post_id ) {
			$post_id = (int) $autosave->ID;
		}
	}

	// Remove this filter to prevent endless loop.
	remove_filter( 'get_post_metadata', 'air_helper_short_circuit_get_post_metadata', 10 );

	// Get post meta value for revision.
	$check = get_post_meta( $post_id, $meta_key );

	/**
	 *  Alter the meta value of complex field to be in same
	 *  way as CF would return it.
	 */
	if ( 'complex' === $meta_keys[ $meta_key ] ) {
		$iterator = new RecursiveArrayIterator( $check[0] );
		$complex_return = iterator_to_array( $iterator, true );
		$check = air_helper_cf_hande_field_complex( $complex_return );
	}

	// Put this filter back to it's place for next met acall.
	add_filter( 'get_post_metadata', 'air_helper_short_circuit_get_post_metadata', 10, 4 );

	// Return the value.
	return $check;
}
add_filter( 'get_post_metadata', 'air_helper_short_circuit_get_post_metadata', 10, 4 );

/**
 * Structure the complex field as CF would.
 *
 * @param array $array Array.
 * @since  1.1.0
 */
function air_helper_cf_hande_field_complex( $array ) {
	$return = array();

	foreach ( $array as $key => $value ) {
		$tmp = air_helper_cf_hande_field_complex_clean_keys( $key, $value );
		$return[ $tmp[0] ] = $tmp[1];
	}

	return $return;
}

/**
 * Clean array keys and remove that nasy and un-neccesary value
 * field. Use only as a part of structuring CF complex field.
 *
 * @param key 	$key Key param.
 * @param value $value Value param.
 * @since  1.1.0
 */
function air_helper_cf_hande_field_complex_clean_keys( $key, $value ) {
	if ( is_array( $value ) ) {
		$c = array();

		foreach ( $value as $nested_key => $nested_value ) {
			if ( 'value' === $nested_key ) {
				continue;
			}

			$tmp = air_helper_cf_hande_field_complex_clean_keys( $nested_key, $nested_value );
			$c[ $tmp[0] ] = $tmp[1];
		}

		return array( ltrim( $key, '_' ), $c );
	} else {
		return array( ltrim( $key, '_' ), $value );
	}
}

/**
 * Show small message below preview button in admin.
 *
 * @since  1.1.0
 */
function air_helper_post_preview_message() {
	printf( '<br /><p style="color:#ddd;">%s</p>', __( 'Two preview times might be needed to see all content correctly.', 'air-helper' ) );
}
add_action( 'post_submitbox_minor_actions', 'air_helper_post_preview_message' );

/**
 * Nasty hack to make meta data preview work correctly,
 * don't tell mama.
 *
 * @param fields $fields Fields param.
 * @since 1.1.0
 */
function air_helper_add_field_debug_preview( $fields ) {
	$fields['debug_preview'] = 'debug_preview';
	return $fields;
}

/**
 *  Nast hack to make meta data preview work correctly,
 *  don't tell mama.
 *
 *  @since 1.1.0
 */
function air_helper_add_input_debug_preview() {
	echo '<input type="hidden" name="debug_preview" value="debug_preview">';
}
add_filter( '_wp_post_revision_fields', 'air_helper_add_field_debug_preview' );
add_action( 'edit_form_after_title', 'air_helper_add_input_debug_preview' );
