<?php
/**
 *  Generally used helper functions.
 *
 *  @package air-helper
 */

if ( ! function_exists( 'post_exists_id' ) ) {
	/**
	 *  Check if post exists by ID.
	 *
	 *  @since  1.4.0
	 *  @param  integer $id post ID.
	 *  @return boolean     true / false weather the post exists.
	 */
	function post_exists_id( $id ) {
		return is_string( get_post_status( $id ) );
	}
}

if ( ! function_exists( 'get_icons_for_user' ) ) {
	/**
	 *  Get list of icons which are available for user.
	 *
	 *  @since  1.4.0
	 *  @return array  array of icons
	 */
	function get_icons_for_user() {
		$icons = array();
		$files = glob( get_template_directory() . '/svg/foruser/*.svg' );

		foreach ( $files as $file ) {
			$filename = explode( '/', $file );
			$filename = end( $filename );
			$icons[ $filename ] = ucfirst( strstr( $filename, '.', true ) );
		}

		return $icons;
	}
}
