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

		$html = '';

		if(is_checkout()){
			global $wp;
			$order_id = 0;
			if( isset($wp->query_vars['order-received']) ){
				$order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) );
			} else if ( isset($_REQUEST['order_id']) ){
				$order_id  = absint($_REQUEST['order_id']);
			}
			if ($order_id == 0 ) return;
			$order = wc_get_order( $order_id );
			if(!$order) return;
			$country = $order->get_billing_country();
			$status_to_display = str_replace("wc-", "", get_option( 'wsb_hub3_order_status', 'on-hold' ));
			$croatian_only = esc_html(get_option( 'wsb_hub3_croatian_customers_only', 'no' ));
			if( "yes" == $croatian_only ){
				if('HR' != $country){
					return;
				}
			}
			$payment_method = $order->get_payment_method();
			$order_status = $order->get_status();
			if('bacs' != $payment_method || $status_to_display != $order_status ) {
				return;
			}
			$slip_width = $a['width'] . "px";
				if (OrderUtil::custom_orders_table_usage_is_enabled()) {
					$hub3_image = $order->get_meta('_wsb_hub3_slip');
				} else {
					$hub3_image = get_post_meta( $order_id, '_wsb_hub3_slip', true );
				}
				
				if($hub3_image){
					$html="<div class='slipdiv'><a title='" . __( 'Enlarge (New window)', 'wsb-hub3' ) . "' href='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image ) ."' target='new'><img style='width: " . esc_html($slip_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image ) ."' alt='HUB-3A' /></a></div>";
				}
		}
		return $html;

	}

	public static function barcode_display($atts) {
		
		/*
		 * Shortcode attributes with default values defined
		 */
		$a = shortcode_atts( array( 
			'width'	  => 400
   		), $atts );

		$html = '';

		if(is_checkout()){
			global $wp;
			$order_id = 0;
			if( isset($wp->query_vars['order-received']) ){
				$order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) );
			} else if ( isset($_REQUEST['order_id']) ){
				$order_id  = absint($_REQUEST['order_id']);
			}
			if ($order_id == 0 ) return;
			$order = wc_get_order( $order_id );
			if(!$order) return;
			$country = $order->get_billing_country();
			$croatian_only = esc_html(get_option( 'wsb_hub3_croatian_customers_only', 'no' ));
			if( "yes" == $croatian_only ){
				if('HR' != $country){
					return;
				}
			}
			$payment_method = $order->get_payment_method();
			$order_status = $order->get_status();
			$status_to_display = str_replace("wc-", "", get_option( 'wsb_hub3_order_status', 'on-hold' ));
			if( 'bacs' != $payment_method || $status_to_display != $order_status) {
				return;
			}
			if (OrderUtil::custom_orders_table_usage_is_enabled()) {
				$barcode_image = $order->get_meta('_wsb_hub3_barcode');
			} else {
				$barcode_image = get_post_meta( $order_id, '_wsb_hub3_barcode', true );
			}
			$barcode_width = $a['width'] . "px";
			if($barcode_image){
				$html.="<p class='barcode-text'><img style='width: " . esc_html($barcode_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $barcode_image ) ."' alt='barcode' /></p>";
			}
		}
		return $html;

	}

}