<?php

/**
 * Block checkout integration: lets the customer choose the bank account for the HUB3 payment.
 *
 * @link       https://www.webstudiobrana.com
 * @since      3.1.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/public
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

class Wsb_Hub3_Blocks_Integration implements IntegrationInterface {

	private $version;

	public function __construct( $version ) {
		$this->version = $version;
	}

	public function get_name() {
		return 'wsb-hub3';
	}

	public function initialize() {
		wp_register_script(
			'wsb-hub3-checkout-block',
			plugin_dir_url( __FILE__ ) . 'js/wsb-hub3-checkout-block.js',
			array( 'wc-blocks-checkout', 'wc-blocks-components', 'wc-blocks-data-store', 'wc-settings', 'wp-data', 'wp-element' ),
			$this->version . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'js/wsb-hub3-checkout-block.js' ),
			true
		);
	}

	public function get_script_handles() {
		return array( 'wsb-hub3-checkout-block' );
	}

	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * Available in JavaScript as wc.wcSettings.getSetting( 'wsb-hub3_data' ).
	 */
	public function get_script_data() {
		$accounts = array();
		foreach ( Wsb_Hub3_Public::bacs_accounts() as $account ) {
			$accounts[] = array(
				'iban'          => $account['iban'],
				'name'          => implode( ' - ', array_filter( array( $account['account_name'], $account['bank_name'] ) ) ),
				'ibanFormatted' => trim( chunk_split( $account['iban'], 4, ' ' ) ),
			);
		}
		return array(
			'accounts' => $accounts,
			'label'    => __( 'Account to pay', 'wsb-hub3' ),
		);
	}
}
