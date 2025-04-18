<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://www.webstudiobrana.com
 * @since             1.0.0
 * @package           Wsb_Hub3
 *
 * @wordpress-plugin
 * Plugin Name:       WSB HUB3
 * Plugin URI:        https://www.webstudiobrana.com/wsb-hub3
 * Description:       Barcode payment details plugin for Woocommerce (for Croatian banks)
 * Version:           3.0.2
 * Author:            Branko Borilovic
 * Author URI:        https://profiles.wordpress.org/branahr
 * WC requires at least: 7.1
 * WC tested up to: 	 9.8
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wsb-hub3
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WSB_HUB3_VERSION', '3.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wsb-hub3-activator.php
 */
function activate_wsb_hub3() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wsb-hub3-activator.php';
	Wsb_Hub3_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wsb-hub3-deactivator.php
 */
function deactivate_wsb_hub3() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wsb-hub3-deactivator.php';
	Wsb_Hub3_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wsb_hub3' );
register_deactivation_hook( __FILE__, 'deactivate_wsb_hub3' );

/**
 * Check if WooCommerce is active
 */
if ( ! class_exists( 'WooCommerce' ) ) {
	add_action( 'admin_notices', 'wsb_woocommerce_missing_notice' );
	return;
} else {
	/**
	 * Declaration of HPOS compatibility
	 */
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
	
		
	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-wsb-hub3.php';
	/**
	 * Begins execution of the plugin.
	 *
	 * @since    1.0.0
	 */
	function run_wsb_hub3() {

		$plugin = new Wsb_Hub3();
		$plugin->run();

	}

	run_wsb_hub3();
}

function wsb_woocommerce_missing_notice() {
	load_plugin_textdomain( 'wsb-hub3', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	echo '<div class="error"><p><strong>' . esc_html__( 'WSB HUB3 requires WooCommerce to be installed and active.', 'wsb-hub3' ) . '</strong></p></div>';
}