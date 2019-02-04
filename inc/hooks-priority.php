<?php

/**
 * @Author: 						Timi Wahalahti, Digitoimisto Dude Oy (https://dude.fi)
 * @Date:   						2019-02-04 12:07:32
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2019-02-04 12:12:05
 *
 * @package content
 */


/**
 *  Stop user enumeraton by ?author=(init) urls
 *  Turn off by using `remove_action( 'init', 'air_helper_stop_user_enumeration' )`
 *
 *  Merged by Davide Giunchi, from plugin "Stop User Enumeration"
 *
 *  @since  1.7.4
 */
function air_helper_stop_user_enumeration() {
	if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
		if ( preg_match('/(wp-comments-post)/', $_SERVER['REQUEST_URI' ]) === 0 && ! empty( $_REQUEST['author'] ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}
}
add_action( 'init', 'air_helper_stop_user_enumeration', 10 );
