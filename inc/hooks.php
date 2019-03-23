<?php
/**
 *  Hooks to alter the way in which core works.
 *
 *  @package air-helper
 */

/**
 *  Force to address in wp_mail function so that test emails wont go to client.
 *  Turn off by using `remove_filter( 'wp_mail', 'air_helper_helper_force_mail_to' )`
 *
 *  @since  0.1.0
 */
if ( getenv( 'WP_ENV' ) === 'development' ) {
	add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
}

// Turn off by using `remove_filter( 'wp_mail', 'air_helper_helper_force_mail_to' )`
if ( getenv( 'WP_ENV' ) === 'staging' ) {
	add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
	add_filter( 'wp_mail_from', 'air_helper_staging_wp_mail_from' );
}

/**
 *  Force to address in wp_mail.
 *  Change allowed staging roles by using `add_filter( 'air_helper_helper_mail_to_allowed_roles', 'myprefix_override_air_helper_helper_mail_to_allowed_roles' )`
 *  Change address from admin_email by using `add_filter( 'air_helper_helper_mail_to', 'myprefix_override_air_helper_helper_mail_to' )`
 *
 *  @since  0.1.0
 *  @param 	array $args Default wp_mail agruments.
 *  @return array         New wp_mail agruments with forced to address
 */
function air_helper_helper_force_mail_to( $args ) {
	$to = apply_filters( 'air_helper_helper_mail_to', 'koodarit@dude.fi' );

	if ( getenv( 'WP_ENV' ) === 'staging' ) {
		$allowed_roles = apply_filters( 'air_helper_helper_mail_to_allowed_roles', array( 'administrator', 'editor', 'author' ) );
		$user = get_user_by( 'email', $args['to'] );

		if ( is_a( $user, 'WP_User' ) ) {
			if ( array_intersect( $allowed_roles, $user->roles ) ) {
				$to = $args['to'];
			}
		}
	}

	$args['to'] = apply_filters( 'air_helper_helper_mail_to', $to );
	return $args;
}

/**
 *  Force from address in staging.
 *
 *  @since  1.8.1
 *  @return string  Email address
 */
function air_helper_staging_wp_mail_from() {
	return 'wordpress@' . str_replace( array( 'http://', 'https://', '/wp' ), '', get_site_url() );
}

/**
 *  Do not force to address when sending notification to new user created.
 *  Turn off by using `remove_action( 'edit_user_created_user', 'air_helper_dont_force_created_user_mail' )`
 *
 *  @since  1.2.0
 *  @param  string $user_id ID of new user.
 *  @param  string $notify  Who to notify about user registration.
 */
function air_helper_dont_force_created_user_mail( $user_id, $notify ) {
	remove_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
	wp_send_new_user_notifications( $user_id, $notify );
	add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
}
add_action( 'edit_user_created_user', 'air_helper_dont_force_created_user_mail', 10, 2 );

/**
 * Remove archive title prefix.
 * Turn off by using `remove_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' )`
 *
 * @since  0.1.0
 * @param  string $title Default title.
 * @return string Title without prefix
 */
function air_helper_helper_remove_archive_title_prefix( $title ) {
	return preg_replace( '/^\w+: /', '', $title );
}
add_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' );

/**
 * Disable emojicons introduced with WP 4.2.
 * Turn off by using `remove_action( 'init', 'air_helper_helper_disable_wp_emojicons' )`
 *
 * @since  0.1.0
 * @link http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
 */
function air_helper_helper_disable_wp_emojicons() {
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	add_filter( 'emoji_svg_url', '__return_false' );

	// Disable classic smilies.
	add_filter( 'option_use_smilies', '__return_false' );

	add_filter( 'tiny_mce_plugins', 'air_helper_helper_disable_emojicons_tinymce' );
}
add_action( 'init', 'air_helper_helper_disable_wp_emojicons' );

/**
 * Disable emojicons introduced with WP 4.2.
 *
 * @since 0.1.0
 * @param array $plugins Plugins.
 */
function air_helper_helper_disable_emojicons_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/**
 * Clean up admin bar.
 * Turn off by using `remove_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_bar_links', 'myprefix_override_air_helper_helper_remove_admin_bar_links' )`
 *
 * @since  0.1.0
 */
function air_helper_helper_remove_admin_bar_links() {
	global $wp_admin_bar;

	$remove_items = apply_filters( 'air_helper_helper_remove_admin_bar_links', array(
		'about',
		'wporg',
		'documentation',
		'support-forums',
		'feedback',
		'updates',
		'comments',
	) );

	foreach ( $remove_items as $item ) {
		$wp_admin_bar->remove_menu( $item );
	}
}
add_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' );

/**
 * Clean up admin menu from stuff we usually don't need.
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_helper_remove_admin_menu_links', 999 )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_menu_links', 'myprefix_override_air_helper_helper_remove_admin_menu_links' )`
 *
 * @since  0.1.0
 */
function air_helper_helper_remove_admin_menu_links() {
	$remove_items = apply_filters( 'air_helper_helper_remove_admin_menu_links', array(
		'edit-comments.php',
		'themes.php?page=editcss',
		'widgets.php',
		'admin.php?page=jetpack',
	) );

	foreach ( $remove_items as $item ) {
		remove_menu_page( $item );
	}

	$remove_items = apply_filters( 'air_helper_helper_remove_admin_submenu_links', array(
		'index.php' => array(
			'update-core.php',
		),
	) );

	foreach ( $remove_items as $parent => $items ) {
		foreach ( $items as $item ) {
			remove_submenu_page( $parent, $item );
		}
	}
}
add_action( 'admin_menu', 'air_helper_helper_remove_admin_menu_links', 999 );

/**
 *  Remove plugins page from admin menu, execpt for users with spesific domain or override in user meta.
 *
 *  Turn off by using `remove_filter( 'air_helper_helper_remove_admin_menu_links', 'air_helper_maybe_remove_plugins_from_admin_menu' )`
 *  Modify list by using `add_filter( 'air_helper_dont_remove_plugins_admin_menu_link_from_domain', 'myprefix_dont_remove_plugins_admin_menu_link_from_domain' )`
 *
 *  @since  1.3.0
 *  @param  array $menu_links pages to remove from admin menu.
 */
function air_helper_maybe_remove_plugins_from_admin_menu( $menu_links ) {
	$current_user = get_current_user_id();
	$user = new WP_User( $current_user );
	$domain = apply_filters( 'air_helper_dont_remove_plugins_admin_menu_link_from_domain', 'dude.fi' );
	$meta_override = get_user_meta( $user->ID, '_airhelper_admin_show_plugins', true );

	if ( 'true' === $meta_override ) {
		return $menu_links;
	}

	if ( strpos( $user->user_email, "@{$domain}" ) === false ) {
		$menu_links[] = 'plugins.php';
		$menu_links[] = 'edit.php?post_type=acf-field-group';
	}

	return $menu_links;
}
add_filter( 'air_helper_helper_remove_admin_menu_links', 'air_helper_maybe_remove_plugins_from_admin_menu' );

/**
 * Hide WP updates nag.
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_wphidenag' )`
 *
 * @since  0.1.0
 */
function air_helper_wphidenag() {
	remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action( 'admin_menu', 'air_helper_wphidenag' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 * Turn off by using `remove_action( 'wp_head', 'air_helper_pingback_header' )`
 *
 * @since  0.1.0
 */
function air_helper_pingback_header() {
	if ( is_singular() && pings_open() ) :
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	endif;
}
add_action( 'wp_head', 'air_helper_pingback_header' );

/**
 *  Disable REST-API users endpoint.
 *  Turn off by using `remove_filter( 'rest_endpoints', 'air_helper_disable_rest_endpoints' )`
 *
 * 	@since  0.1.0
 */
function air_helper_disable_rest_endpoints( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
  	unset( $endpoints['/wp/v2/users'] );
  }

  if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
  	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
  }

  return $endpoints;
}
add_filter( 'rest_endpoints', 'air_helper_disable_rest_endpoints' );

/**
 * Remove WordPress Admin Bar when not on development env.
 * Turn off by using `remove_action( 'get_header', 'air_helper_remove_admin_login_header' )`
 *
 * @since  1.0.1
 * @link 	 http://davidwalsh.name/remove-wordpress-admin-bar-css
 */
function air_helper_remove_admin_login_header() {
	remove_action( 'wp_head', '_admin_bar_bump_cb' );
}
add_action( 'get_header', 'air_helper_remove_admin_login_header' );

if ( getenv( 'WP_ENV' ) === 'development' ) {
	/**
	 *  Better styles for admin bar when in development env.
	 *  Turn off by using `remove_action( 'wp_head', 'air_helper_dev_adminbar' )`
	 *
	 *  @since  1.0.1
	 */
  function air_helper_dev_adminbar() {

  	if ( ! is_user_logged_in() || 'true' !== get_user_option( 'show_admin_bar_front' ) ) {
  		return;
  	} ?>
    <style type="text/css">
      html {
        height: auto;
        top: 32px;
        position: relative;
      }

      @media screen and (max-width: 600px) {
        html {
          top: 46px;
        }
      }

     /* Hide WordPress logo */
     #wp-admin-bar-wp-logo {
       display: none;
     }

     /* Invert admin bar */
     #wpadminbar {
       background: #fff;
     }

     @media screen and (max-width: 600px) {
       #wpadminbar {
         position: fixed;
       }
     }

     #wpadminbar .ab-empty-item,
     #wpadminbar a.ab-item,
     #wpadminbar > #wp-toolbar span.ab-label,
     #wpadminbar > #wp-toolbar span.noticon {
       color: #23282d;
     }

     #wpadminbar #adminbarsearch:before,
     #wpadminbar .ab-icon:before,
     #wpadminbar .ab-item:before {
       color: #23282d;
       background: transparent;
     }

     #wpadminbar.nojs li:hover > .ab-sub-wrapper,
     #wpadminbar li.hover > .ab-sub-wrapper {
       top: 32px;
     }

     #wp-admin-bar-airhelperenv.air-helper-env-prod a {
  		background: #00bb00 !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-stage a {
  		background: orange !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-dev a {
  		background: red !important;
  		color: black !important;
  	}
   </style>
	<?php }
	add_action( 'wp_head', 'air_helper_dev_adminbar' );
} else {
	show_admin_bar( false );
}

/**
 * Add envarioment marker to adminbar.
 * Turn off by using `remove_action( 'admin_bar_menu', 'air_helper_adminbar_show_env' )`
 *
 * @since  1.1.0
 */
function air_helper_adminbar_show_env( $wp_admin_bar ) {
	$env = esc_attr__( 'production', 'air-helper' );
	$class = 'air-helper-env-prod';

	if ( getenv( 'WP_ENV' ) === 'staging' ) {
		$env = esc_attr__( 'staging', 'air-helper' );
		$class = 'air-helper-env-stage';
	} else if ( getenv( 'WP_ENV' ) === 'development' ) {
		$env = esc_attr__( 'development', 'air-helper' );
		$class = 'air-helper-env-dev';
	}

	$wp_admin_bar->add_node( array(
		'id'    => 'airhelperenv',
		'title' => wp_sprintf( __( 'Environment: %s', 'air-helper' ), $env ),
		'href'  => '#',
		'meta'  => array(
      'class' => $class
    ),
	) );

	if ( getenv( 'WP_ENV' ) === 'staging' ) {
		$updated_time = date_i18n( 'j.n.Y H:i:s', get_date_from_gmt( filemtime( get_template_directory() ), 'U' ) );
		$wp_admin_bar->add_node( array(
	    'parent'  => 'airhelperenv',
	    'id'    	=> 'airhelperenv-deployed',
	    'title' 	=> wp_sprintf( __( 'Updated: %s', 'air-helper' ), $updated_time ),
	    'href'  	=> '#',
	  ) );
	}
}
add_action( 'admin_bar_menu', 'air_helper_adminbar_show_env', 999 );

/**
 * Add envarioment marker styles.
 * Turn off by using `remove_action( 'admin_head', 'air_helper_adminbar_show_env_styles' )`
 *
 * @since  1.1.0
 */
function air_helper_adminbar_show_env_styles() { ?>
  <style>
  	#wp-admin-bar-airhelperenv.air-helper-env-prod > a {
  		background: #00bb00 !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-stage > a {
  		background: orange !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-dev > a {
  		background: red !important;
  		color: black !important;
  	}
  </style>
<?php }
add_action( 'admin_head', 'air_helper_adminbar_show_env_styles' );

/**
 * Remove the additional CSS section, introduced in 4.7, from the Customizer.
 * Add back by using `remove_action( 'customize_register', 'air_helper_customizer_remove_css_section' )`
 *
 * @param object $wp_customize WP_Customize_Manager.
 * @since  1.3.0
 */
function air_helper_customizer_remove_css_section( $wp_customize ) {
	$wp_customize->remove_section( 'custom_css' );
}
add_action( 'customize_register', 'air_helper_customizer_remove_css_section', 15 );

/**
 *  Show TinyMCE second editor tools row by default.
 *  Turn off by using `remove_filter( 'tiny_mce_before_init', 'air_helper_show_second_editor_row' )`
 *
 *  @since  1.3.0
 *  @param  array $tinymce tinymce options.
 */
function air_helper_show_second_editor_row( $tinymce ) {
	$tinymce['wordpress_adv_hidden'] = false;
	return $tinymce;
}
add_filter( 'tiny_mce_before_init', 'air_helper_show_second_editor_row' );

/**
 *  Strip unwanted html tags from titles
 *  Turn off by using `remove_filter( 'nav_menu_item_title', 'air_helper_strip_tags_menu_item' )`
 *  Turn off by using `remove_filter( 'the_title', 'air_helper_strip_tags_menu_item' )`
 *
 *  @since  1.4.1
 *  @param  string $title title to strip.
 *  @param  mixed  $arg_2 whatever filter can pass.
 *  @param  mixed  $arg_3 whatever filter can pass.
 *  @param  mixed  $arg_4 whatever filter can pass.
 */
function air_helper_strip_tags_menu_item( $title, $arg_2 = null, $arg_3 = null, $arg_4 = null ) {
	return strip_tags( $title, apply_filters( 'air_helper_allowed_tags_in_title', '<br><em><b><strong>' ) );
}
add_filter( 'nav_menu_item_title', 'air_helper_strip_tags_menu_item', 10, 4 );
add_filter( 'the_title', 'air_helper_strip_tags_menu_item', 10, 2 );

/**
 * Allow Gravity Forms to hide labels to add placeholders.
 * Turn off by using `add_filter( 'gform_enable_field_label_visibility_settings', '__return_false' )`
 *
 * @since  0.1.0
 */
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

/**
 *  Set Yoast SEO plugin metabox priority to low.
 *  Turn off by using `remove_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' )`
 *
 *  @since  0.1.0
 */
function air_helper_lowpriority_yoastseo() {
	return 'low';
}
add_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' );

/**
 *  Remove Update WP text from admin footer.
 *
 *  @since  1.3.0
 */
add_filter( 'update_footer', '__return_empty_string', 11 );

/**
 * Add support for correct UTF8 orderby for post_title and term name (äöå).
 * Turn off by using `remove_filter( 'init', 'air_helper_orderby_fix' )`
 * Props Teemu Suoranta https://gist.github.com/TeemuSuoranta/2174f78f37248aeef483526d1c5d176f
 *
 *  @since  1.5.0
 *  @return string ordering clause for query
 */
function air_helper_orderby_fix() {
	/**
	 * Add support for correct UTF8 orderby for post_title and term name (äöå).
	 *
	 *  @since  1.5.0
	 *  @param string $orderby ordering clause for query
	 *  @return string ordering clause for query
	 */
	add_filter( 'posts_orderby', function( $orderby ) use ( $wpdb ) {
		if ( strstr( $orderby, 'post_title' ) ) {
			$order        = ( strstr($orderby, 'post_title ASC' ) ? 'ASC' : 'DESC' );
			$old_orderby  = $wpdb->posts . '.post_title ' . $order;
			$utf8_orderby = 'CONVERT ( LCASE(' . $wpdb->posts . '.post_title) USING utf8) COLLATE utf8_bin ' . $order;

			// replace orderby clause in $orderby.
			$orderby = str_replace( $old_orderby, $utf8_orderby, $orderby );
		}

		return $orderby;
	} );

	/**
	 * Add support for correct UTF8 orderby for term name (äöå).
	 *
	 *  @since  1.5.0
	 *  @param string $orderby ordering clause for terms query
	 *  @param array  $this_query_vars an array of terms query arguments
	 *  @param array  $this_query_vars_taxonomy an array of taxonomies
	 *  @return string ordering clause for terms query
	 */
	add_filter( 'get_terms_orderby', function( $orderby, $this_query_vars, $this_query_vars_taxonomy ) {
		if ( strstr( $orderby, 't.name' ) ) {
			$old_orderby  = 't.name';
			$utf8_orderby = 'CONVERT ( LCASE(t.name) USING utf8) COLLATE utf8_bin ';

			// replace orderby clause in $orderby.
			$orderby = str_replace( $old_orderby, $utf8_orderby, $orderby );
		}

		return $orderby;
	}, 10, 3);
}
add_filter( 'init', 'air_helper_orderby_fix' );

/**
 *  Disable some views by default.
 *  archives: tag, category, date, author
 *  other: search
 *
 *  Turn off by using `remove_action( 'template_redirect', 'air_helper_disable_views' )`
 *  or spesific views, for example tag archive, with `add_filter( 'air_helper_disable_views_tag', '__return_false' )`
 *
 *  @since  1.6.0
 */
function air_helper_disable_views() {
	// Do not try to disable views if we don't have function to check version where plugin was activated.
	if ( ! function_exists( 'air_helper_activated_at_version' ) ) {
		return;
	}

	// If plugin vas activated before version 1.5.7, do NOT disable views.
	if ( air_helper_activated_at_version() < 157 ) {
		return;
	}

	// Enable tag archives by using `add_filter( 'air_helper_disable_views_tag', '__return_false' )`
	if ( apply_filters( 'air_helper_disable_views_tag', true ) ) {
		if ( is_tag() ) {
	    global $wp_query;
	    $wp_query->set_404();
	    status_header( 404 );
	  }
	}

	// Enable category archives by using `add_filter( 'air_helper_disable_views_category', '__return_false' )`
	if ( apply_filters( 'air_helper_disable_views_category', true ) ) {
		if ( is_category() ) {
	    global $wp_query;
	    $wp_query->set_404();
	    status_header( 404 );
	  }
	}

	// Enable date archives by using `add_filter( 'air_helper_disable_views_date', '__return_false' )`
	if ( apply_filters( 'air_helper_disable_views_date', true ) ) {
		if ( is_date() ) {
	    global $wp_query;
	    $wp_query->set_404();
	    status_header( 404 );
	  }
	}

	// Enable author archives by using `add_filter( 'air_helper_disable_views_author', '__return_false' )`
	if ( apply_filters( 'air_helper_disable_views_author', true ) ) {
		if ( is_author() ) {
	    global $wp_query;
	    $wp_query->set_404();
	    status_header( 404 );
	  }
	}

	// Enable search view by using `add_filter( 'air_helper_disable_views_search', '__return_false' )`
	if ( apply_filters( 'air_helper_disable_views_search', true ) ) {
		if ( is_search() ) {
	    global $wp_query;
	    $wp_query->set_404();
	    status_header( 404 );
	  }
	}
}
add_action( 'template_redirect', 'air_helper_disable_views' );

/**
 *  Remove some Tiny MCE formats from editor.
 *
 *  Turn off by using `remove_action( 'tiny_mce_before_init', 'air_helper_tinymce_remove_formats' )`
 *
 *  @since  1.7.0
 */
function air_helper_tinymce_remove_formats( $init ) {
	// Do not try to do this if we don't have function to check version where plugin was activated.
	if ( ! function_exists( 'air_helper_activated_at_version' ) ) {
		return $init;
	}

	// If plugin vas activated before version 1.7.0, do NOT do this.
	if ( air_helper_activated_at_version() < 170 ) {
		return $init;
	}

  $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
  return $init;
}
add_filter( 'tiny_mce_before_init', 'air_helper_tinymce_remove_formats' );

/**
 *  Unify and modify the login error message to be more general,
 *  so those do not exist any hint what did go wrong.
 *
 *  Turn off by using `remove_action( 'login_errors', 'air_helper_login_errors' )`
 *
 *  @since  1.8.0
 *  @return string  messag to display when login fails
 */
function air_helper_login_errors() {
	return __( '<b>Login failed.</b> Please contact your site admin or agency if you continue having problems.', 'air-helper' );
}
add_filter( 'login_errors', 'air_helper_login_errors' );

// Disable Try Gutenberg notification in dashboard added on 4.8.9.
remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );
