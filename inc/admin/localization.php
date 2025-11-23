<?php
/**
 *  Register string for Polylang for localization.
 *
 *  Big thanks to Aucor Oy and escpecially Teemy Suoranta for the
 *  original (GPL-2.o licensed) code, which this file is based on.
 *  https://github.com/aucor/aucor-starter/blob/master/inc/localization.php
 *
 *  @package air-helper
 */

/**
 *  Register Polylang translatable strings.
 *
 *  @since 1.4.0
 */
if ( function_exists( 'pll_register_string' ) ) {
	$strings = apply_filters( 'air_helper_pll_register_strings', [] );

	if ( is_array( $strings ) ) {
		foreach ( $strings as $key => $value ) {
			pll_register_string( $key, $value, apply_filters( 'air_helper_pll_register_string_group', 'Theme' ) );
		}
	}
} else {
  if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
  }

  class Air_Helper_Localization_Strings_Table extends WP_List_Table {
    function get_columns() { // phpcs:ignore
      $columns = [
        'string_with_key' => 'Key',
        'string'          => 'String',
      ];

      return $columns;
    } // end get_columns

    function prepare_items() { // phpcs:ignore
      $columns = $this->get_columns();
      $hidden = [];
      $sortable = [];
      $this->_column_headers = [ $columns, $hidden, $sortable ];

      $strings_raw = apply_filters( 'air_helper_pll_register_strings', [] );
      $strings_saved = get_option( 'air_helper_localization_string_overrides', [] );

      if ( is_array( $strings_raw ) ) {
        foreach ( $strings_raw as $string_with_key => $string ) {
          $option_key = sanitize_title( $string_with_key );

          $this->items[] = [
            'option_key'      => $option_key,
            'string_with_key' => $string_with_key,
            'string'          => isset( $strings_saved[ $option_key ] ) ? $strings_saved[ $option_key ] : '',
            'default'         => $string,
          ];
        }
      }
    } // end prepare_items

    function column_default( $item, $column_name ) { // phpcs:ignore
      switch ( $column_name ) {
        case 'string_with_key':
          return $item[ $column_name ]; // phpcs:ignore
        default:
          ob_start(); ?>
          <input type="text" name="strings[<?php echo esc_attr( $item['option_key'] ); ?>]" value="<?php echo esc_html( $item['string'] ); ?>" placeholder="<?php echo esc_html( $item['default'] ); ?>" style="width:100%;" />
          <?php return ob_get_clean();
      }
    } // end column_default
  } // end Air_Helper_Localization_Strings_Table

  add_action( 'admin_menu', 'air_helper_localization_strings_override_menu_page' );
}

function air_helper_localization_strings_override_menu_page() { // phpcs:ignore
  add_submenu_page(
    'options-general.php',
    __( 'Localization strings', 'air-helper' ),
    __( 'Localization strings', 'air-helper' ),
    'manage_options',
    'air-helper-localization-strings-override',
    'air_helper_localization_strings_override_page'
  );
} // end air_helper_localization_strings_override_menu_page

function air_helper_localization_strings_override_page() {
  global $title, $plugin_page;

  air_helper_localization_strings_override_maybe_handle_reset();
  air_helper_localization_strings_override_maybe_handle_save();

  $table = new Air_Helper_Localization_Strings_Table();
  $table->prepare_items();

  ob_start(); ?>

  <div class="wrap">
    <h2><?php echo esc_html( $title ); ?></h2>

    <form method="post" action="options-general.php?page=<?php echo esc_attr( $plugin_page ); ?>">
      <?php $table->display();
      wp_nonce_field( -1, '_wpnonce_air_helper_strings' ); ?>

      <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Save' ); ?>">
    </form>

    <p class="button-wrapper">
      <a href="<?php echo esc_url( wp_nonce_url( 'options-general.php?page=' . $plugin_page, -1, '_wpnonce_air_helper_strings_reset' ) ); ?>">
        <?php esc_html_e( 'Reset to default' ); ?>
      </a>
    </p>
  </div>

  <?php echo ob_get_clean(); // phpcs:ignore
} // end air_helper_localization_strings_override_page

function air_helper_localization_strings_override_maybe_handle_reset() {
  if ( ! isset( $_GET['_wpnonce_air_helper_strings_reset'] ) ) {
    return;
  }

  if ( ! wp_verify_nonce( $_GET['_wpnonce_air_helper_strings_reset'], -1 ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    return;
  }

  update_option( 'air_helper_localization_string_overrides', false );

  $message = __( 'Localization string overrides removed.', 'air-helper' );
  printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( $message ) );
} // end air_helper_localization_strings_override_maybe_handle_reset

function air_helper_localization_strings_override_maybe_handle_save() {
  if ( ! isset( $_POST['_wpnonce_air_helper_strings'] ) ) {
    return;
  }

  if ( ! wp_verify_nonce( $_POST['_wpnonce_air_helper_strings'], -1 ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    return;
  }

  if ( ! isset( $_POST['strings'] ) ) {
    return;
  }

  if ( ! is_array( $_POST['strings'] ) ) {
    return;
  }

  $strings = array_filter( $_POST['strings'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
  if ( empty( $strings ) ) {
    return;
  }

  update_option( 'air_helper_localization_string_overrides', $strings );

  $message = __( 'Localization string overrides saved.', 'air-helper' );
  printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( $message ) );
} // end air_helper_localization_strings_override_maybe_handle_save
