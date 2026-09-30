<?php

/**
 * Define the internationalization functionality
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */

/**
 * @since      1.0.0
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 * @author     Branko Borilovic <brana.hr@gmail.com>
 */
class Wsb_Hub3_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wsb-hub3',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages'
		);

		// A WordPress.org language pack lags behind new plugin versions, so bundled files fill in strings it lacks.
		// The first loaded file wins: language pack first, bundled translations second.
		$locale = determine_locale();
		load_textdomain( 'wsb-hub3', WP_LANG_DIR . '/plugins/wsb-hub3-' . $locale . '.mo', $locale );
		load_textdomain( 'wsb-hub3', dirname( __DIR__ ) . '/languages/wsb-hub3-' . $locale . '.mo', $locale );

	}



}
