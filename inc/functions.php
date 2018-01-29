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

if ( ! function_exists( 'get_posts_array' ) ) {
	/**
	 *  Get posts in key=>value array.
	 *
	 *  @since  1.4.2
	 *  @param  array  $args 				Arguments passed to get_posts function.
	 *  @param  string $return_key 	Which field to use as a key in return.
	 *  @return array               {$return_key}=>post_title array of posts.
	 */
	function get_posts_array( $args = array(), $return_key = 'ID' ) {
		$return = array();
		$posts = get_posts( $args );

		foreach ( $posts as $post ) {
			$return[ $post->{ $return_key } ] = $post->post_title;
		}

		return $return;
	}
}

if ( ! function_exists( 'dude_get_post_meta' ) ) {
	/**
	 *  Our own function mainly for getting CF complex fields.
	 *
	 *  @since  1.1.0
	 *  @param int    $post_id Post ID.
	 *  @param string $key     Optional. The meta key to retrieve. By default, returns
	 *                         data for all keys. Default empty.
	 *  @param bool   $single  Optional. Whether to return a single value. Default false.
	 *  @return mixed 					Will be an array if $single is false. Will be value of meta data
	 *                          field if $single is true.
	 */
	function dude_get_post_meta( $post_id, $key, $single ) {
		if ( isset( $_GET['preview_id'] ) ) {
			return get_post_meta( $post_id, '_' . $key, $single );
		} elseif ( function_exists( 'carbon_get_post_meta' ) ) {
			return carbon_get_post_meta( $post_id, $key );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'dude_get_crb_pll_id' ) ) {
	/**
	 *  Make CF conditional display condition with post_id to work with Polylang.
	 *
	 *  @since  1.4.3
	 *  @param  mixed   $condition Condition type name as a string from CF.
	 *  @param  integer $post_id   Post ID to check against.
	 *  @param  string  $operator  Which operator to use in conditional check.
	 *  @return mixed 						 CF condition result.
	 */
	function dude_get_crb_pll_id( $condition, $post_id = 0, $operator = '=' ) {
		if ( function_exists( 'pll_e' ) ) {
			// If Polylang is active, get translations for post.
			global $polylang;
			$translations = $polylang->model->post->get_translations( $post_id );

			// Modify operator to work with array.
			if ( '=' === $operator ) {
				$operator = 'IN';
			} else {
				$operator = 'NOT IN';
			}

			// CF condition check.
			$condition->where( 'post_id', $operator, $translations );
		} else {
			// Polylang is not active, use default condition check.
			$condition->where( 'post_id', $operator, $post_id );
		}

		// Return condition result.
		return $condition;
	}
}
