<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */

/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 * @author     Branko Borilovic <brana.hr@gmail.com>
 */
class Wsb_Hub3_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once __DIR__ . '/class-wsb-hub3-paybycode-notice.php';
		Wsb_Hub3_Paybycode_Notice::activate( WSB_HUB3_VERSION );
	}

}
