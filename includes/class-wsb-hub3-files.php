<?php

/**
 * Storage of generated barcodes and HUB-3A slips.
 *
 * @link       https://www.webstudiobrana.com
 * @since      3.1.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */

/**
 * Images are kept in uploads, because WordPress deletes the plugin folder on every plugin update.
 *
 * @since      3.1.0
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */
class Wsb_Hub3_Files {

	const DIR = 'wsb-hub3';

	/**
	 * Absolute path of the storage folder, created on first use.
	 */
	public static function dir() {
		$uploads = wp_upload_dir( null, false );
		$dir     = trailingslashit( $uploads['basedir'] ) . self::DIR . '/';
		if ( ! is_dir( $dir ) && wp_mkdir_p( $dir ) ) {
			file_put_contents( $dir . 'index.php', "<?php\n// Silence is golden.\n" );
		}
		return $dir;
	}

	/**
	 * Absolute path of a stored file. basename() keeps meta values from pointing outside the folder.
	 */
	public static function path( $file ) {
		return self::dir() . basename( (string) $file );
	}

	public static function exists( $file ) {
		return '' !== (string) $file && is_file( self::path( $file ) );
	}

	/**
	 * Public URL of a stored file, or '' if it doesn't exist. Versioned so browsers reload regenerated images.
	 */
	public static function url( $file ) {
		if ( ! self::exists( $file ) ) {
			return '';
		}
		$uploads = wp_upload_dir( null, false );
		return trailingslashit( $uploads['baseurl'] ) . self::DIR . '/' . rawurlencode( basename( $file ) ) . '?ver=' . filemtime( self::path( $file ) );
	}

	/**
	 * File name for an order image. The random token keeps other visitors from guessing it from the order ID.
	 */
	public static function name( $order, $type, $ext ) {
		$token = (string) $order->get_meta( '_wsb_hub3_file_token' );
		if ( ! preg_match( '/^[A-Za-z0-9]{20,}$/', $token ) ) {
			$token = wp_generate_password( 24, false );
			$order->update_meta_data( '_wsb_hub3_file_token', $token );
		}
		return $type . '-' . $order->get_id() . '-' . $token . '.' . $ext;
	}

	/**
	 * Moves images saved by versions before 3.1.0 out of the plugin folder, $limit files per call.
	 * Returns true when no old files are left.
	 */
	public static function migrate_legacy( $limit = 100 ) {
		$legacy = plugin_dir_path( __DIR__ ) . 'barcodes/';
		$files  = array_merge( (array) glob( $legacy . 'barcode_*' ), (array) glob( $legacy . 'hub-3a-*' ) );
		$batch  = array_slice( array_filter( $files ), 0, $limit );

		foreach ( $batch as $old ) {
			if ( ! preg_match( '/^(barcode_|hub-3a-)(\d+)\.(png|jpe?g|gif)$/', basename( $old ), $m ) ) {
				continue;
			}
			$order = wc_get_order( (int) $m[2] );
			if ( ! $order ) {
				// Payer details of a deleted order shouldn't stay publicly reachable.
				unlink( $old );
				continue;
			}
			$is_barcode = 'barcode_' === $m[1];
			$meta_key   = $is_barcode ? '_wsb_hub3_barcode' : '_wsb_hub3_slip';
			// Already recreated from current order data, so the old copy is outdated.
			if ( self::exists( $order->get_meta( $meta_key ) ) ) {
				unlink( $old );
				continue;
			}
			$new = self::name( $order, $is_barcode ? 'barcode' : 'hub-3a', $m[3] );
			// rename() fails when uploads is on another disk.
			if ( rename( $old, self::path( $new ) ) || ( copy( $old, self::path( $new ) ) && unlink( $old ) ) ) {
				$order->update_meta_data( $meta_key, $new );
				$order->save_meta_data();
			}
		}
		return count( array_filter( $files ) ) <= $limit;
	}
}
