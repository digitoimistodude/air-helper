<?php
/**
 * Yoast SEO plugin actions.
 *
 * @Author: Timi Wahalahti
 * @Date:   2020-01-10 16:30:25
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2020-02-11 14:59:14
 *
 * @package air-helper
 */

/**
 *  Set Yoast SEO plugin metabox priority to low.
 *
 *  Turn off by using `remove_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' )`
 *
 *  @since  0.1.0
 */
add_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' );
function air_helper_lowpriority_yoastseo() {
  return 'low';
} // end air_helper_lowpriority_yoastseo
