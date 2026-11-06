<?php
/**
 * Local WordPress integration checks. Usage:
 * php tests/paybycode-notice.php C:/path/to/local/wordpress
 * Restores the notice option and does not change plugin activation or user accounts.
 */
if ( PHP_SAPI !== 'cli' || empty( $argv[1] ) ) {
	exit( "Usage: php tests/paybycode-notice.php /path/to/local/wordpress\n" );
}
$test_wordpress_root = rtrim( $argv[1], '/\\' ) . '/';
define( 'WP_USE_THEMES', false );
define( 'DISABLE_WP_CRON', true );
define( 'DOING_AJAX', true );
$_SERVER['HTTP_HOST'] = 'localhost';
require $test_wordpress_root . 'wp-includes/plugin.php';
add_filter( 'option_active_plugins', static function () { return array( 'woocommerce/woocommerce.php' ); } );
add_filter( 'pre_site_option_active_sitewide_plugins', static function () { return array(); } );
add_filter( 'pre_http_request', static function () { return new WP_Error( 'test_blocked', 'HTTP blocked by test' ); } );
add_filter( 'pre_wp_mail', '__return_true' );
require $test_wordpress_root . 'wp-load.php';
require_once $test_wordpress_root . 'wp-admin/includes/class-wp-screen.php';
require_once $test_wordpress_root . 'wp-admin/includes/screen.php';
require_once dirname( __DIR__ ) . '/includes/class-wsb-hub3-paybycode-notice.php';

class Hub3_Notice_Test_Response extends RuntimeException {}
add_filter( 'wp_die_ajax_handler', static function () {
	return static function () { throw new Hub3_Notice_Test_Response(); };
} );
function expect_notice( $condition, $label ) {
	if ( ! $condition ) {
		throw new RuntimeException( $label );
	}
	echo 'PASS ' . $label . "\n";
}
function notice_html( $notice ) {
	ob_start();
	$notice->render();
	return ob_get_clean();
}
function notice_request( $callback ) {
	ob_start();
	try {
		$callback();
	} catch ( Hub3_Notice_Test_Response $response ) {
		return ob_get_clean();
	}
	ob_end_clean();
	throw new RuntimeException( 'Request did not terminate.' );
}

$original_state = get_option( Wsb_Hub3_Paybycode_Notice::OPTION, false );
$users = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
if ( ! $users ) {
	throw new RuntimeException( 'A local administrator is required.' );
}
wp_set_current_user( $users[0] );
$notice = new Wsb_Hub3_Paybycode_Notice( '3.1.4' );
try {
	delete_option( Wsb_Hub3_Paybycode_Notice::OPTION );
	Wsb_Hub3_Paybycode_Notice::activate( '3.1.4' );
	$notice->sync();
	set_current_screen( 'plugins' );
	expect_notice( '' === notice_html( $notice ), 'fresh activation has no promotion' );

	delete_option( Wsb_Hub3_Paybycode_Notice::OPTION );
	$notice->sync();
	expect_notice( strpos( notice_html( $notice ), 'wsb-hub3-paybycode-notice' ) !== false, 'upgrade from legacy installation' );
	foreach ( array( 'plugins', 'update-core' ) as $screen ) {
		set_current_screen( $screen );
		expect_notice( '' !== notice_html( $notice ), 'allowed screen: ' . $screen );
	}
	foreach ( array( 'dashboard', 'edit-post', 'woocommerce_page_wc-orders' ) as $screen ) {
		set_current_screen( $screen );
		expect_notice( '' === notice_html( $notice ), 'excluded screen: ' . $screen );
	}
	set_current_screen( 'woocommerce_page_wc-settings' );
	$_GET['tab'] = 'general';
	expect_notice( '' === notice_html( $notice ), 'other WooCommerce tabs excluded' );
	$_GET['tab'] = 'wsb_hub3_admin_tab';
	expect_notice( '' !== notice_html( $notice ), 'HUB3 settings included' );
	$_GET['section'] = 'paybycode';
	expect_notice( '' === notice_html( $notice ), 'no duplicate promotion on PayByCode information page' );
	unset( $_GET['section'] );

	wp_set_current_user( 0 );
	expect_notice( '' === notice_html( $notice ), 'unauthorized users see no promotion' );
	$state = get_option( Wsb_Hub3_Paybycode_Notice::OPTION );
	notice_request( array( $notice, 'dismiss' ) );
	expect_notice( $state === get_option( Wsb_Hub3_Paybycode_Notice::OPTION ), 'unauthorized dismissal rejected' );
	wp_set_current_user( $users[0] );
	$_REQUEST['_wpnonce'] = 'invalid';
	notice_request( array( $notice, 'dismiss' ) );
	expect_notice( $state === get_option( Wsb_Hub3_Paybycode_Notice::OPTION ), 'invalid dismissal nonce rejected' );
	$_REQUEST['_wpnonce'] = wp_create_nonce( Wsb_Hub3_Paybycode_Notice::NONCE );
	$response = json_decode( notice_request( array( $notice, 'dismiss' ) ), true );
	expect_notice( ! empty( $response['success'] ) && '' === notice_html( $notice ), 'authorized dismissal persists' );
	Wsb_Hub3_Paybycode_Notice::activate( '3.1.4' );
	$next_release = new Wsb_Hub3_Paybycode_Notice( '3.1.5' );
	$next_release->sync();
	expect_notice( '' === notice_html( $next_release ), 'dismissal survives reactivation and later updates' );

	delete_option( Wsb_Hub3_Paybycode_Notice::OPTION );
	Wsb_Hub3_Paybycode_Notice::activate( '3.1.3' );
	$_REQUEST['nonce'] = wp_create_nonce( Wsb_Hub3_Paybycode_Notice::NONCE );
	$response = json_decode( notice_request( array( $notice, 'ajax_notice' ) ), true );
	expect_notice( ! empty( $response['data']['html'] ), 'inline update retrieves notice from new code' );
	$_REQUEST['nonce'] = 'invalid';
	$response = notice_request( array( $notice, 'ajax_notice' ) );
	expect_notice( '' === $response, 'invalid fetch nonce rejected' );

	set_current_screen( 'plugins' );
	define( 'WSB_PBC_WC_VERSION', 'test' );
	expect_notice( '' === notice_html( $notice ), 'active PayByCode hides promotion' );
	echo "Notice integration checks passed.\n";
} finally {
	if ( false === $original_state ) {
		delete_option( Wsb_Hub3_Paybycode_Notice::OPTION );
	} else {
		update_option( Wsb_Hub3_Paybycode_Notice::OPTION, $original_state, false );
	}
}
