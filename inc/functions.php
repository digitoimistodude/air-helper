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
	 *  @since  2.2.0
	 *  @param  integer $id post ID.
	 *  @return boolean     true / false weather the post exists.
	 */
	function post_exists_id( $id ) {
		return is_string( get_post_status( $id ) );
	}
}
