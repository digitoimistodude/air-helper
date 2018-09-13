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
		if ( empty( $post_id ) ) {
			return false;
		}

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
	 *  @param  array  $args       Arguments passed to get_posts function.
	 *  @param  string $return_key Which field to use as a key in return.
	 *  @return array              {$return_key}=>post_title array of posts.
	 */
	function get_posts_array( $args = array(), $return_key = 'ID' ) {
		$return = array();
		$defaults = array(
			'posts_per_page'	=> 100,
			'orderby'					=> 'title',
			'order'						=> 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );
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

if ( ! function_exists( 'get_next_page_id' ) ) {
	/**
	 *  Get ID of next page.
	 *
	 *  @since  1.5.1
	 *  @param  integer $id page which next to get.
	 *  @return mixed       false if no next page, id if there is.
	 */
	function get_next_page_id( $id = 0 ) {
		$next_page_id = false;

		// Get all pages under this section
		$post = get_post( $id );
		$get_pages = get_pages( array(
			'post_type'		=> $post->post_type,
			'child_of'		=> $post->post_parent,
			'parent'			=> $post->post_parent,
			'sort_column'	=> 'menu_order',
			'sort_order'	=> 'asc',
		) );

		// Count pages.
		$page_count = count( $get_pages );

		for ( $p = 0; $p < $page_count; $p++ ) {
			// Get the array key for our entry.
			if ( isset( $get_pages[ $p ] ) && $id === $get_pages[ $p ]->ID ) {
				break;
			}
		}

		// Assign our next key.
		$next_key = $p + 1;

		// If there isn't a value assigned for the previous key, go all the way to the end.
		if ( isset( $get_pages[ $next_key ] ) ) {
			$next_page_id = $get_pages[ $next_key ]->ID;
		}

		return $next_page_id;
	}
} // End if().

if ( ! function_exists( 'get_prev_page_id' ) ) {
	/**
	 *  Get ID of previous page.
	 *
	 *  @since  1.5.1
	 *  @param  integer $id page which previous to get.
	 *  @return mixed       false if no previous page, id if there is.
	 */
	function get_prev_page_id( $id = 0 ) {
		$prev_page_id = false;

		// Get all pages under this section
		$post = get_post( $id );
		$get_pages = get_pages( array(
			'post_type'		=> $post->post_type,
			'child_of'		=> $post->post_parent,
			'parent'			=> $post->post_parent,
			'sort_column'	=> 'menu_order',
			'sort_order'	=> 'asc',
		) );

		// Count pages.
		$page_count = count( $get_pages );

		for ( $p = 0; $p < $page_count; $p++ ) {
			// Get the array key for our entry.
			if ( isset( $get_pages[ $p ] ) && $id === $get_pages[ $p ]->ID ) {
				break;
			}
		}

		// Assign our next key.
		$prev_key = $p - 1;
		$last_key = $page_count - 1;

		// If there isn't a value assigned for the previous key, go all the way to the end.
		if ( isset( $get_pages[ $prev_key ] ) ) {
			$prev_page_id = $get_pages[ $prev_key ]->ID;
		}

		return $prev_page_id;
	}
} // End if().

if ( ! function_exists( 'get_post_years' ) ) {
	/**
	 *  Get years where there are posts.
	 *
	 *  @since  1.6.0
	 *  @param  string  $post_type post type to get post years, defaults to post.
	 *  @return array 	           array containing years where there are posts.
	 */
	function get_post_years( $post_type = 'post' ) {

		// Check if result is cached and if, return cached version.
		$result = get_transient( "get_{$post_type}_years_result" );
		if ( ! empty( $result ) ) {
			return $result;
		}

		global $wpdb;
		$result = array();

		// Do databse query to get years.
		$years = $wpdb->get_results( "SELECT YEAR(post_date) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$post_type}' GROUP BY YEAR(post_date) DESC", ARRAY_N );

		// Loop result.
		if ( is_array( $years ) && count( $years ) > 0 ) {
			foreach ( $years as $year ) {
				$result[] = $year[0];
			}
		}

		// Save result to cache for 30 minutes.
		set_transient( "get_{$post_type}_years_result", $result, MINUTE_IN_SECONDS * 30 );

		return $result;
	}
} // end if

if ( ! function_exists( 'get_post_months_by_year' ) ) {
	/**
	 *  Get months where there are posts in spesific year.
	 *
	 *  @since  1.6.0
	 *  @param  string  $year      year to get posts, defaults to current year.
	 *  @param  string  $post_type post type to get post years, defaults to post.
	 *  @return array 	           array containing months where there are posts.
	 */
	function get_post_months_by_year( $year = '', $post_type = 'post' ) {
		// Use current year if not defined.
		if ( empty( $year ) ) {
			$year = date( 'Y' );
		}

		// Check if result is cached and if, return cached version.
		$result = get_transient( "get_{$post_type}_months_by_year_{$year}_result" );
		if ( ! empty( $result ) ) {
			return $result;
		}

		global $wpdb;
		$result = array();

		// Do databse query to get years.
		$months = $wpdb->get_results( "SELECT DISTINCT MONTH(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = '{$post_type}' AND YEAR(post_date) = '".$year."' ORDER BY post_date DESC", ARRAY_N );

		// Loop result.
		if ( is_array( $months ) && count( $months ) > 0 ) {
			foreach ( $months as $month ) {
				$result[] = $month[0];
			}
		}

		// Save result to cache for 30 minutes.
		set_transient( "get_{$post_type}_months_by_year_{$year}_result", $result, MINUTE_IN_SECONDS * 30 );

		return $result;
	}
} // end if
