<?php

/**
 * Wsb Hub3 Plugin shortcodes
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.6
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */

/**
 * This class defines shortcodes for the plugin.
 *
 * @since      1.0.6
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 * @author     Branko Borilovic <brana@webstudiobrana.com>
 */
class Wsb_Hub3_Shortcodes {

	/**.
	 * @since    1.0.6
	 */
	public static function init() {

		add_shortcode( 'wsb_hub3', array( __CLASS__, 'hub3_display' ) );
		add_shortcode( 'wsb_barcode', array( __CLASS__, 'barcode_display' ) );
		
	}
	
	public static function hub3_display($atts) {
		
		/*
		 * Shortcode attributes with default values defined
		 */
		$a = shortcode_atts( array( 
			'width'	  => 1100
   		), $atts );

		$order = self::order_to_show();
		if ( ! $order ) {
			return '';
		}
		$hub3_image = Wsb_Hub3_Files::url( $order->get_meta('_wsb_hub3_slip') );
		if ( ! $hub3_image ) {
			return '';
		}
		$slip_width = ( absint( $a['width'] ) ?: 1100 ) . 'px';
		return "<div class='slipdiv'><a title='" . esc_attr__( 'Enlarge (New window)', 'wsb-hub3' ) . "' href='". esc_url( $hub3_image ) ."' target='new'><img style='width: " . esc_attr($slip_width) . "' src='". esc_url( $hub3_image ) ."' alt='HUB-3A' /></a></div>";

	}

	public static function barcode_display($atts) {
		
		/*
		 * Shortcode attributes with default values defined
		 */
		$a = shortcode_atts( array( 
			'width'	  => 400
   		), $atts );

		$order = self::order_to_show();
		if ( ! $order ) {
			return '';
		}
		$barcode_image = Wsb_Hub3_Files::url( $order->get_meta('_wsb_hub3_barcode') );
		if ( ! $barcode_image ) {
			return '';
		}
		$barcode_width = ( absint( $a['width'] ) ?: 400 ) . 'px';
		return "<p class='barcode-text'><img style='width: " . esc_attr($barcode_width) . "' src='". esc_url( $barcode_image ) ."' alt='barcode' /></p>";

	}

	/**
	 * Order from the thank-you URL (order-received) or from ?order_id= on a custom thank-you page,
	 * if the visitor may see it and HUB3 data is shown for it. Missing images are recreated.
	 * @since    3.1.0
	 */
	private static function order_to_show() {
		global $wp;
		if ( isset( $wp->query_vars['order-received'] ) ) {
			$order_id = apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) );
		} else {
			$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		}
		$order = $order_id ? wc_get_order( $order_id ) : false;
		if ( ! $order || ! self::can_view( $order ) ) {
			return null;
		}
		if ( 'yes' === get_option( 'wsb_hub3_croatian_customers_only', 'no' ) && 'HR' !== $order->get_billing_country() ) {
			return null;
		}
		$status_to_display = str_replace( 'wc-', '', get_option( 'wsb_hub3_order_status', 'on-hold' ) );
		if ( 'bacs' !== $order->get_payment_method() || $status_to_display !== $order->get_status() ) {
			return null;
		}
		// The stylesheet is otherwise only loaded on checkout and account pages.
		wp_enqueue_style( 'wsb-hub3' );
		return apply_filters( 'wsb_hub3_order_images', $order );
	}

	/**
	 * The order ID comes from the URL, so only show payer data with the order key or to the logged-in owner.
	 * @since    3.1.0
	 */
	private static function can_view( $order ) {
		$key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
		if ( '' !== $key && $order->key_is_valid( $key ) ) {
			return true;
		}
		$customer_id = (int) $order->get_customer_id();
		return $customer_id > 0 && get_current_user_id() === $customer_id;
	}

}