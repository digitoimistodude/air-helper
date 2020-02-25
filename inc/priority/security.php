<?php
/**
 * Prioritized security related actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:00:16
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:42:34
 *
 * @package air-helper
 */

/**
 *  Stop user enumeraton by ?author=(init) urls.
 *  Idea by Davide Giunchi, from plugin "Stop User Enumeration"
 *
 *  Turn off by using `remove_action( 'init', 'air_helper_stop_user_enumeration' )`
 *
 *  @since  1.7.4
 */
add_action( 'init', 'air_helper_stop_user_enumeration', 10 );
function air_helper_stop_user_enumeration() {
  if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
    if ( preg_match( '/(wp-comments-post)/', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) === 0 && ! empty( $_REQUEST['author'] ) ) {
      wp_safe_redirect( home_url() );
      exit;
    }
  }
} // end air_helper_stop_user_enumeration

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
add_action( 'login_form', 'air_helper_login_honeypot_form', 99 );
function air_helper_login_honeypot_form() {
  // Generate new prefix to honeypot if it's older than 30 minutes
  $prefix = get_option( 'air_helper_login_honeypot' );
  if ( ! $prefix || $prefix['generated'] < strtotime( '-30 minutes' ) ) {
    $prefix = air_helper_login_honeypot_reset_prefix();
  } ?>

  <p id="air_lh_name_field" class="air_lh_name_field">
    <label for="air_lh_name"><?php echo esc_html( 'Append three letters to this input', 'air-helper' ); ?></label><br />
    <input type="text" name="air_lh_name" id="air_lh_name" class="input" value="<?php echo esc_attr( $prefix['prefix'] ); ?>" size="20" autocomplete="off" />
  </p>

  <script type="text/javascript">
    var text = document.getElementById('air_lh_name');
    text.value += '<?php echo esc_attr( wp_generate_password( 3, false ) ); ?>';
    document.getElementById('air_lh_name_field').style.display = 'none';
  </script>
<?php } // end air_helper_login_honeypot_form

/**
 *  Check if login form honeypot seems legit.
 *
 *  @since  1.9.0
 *  @param  mixed  $user      if the user is authenticated. WP_Error or null otherwise.
 *  @param  string $username  username or email address.
 *  @param  string $password  user password.
 *  @return mixed             WP_User object if honeypot passed, null otherwise.
 *
 *  phpcs:disable WordPress.Security.NonceVerification.Missing
 */
add_action( 'authenticate', 'air_helper_login_honeypot_check', 1000, 3 );
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
    if ( 6 !== mb_strlen( sanitize_text_field( wp_unslash( $_POST['air_lh_name'] ) ) ) ) {
      return null;
    }

    // bother database at this point
    $prefix = get_option( 'air_helper_login_honeypot' );

    // prefix is too old
    if ( $prefix['generated'] < strtotime( '-30 minutes' ) ) {
      return null;
    }

    // prefix is not correct
    if ( substr( sanitize_text_field( wp_unslash( $_POST['air_lh_name'] ) ), 0, 3 ) !== $prefix['prefix'] ) {
      return null;
    }
  }

  return $user;
} // end air_helper_login_honeypot_check
// phpcs: enable WordPress.Security.NonceVerification.Missing

/**
 *  Reset login form honeypot prefix on call and after succesfull login.
 *
 *  @since  1.9.0
 *  @return array  prexif generation time an prefix itself
 */
add_action( 'wp_login', 'air_helper_login_honeypot_reset_prefix' );
function air_helper_login_honeypot_reset_prefix() {
  $prefix = [
    'generated' => time(),
    'prefix'    => wp_generate_password( 3, false ),
  ];

  update_option( 'air_helper_login_honeypot', $prefix, false );

  return $prefix;
} // end air_helper_login_honeypot_reset_prefix

/**
 *  Unify and modify the login error message to be more general,
 *  so those do not exist any hint what did go wrong.
 *
 *  Turn off by using `remove_action( 'login_errors', 'air_helper_login_errors' )`
 *
 *  @since  1.8.0
 *  @return string  messag to display when login fails
 */
add_filter( 'login_errors', 'air_helper_login_errors' );
function air_helper_login_errors() {
  return __( '<b>Login failed.</b> Please contact your site admin or agency if you continue having problems.', 'air-helper' );
} // end air_helper_login_errors
