<?php

/**
 *  Force essential SendGrid settings.
 *  Turn off by using `add_filter( 'air_helper_sendgrid', '__return_false' )`
 *
 *  @since 0.1.0
 */
if ( apply_filters( 'air_helper_sendgrid', true ) ) {
	define( 'SENDGRID_API_KEY', getenv( 'SENDGRID_API_KEY' ) );
	define( 'SENDGRID_CATEGORIES', get_option( 'blogname' ) );
}

/**
 * Custom uploads folder media/ instead of default content/uploads/.
 * Turn off by using filter `add_filter( 'air_helper_change_uploads_path', '__return_false' )`
 *
 * @since 0.1.0
 */
if( apply_filters( 'air_helper_change_uploads_path', true ) ) {
	update_option( 'upload_path', untrailingslashit( str_replace( 'wp', 'media', ABSPATH ) ) );
	update_option( 'upload_url_path', untrailingslashit( str_replace( 'wp', 'media', get_site_url() ) ) );
	define( 'uploads', '' . 'media' );
	add_filter( 'option_uploads_use_yearmonth_folders', '__return_false', 100 );
}
