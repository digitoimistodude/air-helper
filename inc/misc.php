<?php
/**
 *  Misc content.
 *
 *  @package air-helper
 */

/**
 *  Force essential SendGrid settings.
 *  Turn off by using `add_filter( 'air_helper_sendgrid', '__return_false' )`
 *
 *  @since 0.1.0
 *  @package air-helper
 */
if ( apply_filters( 'air_helper_sendgrid', true ) ) {

	/**
	 *  Check that SendGrid API key is set and show warning if not.
	 */
	add_action( 'admin_init', 'air_helper_sendgrid_check' );

	// Define SendGrid settings.
	define( 'SENDGRID_API_KEY', getenv( 'SENDGRID_API_KEY' ) );
	define( 'SENDGRID_CATEGORIES', get_option( 'blogname' ) );
}

/**
 *  Check if SendGrid is not active or configured.
 *
 *  @since  1.5.3
 */
function air_helper_sendgrid_check() {
	if ( ! getenv( 'SENDGRID_API_KEY' ) || ! is_plugin_active( 'sendgrid-email-delivery-simplified/wpsendgrid.php' ) ) {
		add_action( 'admin_notices', 'air_helper_sendgrid_not_configured_notice' );
	}
}

/**
 *  Show notice if SendGrid is not active or configured.
 *
 *  @since  1.5.3
 */
function air_helper_sendgrid_not_configured_notice() {
	$class = 'notice notice-error';
	$message = __( 'SendGrid email delivery plugin is not active or configured. Please contact your agency to fix this issue.', 'air-helper' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 * Custom uploads folder media/ instead of default content/uploads/.
 * Turn off by using filter `add_filter( 'air_helper_change_uploads_path', '__return_false' )`
 *
 * @since 0.1.0
 */
if ( apply_filters( 'air_helper_change_uploads_path', true ) ) {
	update_option( 'upload_path', untrailingslashit( str_replace( 'wp', 'media', ABSPATH ) ) );
	update_option( 'upload_url_path', untrailingslashit( str_replace( 'wp', 'media', get_site_url() ) ) );
	define( 'uploads', '' . 'media' );
	add_filter( 'option_uploads_use_yearmonth_folders', '__return_false', 100 );
}
