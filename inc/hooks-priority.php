<?php
/**
 * Priority hooks to run on init action with order 5.
 *
 * @Author: 						Timi Wahalahti, Digitoimisto Dude Oy (https://dude.fi)
 * @Date:   						2019-02-04 12:07:32
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2019-05-08 17:19:13
 *
 * @package air-helper
 */

/**
 *  Stop user enumeraton by ?author=(init) urls
 *  Turn off by using `remove_action( 'init', 'air_helper_stop_user_enumeration' )`
 *
 *  Idea by Davide Giunchi, from plugin "Stop User Enumeration"
 *
 *  @since  1.7.4
 */
function air_helper_stop_user_enumeration() {
	if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
		if ( preg_match('/(wp-comments-post)/', $_SERVER['REQUEST_URI'] ) === 0 && ! empty( $_REQUEST['author'] ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}
}
add_action( 'init', 'air_helper_stop_user_enumeration', 10 );

/**
 *  Add honeypot to the login form.
 *
 *  For login to succeed, we require that the field value is exactly
 *  six characters long and has is prefixed with correct three letters.
 *  Prefix cannot be older than 30 minutes. After the prefix, following
 *  three charters can be anything. Store the prefix and generation time
 *  to the options table for later use.
 *
 *  Append the three charters with javascript and hide the field. In case
 *  user has javascript disabled, the label describes what the input is and
 *  what to do with that. This is unlikely to happen, but better safe than
 *  sorry.
 *
 *  @since  1.9.0
 */
function air_helper_login_honeypot_form() {
	// Generate new prefix to honeypot if it's older than 30 minutes
	$prefix = get_option( 'air_helper_login_honeypot' );
	if ( ! $prefix || $prefix['generated'] < strtotime( '-30 minutes' ) ) {
		$prefix = air_helper_login_honeypot_reset_prefix();
	} ?>

	<p id="air_lh_name_field" class="air_lh_name_field">
		<label for="air_lh_name"><?php _e( 'Append three letters to this input', 'air-helper' ); ?></label><br />
		<input type="text" name="air_lh_name" id="air_lh_name" class="input" value="<?php echo $prefix['prefix']; ?>" size="20" autocomplete="off" />
	</p>

	<script type="text/javascript">
    var text = document.getElementById('air_lh_name');
    text.value += '<?php echo wp_generate_password( 3, false ); ?>';
    document.getElementById('air_lh_name_field').style.display = 'none';
	</script>
<?php }
add_action( 'login_form', 'air_helper_login_honeypot_form', 99 );

/**
 *  Check if login form honeypot seems legit.
 *
 *  @since  1.9.0
 *  @param  mixed  $user      if the user is authenticated. WP_Error or null otherwise.
 *  @param  string $username  username or email address.
 *  @param  string $password  user password.
 *  @return mixed             WP_User object if honeypot passed, null otherwise.
 */
function air_helper_login_honeypot_check( $user, $username, $password ) {
	// field is required
	if ( ! empty( $_POST ) ) {
		if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
			return $user;
		}

		if ( ! isset( $_POST['air_lh_name'] ) ) {
			return null;
		}

		// field cant be empty
		if ( empty( $_POST['air_lh_name'] ) ) {
			return null;
		}

		// value needs to be exactly six charters long
		if ( 6 !== mb_strlen( $_POST['air_lh_name'] ) ) {
			return null;
		}

		// bother database at this point
		$prefix = get_option( 'air_helper_login_honeypot' );

		// prefix is too old
		if ( $prefix['generated'] < strtotime( '-30 minutes' ) ) {
			return null;
		}

		// prefix is not correct
		if ( substr( $_POST['air_lh_name'], 0, 3 ) !== $prefix['prefix'] ) {
			return null;
		}
	}

	return $user;
}
add_action( 'authenticate', 'air_helper_login_honeypot_check', 1000, 3 );

/**
 *  Reset login form honeypot prefix on call and after succesfull login.
 *
 *  @since  1.9.0
 *  @return array  prexif generation time an prefix itself
 */
function air_helper_login_honeypot_reset_prefix() {
	$prefix = array(
		'generated'	=> time(),
		'prefix'		=> wp_generate_password( 3, false ),
	);

	update_option( 'air_helper_login_honeypot', $prefix, false );

	return $prefix;
}
add_action( 'wp_login', 'air_helper_login_honeypot_reset_prefix' );

/**
 *  We take care of multiple things, user does not need to know all the details.
 *
 *  @since  1.10.0
 */
function air_helper_remove_status_tests( $tests ) {
	// We take care of server requirements.
	unset( $tests['direct']['php_version'] );
	unset( $tests['direct']['sql_server'] );
	unset( $tests['direct']['php_extensions'] );
	unset( $tests['direct']['utf8mb4_support'] );

	// We provide the updates.
	unset( $tests['direct']['wordpress_version'] );
	unset( $tests['direct']['plugin_version'] );
	unset( $tests['direct']['theme_version'] );
	unset( $tests['async']['background_updates'] );

	return $tests;
}
add_filter( 'site_status_tests', 'air_helper_remove_status_tests' );

/**
 *  We take care of multiple things, user does not need to know all the details.
 *
 *  @since  1.10.0
 */
function air_helper_remove_debug_information( $debug_info ) {
	unset( $debug_info['wp-server'] );
	unset( $debug_info['wp-paths-sizes'] );
	unset( $debug_info['wp-database'] );
	unset( $debug_info['wp-constants'] );
	unset( $debug_info['wp-filesystem'] );
	unset( $debug_info['wp-media'] );

	return $debug_info;
}
add_filter( 'debug_information', 'air_helper_remove_debug_information' );
