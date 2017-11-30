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
	 *  @param  integer $post_id post ID.
	 *  @return boolean          true / false whether the post exists.
	 */
	function post_exists_id( $post_id ) {
		return is_string( get_post_status( $post_id ) );
	}
}

if ( ! function_exists( 'has_content' ) ) {
	/**
	 *  Check if post has main content.
	 *
	 *  @since  1.4.2
	 *  @param  integer $post_id post ID.
	 *  @return boolean          true / false whether the post content exists.
	 */
	function has_content( $post_id = null ) {
		if ( isset( $post_id ) ) {
			$content = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
		} else {
			$content = get_the_content();
		}

		return ! empty( $content );
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

if ( ! function_exists( 'has_children' ) ) {
	/**
	 *  Check if post has child pages.
	 *
	 *  @since  1.4.2
	 *  @param  integer $post_id  post ID, defaults to current post.
	 *  @param  string  $post_type post type, defaults to page or current post type.
	 *  @return boolean            true / false whether post has childs.
	 */
	function has_children( $post_id = null, $post_type = 'page' ) {
		if ( null === $post_id ) {
			global $post;
			$post_id = $post->ID;
			$post_type = $post->post_type;
		}

		$query = new WP_Query( array(
			'post_parent'	=> $post_id,
			'post_type'		=> $post_type,
		) );

		return $query->have_posts();
	}
}
