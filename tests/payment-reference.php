<?php
/**
 * Standalone regression checks: php tests/payment-reference.php
 */
if ( PHP_SAPI !== 'cli' ) {
	exit;
}
require_once dirname( __DIR__ ) . '/includes/class-wsb-hub3-validator.php';

function expect_reference( $expected, $model, $order, $prefix = '', $format = 'orderid', $date = '', $suffix = '', $expected_changes = array() ) {
	$actual = Wsb_Hub3_Validator::build_reference( $model, $prefix, $format, $date, $order, $suffix, $changes );
	if ( $expected !== $actual || $expected_changes !== $changes ) {
		throw new RuntimeException( json_encode( compact( 'model', 'order', 'expected', 'actual', 'expected_changes', 'changes' ) ) );
	}
	if ( '' !== $actual && ! Wsb_Hub3_Validator::is_valid_payment_reference( $model, $actual ) ) {
		throw new RuntimeException( 'Invalid generated reference: ' . $actual );
	}
}

expect_reference( '12345-678', '00', '12345-678' );
expect_reference( '001-002-003', '00', '001-002-003' );
expect_reference( '123456789012-123456789', '00', '123456789012-123456789' );
expect_reference( '9-123-45', '00', '123-45', '9' );
expect_reference( '123-45-2026', '00', '123-45', '', 'order-date', '2026' );
expect_reference( '2026-123-45', '00', '123-45', '', 'date-order', '2026' );
expect_reference( '123-45-6', '00', '123-45-6', '9', 'order-date', '2026', '8', array( 'parts' ) );
expect_reference( '123456789012-123456789', '00', '123456789012-123456789', '9', 'orderid', '', '', array( 'length' ) );
expect_reference( '9-2026-8', '00', '123-45-6', '9', 'date', '2026', '8' );
expect_reference( '12345', '00', 'ABC123-45', '', 'orderid', '', '', array( 'digits' ) );
expect_reference( '1234', '00', '1-2-3-4', '', 'orderid', '', '', array( 'digits' ) );
expect_reference( '12345', '00', '123--45', '', 'orderid', '', '', array( 'digits' ) );
expect_reference( '234567890123', '00', '1234567890123', '', 'orderid', '', '', array( 'part_length' ) );
expect_reference( '123-455', '01', '123-45' );
expect_reference( '1-111111111119', '01', '1-111111111111', '', 'orderid', '', '', array( 'part_length' ) );
expect_reference( '111111111111-111111111', '01', '111111111111-11111111' );
expect_reference( '123455', '01', '12345' );
expect_reference( '', '99', '123-45', '9', 'order-date', '2026', '8' );

foreach ( array( '123-456', '001-002-003', '123456789012-123456789' ) as $reference ) {
	if ( ! Wsb_Hub3_Validator::is_valid_payment_reference( '00', $reference ) ) {
		throw new RuntimeException( 'Rejected valid reference: ' . $reference );
	}
}
foreach ( array( '', '1-2-3-4', '-123', '123-', '123--4', 'AB123', '1234567890123', '123456789012-1234567890', "123\n", 123, array() ) as $reference ) {
	if ( Wsb_Hub3_Validator::is_valid_payment_reference( '00', $reference ) ) {
		throw new RuntimeException( 'Accepted invalid reference: ' . json_encode( $reference ) );
	}
}
if ( ! Wsb_Hub3_Validator::is_valid_payment_reference( '01', '123-455' )
	|| Wsb_Hub3_Validator::is_valid_payment_reference( '01', '123-456' )
	|| Wsb_Hub3_Validator::is_valid_payment_reference( '99', '123' )
	|| ! Wsb_Hub3_Validator::is_valid_payment_reference( '99', '' ) ) {
	throw new RuntimeException( 'Model validation failed.' );
}

echo "Payment reference regression checks passed.\n";
