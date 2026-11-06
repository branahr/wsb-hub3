<?php
/**
 * A site-wide, permanently dismissible introduction after a HUB3 update.
 *
 * @since 3.1.4
 */
class Wsb_Hub3_Paybycode_Notice {

	const OPTION = 'wsb_hub3_paybycode_notice';
	const ACTION = 'wsb_hub3_dismiss_paybycode_notice';
	const NONCE = 'wsb_hub3_paybycode_notice';

	private $version;

	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Fresh installations wait until a subsequent update. Reactivation preserves dismissal.
	 */
	public static function activate( $version ) {
		add_option( self::OPTION, array( 'version' => $version, 'pending' => false, 'dismissed' => false ), '', false );
	}

	/**
	 * Missing state on an already active installation means an update from before 3.1.4.
	 * Runs on the next request, because WordPress upgrades execute the old plugin code.
	 */
	public function sync() {
		$state = get_option( self::OPTION, false );
		if ( false === $state ) {
			add_option( self::OPTION, array( 'version' => $this->version, 'pending' => true, 'dismissed' => false ), '', false );
		} elseif ( is_array( $state ) && ( $state['version'] ?? '' ) !== $this->version ) {
			$state['version'] = $this->version;
			$state['pending'] = empty( $state['dismissed'] );
			update_option( self::OPTION, $state, false );
		}
	}

	public function is_notice_screen() {
		$screen = get_current_screen();
		if ( ! $screen || is_network_admin() ) {
			return false;
		}
		if ( in_array( $screen->id, array( 'plugins', 'update-core' ), true ) ) {
			return true;
		}
		return 'woocommerce_page_wc-settings' === $screen->id
			&& isset( $_GET['tab'] ) && 'wsb_hub3_admin_tab' === $_GET['tab']
			&& ( ! isset( $_GET['section'] ) || 'paybycode' !== $_GET['section'] );
	}

	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( ! current_user_can( 'manage_woocommerce' ) || is_network_admin()
			|| ( ! $this->is_notice_screen() && ( ! $screen || 'update' !== $screen->id ) ) ) {
			return;
		}
		wp_enqueue_script( 'wsb-hub3-paybycode-notice', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/wsb-hub3-paybycode-notice.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'wsb-hub3-paybycode-notice', 'wsbHub3Notice', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( self::NONCE ),
			'plugin'  => plugin_basename( dirname( __DIR__ ) . '/wsb-hub3.php' ),
		) );
	}

	private function is_visible() {
		$state = get_option( self::OPTION, array() );
		return current_user_can( 'manage_woocommerce' ) && ! defined( 'WSB_PBC_WC_VERSION' )
			&& is_array( $state ) && ! empty( $state['pending'] ) && empty( $state['dismissed'] );
	}

	public function render() {
		if ( $this->is_notice_screen() ) {
			$this->output();
		}
	}

	private function output() {
		if ( ! $this->is_visible() ) {
			return;
		}
		$url = admin_url( 'admin.php?page=wc-settings&tab=wsb_hub3_admin_tab&section=paybycode' );
		?>
		<div id="wsb-hub3-paybycode-notice" class="notice notice-info">
			<p><strong><?php esc_html_e( 'Barcodes & QR codes with local generation', 'wsb-hub3' ); ?></strong></p>
			<p><?php esc_html_e( 'WSB PayByCode is a paid plugin that supports Croatian HUB-3 barcodes and EPC/SEPA QR codes for compatible banking apps. Payment codes are generated on your store server without sending their data to an external barcode service. It also offers payment reminders and automatic cancellations for unpaid orders.', 'wsb-hub3' ); ?></p>
			<p><?php esc_html_e( 'WSB HUB3 remains free and maintained. Deactivate WSB HUB3 when switching to PayByCode.', 'wsb-hub3' ); ?></p>
			<div class="wsb-hub3-notice-actions">
				<a class="button button-secondary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Explore WSB PayByCode', 'wsb-hub3' ); ?></a>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
					<?php wp_nonce_field( self::NONCE ); ?>
					<button type="submit" class="button-link"><?php esc_html_e( 'Do not show again', 'wsb-hub3' ); ?></button>
				</form>
			</div>
			<p class="wsb-hub3-notice-error" role="alert" hidden><?php esc_html_e( 'The notice could not be dismissed. Please try again.', 'wsb-hub3' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Fetches the notice after a successful inline update, using the newly installed code.
	 */
	public function ajax_notice() {
		check_ajax_referer( self::NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) || is_network_admin() ) {
			wp_send_json_error( null, 403 );
		}
		$this->sync();
		ob_start();
		$this->output();
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	public function dismiss() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to perform this action.' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::NONCE );
		$state = get_option( self::OPTION, array() );
		$state = is_array( $state ) ? $state : array();
		$state['version'] = $this->version;
		$state['pending'] = false;
		$state['dismissed'] = true;
		update_option( self::OPTION, $state, false );
		if ( wp_doing_ajax() ) {
			wp_send_json_success();
		}
		wp_safe_redirect( wp_get_referer() ?: admin_url( 'plugins.php' ) );
		exit;
	}
}
