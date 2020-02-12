<?php
/**
 * News and help widget for upkeeping customers.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 15:16:02
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-12 15:36:07
 *
 * @package air-helper
 */

/**
 *  Add our news and support widget to dashboard. Also make sure that it is always first in
 *  order.
 *
 *  Turn off by using `remove_action( 'wp_dashboard_setup', 'air_helper_admin_dashboard_widgets_setup' )`
 *
 *  @since  1.7.0
 */
add_action( 'wp_dashboard_setup', 'air_helper_admin_dashboard_widgets_setup' );
function air_helper_admin_dashboard_widgets_setup() {
  // In which servers widget should be visible
  $hostnames_where_visible = apply_filters( 'air_helper_dashboard_widget_show_hostnames', array(
    'craft' => true,
    'ghost' => true,
  ) );

  // Check that widget is allowed to be visible on this site, bail if not
  if ( 'development' !== getenv( 'WP_ENV' ) && ! array_key_exists( php_uname( 'n' ), $hostnames_where_visible ) ) {
    return;
  }

  // Add the dashboard widget
  wp_add_dashboard_widget(
    'air-helper-help', // id
    __( 'Updates from Dude', 'air-helper' ), // name
    'air_helper_admin_dashboard_widget_callback' // callbac
  );

  // Alter the widget order and make our widget always first
  global $wp_meta_boxes;
  $widget = $wp_meta_boxes['dashboard']['normal']['core']['air-helper-help'];
  unset( $wp_meta_boxes['dashboard']['normal']['core']['air-helper-help'] );
  $wp_meta_boxes['dashboard']['side']['core']['air-helper-help'] = $widget; // phpcs:ignore
} // end air_helper_admin_dashboard_widgets_setup

/**
 *  Enqueue styles and scripts for dashboard widget.
 *
 *  @since  1.7.0
 */
add_action( 'admin_enqueue_scripts', 'air_helper_dashboard_widget_styles' );
function air_helper_dashboard_widget_styles() {
  wp_register_style( 'air-helper-dashboard-widget', air_helper_base_url() . '/assets/css/dashboard-widget.css', false, air_helper_version() );
  wp_enqueue_style( 'air-helper-dashboard-widget' );
} // end air_helper_dashboard_widget_styles

/**
 *  Output dashboard widget content.
 *
 *  @since  1.7.0
 *  @param  mixed   $post          where widget is shown
 *  @param  array   $callback_args arguments passed into callback function
 */
function air_helper_admin_dashboard_widget_callback( $post, $callback_args ) {
  // get data for widget
  $data = _air_helper_admin_dashboard_widget_get_data();

  // if no data, show error message and bail
  if ( empty( $data ) ) {
    echo wpautop( __( 'Error while fetching data.', 'air-helper' ) );
    return;
  } ?>

  <div class="air-helper-help-wrapper">

    <div class="news-wrapper">

      <?php // check if we have sheculed maintenances
      if ( ! empty( $data->maintenances ) ) :
        // show only first maintennace
        $maintenance = $data->maintenances[0];
        $statuspage_url = apply_filters( 'air_helper_dashboard_widget_statuspage_url', 'https://status.dude.fi' );

        if ( isset( $maintenance->start ) &&
          isset( $maintenance->end ) &&
          isset( $maintenance->title ) &&
          isset( $maintenance->desc )
        ) :
          // make maintenance start and end times to human readbale string
          $day_str = _air_helper_admin_dashboard_widget_get_time_str( $maintenance->start, $maintenance->end ); ?>
          <div class="maintenance">
            <h3><?php echo sanitize_text_field( $maintenance->title ) ?></h3>

            <p class="time">
              <?php echo $day_str ?>
            </p>

            <?php echo wpautop( wp_kses_post( $maintenance->desc ) ) ?>

            <p class="read-more">
              <a href="<?php echo $statuspage_url ?>" target="_blank"><?php _e( 'Server status on real time', 'air-helper' ) ?> &rarr;</a>
            </p>
          </div>
        <?php endif; // maintennace content isset
      endif; // ! empty( $data->maintenances )

      // check if we have news to show
      if ( ! empty( $data->news ) ) :

        // loop news
        foreach( $data->news as $news ) :

          // if no essential content, skip this and continue to next
          if ( ! isset( $news->content ) ||
            ! isset( $news->time ) ||
            ! isset( $news->title ) ||
            ! isset( $news->content )
          ) {
            continue;
          }

          // strip tags from content.
          $content = strip_tags( $news->content, '<a><i><b><br><strong><italic>' ); ?>
          <div class="news">
            <p class="time">
              <?php echo date_i18n( 'j.n.Y H:i', strtotime( $news->time ) ) ?>
            </p>

            <h3><?php echo sanitize_text_field( $news->title ) ?></h3>

            <?php echo wpautop( wp_kses_post( $content ) );

            if ( isset( $news->link ) ) :
              if ( ! empty( $news->link->href ) && ! empty( $news->link->title ) ) : ?>
                <p class="read-more">
                  <a href="<?php echo esc_url( $news->link->href ) ?>" target="_blank"><?php echo sanitize_text_field( $news->link->title ) ?> &rarr;</a>
                </p>
              <?php endif;
            endif; ?>

          </div>
        <?php endforeach; // loop news
      endif; // ! empty( $data->news ) ?>
    </div>

  </div>
<?php } // end function air_helper_admin_dashboard_widget_help_callback

/**
 *  Get data for the widget from helpwidget api.
 *
 *  @since  1.7.0
 *  @return mixed  false if no data, otherwise data object
 */
function _air_helper_admin_dashboard_widget_get_data() {
  // Get data from transient
  if ( $data = get_site_transient( 'air_helpwidget_data' ) ) {
    return $data;
  }

  // Make api reauest if data isn't in cache
  $api_base = _air_helper_admin_dashboard_widget_get_api_url();
  $api_access_token = _air_helper_admin_dashboard_widget_get_api_key();
  $request = wp_remote_request( "{$api_base}/v1/newsfeed?access_token={$api_access_token}" );

  if ( is_wp_error( $request ) ) {
    return false;
  }

  if ( empty( $request['body'] ) ) {
    return false;
  }

  // decode returned data
  $data = json_decode( $request['body'] );

  // Set data to cache
  set_site_transient( 'air_helpwidget_data', $data, apply_filters( 'air_helper_dashboard_widget_data_cache_lifetime', HOUR_IN_SECONDS ) );

  return $data;
} // end function _air_helper_admin_dashboard_widget_get_data

function _air_helper_admin_dashboard_widget_get_time_str( $start = null, $end = null ) {
  $day_str = '';

  // bail if not time provided
  if ( empty( $start ) ) {
    return $day_str;
  }

  // no end time provided or same as start, make simple string
  if ( empty( $end ) || $start === $end ) {
    $day_str = ucfirst( date_i18n( 'l j.n.Y H:i:s', strtotime( get_date_from_gmt( $start ) ) ) );
  } else {
    // get months and dates for comparison
    $start_month = date_i18n( 'M', strtotime( get_date_from_gmt( $start ) ) );
    $end_month = date_i18n( 'M', strtotime( get_date_from_gmt( $end ) ) );
    $start_day = date_i18n( 'D', strtotime( get_date_from_gmt( $start ) ) );
    $end_day = date_i18n( 'D', strtotime( get_date_from_gmt( $end ) ) );

    // make str start based on if start month and end month are same or if dates are same
    if ( $start_month !== $end_month ) {
      $day_str = ucfirst( date_i18n( 'l\n\a j.n. -', strtotime( get_date_from_gmt( $start ) ) ) );
    } elseif ( $start_day !== $end_day ) {
      $day_str = ucfirst( date_i18n( 'l\n\a j. -', strtotime( get_date_from_gmt( $start ) ) ) );
    }

    // add end day to str
    $day_str .= ucfirst( date_i18n( 'l\n\a j.n.Y', strtotime( get_date_from_gmt( $end ) ) ) );

    // and times to str
    $day_str .= date_i18n( ' H:i', strtotime( get_date_from_gmt( $start ) ) );
    $day_str .= date_i18n( ' - H:i', strtotime( get_date_from_gmt( $end ) ) );
  }

  // return str
  return $day_str;
} // end function _air_helper_admin_dashboard_widget_get_time_str

/**
 *  Get API base url for dashboard widget API calls.
 *
 *  @since  1.7.0
 *  @return string  api base url
 */
function _air_helper_admin_dashboard_widget_get_api_url() {
  return 'https://api.dude.fi/helpwidget';
} // end _air_helper_admin_dashboard_widget_get_api_url

/**
 *  Get key for dashbard widget API calls.
 *
 *  @since  1.7.0
 *  @return string  api key
 */
function _air_helper_admin_dashboard_widget_get_api_key() {
  return getenv( 'SENDGRID_API_KEY' );
} // end _air_helper_admin_dashboard_widget_get_api_key
