<?php

/**
 * Fired on saving options
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */

/**
 * Fired on saving options.
 *
 * @since      1.0.0
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 * @author     Branko Borilovic <brana.hr@gmail.com>
 */
class Wsb_Hub3_Validator {

	// HUB-3 barcode limits, in characters.
	const RECEIVER_NAME_MAX    = 25;
	const RECEIVER_ADDRESS_MAX = 25;
	// Place is "postcode city" (max 27), postcode is always 5 digits.
	const RECEIVER_CITY_MAX    = 21;
	const REFERENCE_MAX        = 22;

	// FINA "poziv na broj" rules (models HR00, HR01): up to 3 parts of up to 12 digits.
	const REFERENCE_PARTS_MAX  = 3;
	const REFERENCE_PART_MAX   = 12;
	const MODELS               = array( '00', '01', '99' );

	// Reference date format option => PHP date() format.
	const REFERENCE_DATE_FORMATS = array(
		'ddmmyyyy' => 'dmY',
		'ddmmyy'   => 'dmy',
		'ddmm'     => 'dm',
		'mmyyyy'   => 'mY',
		'mmyy'     => 'my',
		'yyyy'     => 'Y',
		'yy'       => 'y',
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public $wsb_notices = array();
	public function __construct() {

		$this->wsb_notices = array();

	}

	/**
	 * Validate receiver name.
	 * @since    2.0.3
	 */
	function is_valid_receiver_name($name) 
	{
		if (empty($name)) {
			$this->wsb_notices[] = array( 'message' => __( 'Name can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		$length = mb_strlen($name);
		if ($length > self::RECEIVER_NAME_MAX) {
			/* translators: 1: maximum number of characters, 2: current number of characters */
			$this->wsb_notices[] = array( 'message' => sprintf( __( 'Recipient name can have at most %1$d characters (currently %2$d).', 'wsb-hub3' ), self::RECEIVER_NAME_MAX, $length ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[0-9A-Za-z .,\-()_ĐŠŽĆČđšžćč&]{2,}$/u", $name)) {
			$this->wsb_notices[] = array( 'message' => __( 'Name is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate recipient name.
	 * @since    1.0.0
	 */
	function is_valid_name($name) 
	{
		if (empty($name)) {
			$this->wsb_notices[] = array( 'message' => __( 'Name can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[0-9A-Za-z .,\-()_ĐŠŽĆČđšžćč&]{2,30}$/", $name)) {
			$this->wsb_notices[] = array( 'message' => __( 'Name is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate receiver address.
	 * @since    2.0.3
	 */
	function is_valid_receiver_address($address) 
	{
		if (empty($address)) {
			$this->wsb_notices[] = array( 'message' => __( 'Address can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		$length = mb_strlen($address);
		if ($length > self::RECEIVER_ADDRESS_MAX) {
			/* translators: 1: maximum number of characters, 2: current number of characters */
			$this->wsb_notices[] = array( 'message' => sprintf( __( 'Address can have at most %1$d characters (currently %2$d).', 'wsb-hub3' ), self::RECEIVER_ADDRESS_MAX, $length ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[0-9A-Za-z \/\-.,()ĐŠŽĆČđšžćč]{4,}$/u", $address)) {
			$this->wsb_notices[] = array( 'message' => __( 'Address is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate recipient address.
	 * @since    1.0.0
	 */
	function is_valid_address($address) 
	{
		if (empty($address)) {
			$this->wsb_notices[] = array( 'message' => __( 'Address can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[0-9A-Za-z \/\-.,()ĐŠŽĆČđšžćč]{4,27}$/", $address)) {
			$this->wsb_notices[] = array( 'message' => __( 'Address is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate barcode img type.
	 * @since    1.0.0
	 */
	function is_valid_img_type($img_type) 
	{
		if (empty($img_type)) {
			$this->wsb_notices[] = array( 'message' => __( 'Image type can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		$valid_values = array('gif','jpg','png');
		if( !in_array( $img_type, $valid_values ) ){
			$this->wsb_notices[] = array( 'message' => __( 'Image type is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate barcode img color.
	 * @since    1.0.0
	 */
	function is_valid_img_color($img_color) 
	{
		if (!preg_match("/^[0-9A-Za-z#]{4,7}$/", $img_color)) {
			$this->wsb_notices[] = array( 'message' => __( 'Image color is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate recipient city.
	 * @since    1.0.0
	 */
	function is_valid_city($city) 
	{
		if (empty($city)) {
			$this->wsb_notices[] = array( 'message' => __( 'City can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		$length = mb_strlen($city);
		if ($length > self::RECEIVER_CITY_MAX) {
			/* translators: 1: maximum number of characters, 2: current number of characters */
			$this->wsb_notices[] = array( 'message' => sprintf( __( 'City can have at most %1$d characters (currently %2$d).', 'wsb-hub3' ), self::RECEIVER_CITY_MAX, $length ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[A-Za-z .,ĐŠŽĆČđšžćč]{2,}$/u", $city)) {
			$this->wsb_notices[] = array( 'message' => __( 'City is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate recipient postal code.
	 * @since    1.0.0
	 */
	function is_valid_postcode($postcode) 
	{
		if (empty($postcode)) {
			$this->wsb_notices[] = array( 'message' => __( 'Postcode can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[0-9]{5}$/", $postcode)) {
			$this->wsb_notices[] = array( 'message' => __( 'Postcode is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate barcode padding.
	 * @since    1.0.0
	 */
	function is_valid_padding($padding) 
	{
		if (!preg_match("/^[0-9]{1,3}$/", $padding)) {
			$this->wsb_notices[] = array( 'message' => __( 'Padding value is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate recipient IBAN.
	 * @since    1.0.0
	 */
	function is_valid_iban($iban) 
	{
		if (empty($iban)) {
			$this->wsb_notices[] = array( 'message' => __( 'IBAN can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[A-Z]{2}\\d{19}$/", $iban)) {
			$this->wsb_notices[] = array( 'message' => __( 'IBAN is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		// ISO 13616: with the first 4 characters moved to the end and letters as numbers (A=10), mod 97 must be 1.
		$numeric = '';
		foreach (str_split(substr($iban, 4) . substr($iban, 0, 4)) as $char) {
			$numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
		}
		$rest = 0;
		foreach (str_split($numeric, 7) as $chunk) {
			$rest = (int) ($rest . $chunk) % 97;
		}
		if (1 !== $rest) {
			$this->wsb_notices[] = array( 'message' => __( 'IBAN check digits are not valid. Please check the IBAN for typos.', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate payment model.
	 * @since    1.0.0
	 */
	function is_valid_model($model) 
	{
		if (!in_array((string) $model, self::MODELS, true)) {
			$this->wsb_notices[] = array( 'message' => __( 'Model is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Saved payment model, falling back to HR00 when empty or unsupported.
	 */
	public static function receiver_model() {
		$model = (string) get_option( 'wsb_hub3_receiver_model' );
		return in_array( $model, self::MODELS, true ) ? $model : '00';
	}

	/**
	 * Validate that the reference layout has no more parts than FINA allows.
	 * @since    3.1.0
	 */
	function is_valid_reference_parts($model, $prefix, $format, $sufix)
	{
		if ('99' === (string) $model) {
			return true;
		}
		$parts = in_array($format, array('order-date', 'date-order'), true) ? 2 : 1;
		$parts += ('' !== (string) $prefix ? 1 : 0) + ('' !== (string) $sufix ? 1 : 0);
		if ($parts > self::REFERENCE_PARTS_MAX) {
			/* translators: %d: maximum number of reference parts */
			$this->wsb_notices[] = array( 'message' => sprintf( __( 'The payment reference can have at most %d parts. When the reference contains both the order number and the date, use either a prefix or a sufix, not both.', 'wsb-hub3' ), self::REFERENCE_PARTS_MAX ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Date part of the payment reference.
	 */
	public static function reference_date( $format, $timestamp ) {
		return isset( self::REFERENCE_DATE_FORMATS[ $format ] ) ? date( self::REFERENCE_DATE_FORMATS[ $format ], $timestamp ) : '';
	}

	/**
	 * Build a payment reference that follows FINA rules for models HR00, HR01 and HR99.
	 * $changes receives a code for every adjustment made: digits, part_length, parts, length.
	 */
	public static function build_reference( $model, $prefix, $format, $date, $order_number, $sufix, &$changes = null ) {
		$changes = array();
		if ( '99' === (string) $model ) {
			return '';
		}
		$check = '01' === (string) $model ? 1 : 0;

		$order = preg_replace( '/\D/', '', (string) $order_number );
		if ( $order !== (string) $order_number ) {
			$changes[] = 'digits';
		}
		if ( strlen( $order ) > self::REFERENCE_PART_MAX ) {
			// The last digits of an order number are the ones that tell orders apart.
			$order     = substr( $order, -self::REFERENCE_PART_MAX );
			$changes[] = 'part_length';
		}

		$parts = array();
		if ( '' !== (string) $prefix ) {
			$parts['prefix'] = (string) $prefix;
		}
		switch ( $format ) {
			case 'date':
				$parts['date'] = $date;
				break;
			case 'order-date':
				$parts['order'] = $order;
				$parts['date']  = $date;
				break;
			case 'date-order':
				$parts['date']  = $date;
				$parts['order'] = $order;
				break;
			default:
				$parts['order'] = $order;
				break;
		}
		if ( '' !== (string) $sufix ) {
			$parts['sufix'] = (string) $sufix;
		}
		$parts = array_filter( $parts, 'strlen' );

		// Leave out other parts first; the order number identifies the payment.
		foreach ( array( 'sufix', 'prefix', 'date' ) as $key ) {
			$too_many = count( $parts ) > self::REFERENCE_PARTS_MAX;
			$too_long = strlen( implode( '-', $parts ) ) + $check > self::REFERENCE_MAX;
			if ( ! $too_many && ! $too_long ) {
				break;
			}
			if ( ! isset( $parts[ $key ] ) || ( 'date' === $key && ! isset( $parts['order'] ) ) ) {
				continue;
			}
			unset( $parts[ $key ] );
			$changes[] = $too_many ? 'parts' : 'length';
		}
		if ( ! $parts ) {
			return '';
		}

		if ( $check ) {
			$last = array_key_last( $parts );
			// The check digit is appended to the last part, which may then still have at most 12 digits.
			if ( strlen( $parts[ $last ] ) >= self::REFERENCE_PART_MAX ) {
				$parts[ $last ] = substr( $parts[ $last ], -( self::REFERENCE_PART_MAX - 1 ) );
				$changes[]      = 'part_length';
			}
			$parts[ $last ] .= self::mod11ini( implode( '', $parts ) );
		}

		$changes = array_values( array_unique( $changes ) );
		return implode( '-', $parts );
	}

	/**
	 * FINA MOD11INI check digit (model HR01), computed over all digits of the reference.
	 */
	public static function mod11ini( $digits ) {
		$sum    = 0;
		$weight = 2;
		for ( $i = strlen( $digits ) - 1; $i >= 0; $i-- ) {
			$sum += (int) $digits[ $i ] * $weight++;
		}
		$rest = $sum % 11;
		return $rest < 2 ? 0 : 11 - $rest;
	}

	/**
	 * Human readable explanations for build_reference() change codes.
	 */
	public static function reference_change_messages( $changes ) {
		$messages = array(
			'digits'      => __( 'Characters other than digits were removed from the order number.', 'wsb-hub3' ),
			/* translators: %d: maximum number of digits in one reference part */
			'part_length' => sprintf( __( 'The order number was shortened to its last digits, because one part can have at most %d digits.', 'wsb-hub3' ), self::REFERENCE_PART_MAX ),
			/* translators: %d: maximum number of reference parts */
			'parts'       => sprintf( __( 'Parts were left out, because the reference can have at most %d parts.', 'wsb-hub3' ), self::REFERENCE_PARTS_MAX ),
			/* translators: %d: maximum reference length */
			'length'      => sprintf( __( 'Parts were left out, because the reference can have at most %d characters.', 'wsb-hub3' ), self::REFERENCE_MAX ),
		);
		return array_values( array_intersect_key( $messages, array_flip( (array) $changes ) ) );
	}

	/**
	 * Validate payment purpose.
	 * @since    1.0.0
	 */
	function is_valid_purpose($purpose) 
	{
		if (!preg_match("/^[A-Z]{4}$/", $purpose)) {
			$this->wsb_notices[] = array( 'message' => __( 'Purpose is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate reference prefix.
	 * @since    1.0.0
	 */
	function is_valid_reference_prefix($number) 
	{
		if (!preg_match("/^[0-9]{1,6}$/", $number)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference prefix must be a numeric value and can hold up to 6 digits.', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate reference sufix.
	 * @since    1.0.0
	 */
	function is_valid_reference_sufix($number) 
	{
		if (!preg_match("/^[0-9]{1,6}$/", $number)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference sufix must be a numeric value and can hold up to 6 digits.', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate payment description.
	 * @since    1.0.0
	 */
	function is_valid_description($description) 
	{
		if (empty($description)) {
			$this->wsb_notices[] = array( 'message' => __( 'Description can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[0-9A-Za-z \/\-_.,\[\]!()ĐŠŽĆČđšžćč]{2,35}$/", $description)) {
			$this->wsb_notices[] = array( 'message' => __( 'Description too long or contains invalid characters', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate payment reference.
	 * @since    1.0.0
	 */
	function is_valid_reference($reference) 
	{
		if (empty($reference)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[a-z \-]{2,10}$/", $reference)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate reference order nr.
	 * @since    1.0.6
	 */
	function is_valid_reference_order($reference_order) 
	{
		if (empty($reference_order)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference order can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[a-z -]{2,10}$/", $reference_order)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference Order format is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate reference date format.
	 * @since    1.0.0
	 */
	function is_valid_reference_date($reference_date) 
	{
		if (empty($reference_date)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference date format can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[dmy-]{2,8}$/", $reference_date)) {
			$this->wsb_notices[] = array( 'message' => __( 'Reference date format is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate order status.
	 * @since    1.0.0
	 */
	function is_valid_status($status) 
	{
		if (empty($status)) {
			$this->wsb_notices[] = array( 'message' => __( 'Order status can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[a-z -]{4,20}$/", $status)) {
			$this->wsb_notices[] = array( 'message' => __( 'Order status is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate email display.
	 * @since    1.0.0
	 */
	function is_valid_display_email($value) 
	{
		if (empty($value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Display in email can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[a-z0-9]{4,10}$/", $value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Display in email is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate thankyou display.
	 * @since    1.0.0
	 */
	function is_valid_display_thankyou($value) 
	{
		if (empty($value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Thank you page display parameter can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[a-z0-9]{4,10}$/", $value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Thank you page display prameter is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate order details display.
	 * @since    1.0.0
	 */
	function is_valid_display_order($value) 
	{
		if (empty($value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Order details page display parameter can not be empty', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		if (!preg_match("/^[a-z0-9]{4,10}$/", $value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Order details page display prameter is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}


	/**
	 * Validate barcode text.
	 * @since    1.0.0
	 */
	function is_valid_barcode_text($barcode_text) 
	{
		if (!preg_match("/^[0-9A-Za-z \/\-.:,_!?()%ĐŠŽĆČđšžćč]{2,150}$/", $barcode_text)) {
			$this->wsb_notices[] = array( 'message' => __( 'Barcode text contains invalid characters', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate payment description text.
	 * @since    1.0.0
	 */
	function is_valid_description_text($payment_text) 
	{
		if (!preg_match("/^[0-9A-Za-z \/\-.:,_!?()%ĐŠŽĆČđšžćč]{2,150}$/", $payment_text)) {
			$this->wsb_notices[] = array( 'message' => __( 'Payment description text contains invalid characters', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate reference sufix.
	 * @since    1.0.0
	 */
	function is_valid_img_width($number) 
	{
		if (!preg_match("/^[0-9]{2,4}$/", $number)) {
			$this->wsb_notices[] = array( 'message' => __( 'Image width must be a numeric value (2 - 4 digits).', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Validate checkbox.
	 * @since    1.0.5
	 */
	function is_valid_checkbox($value) 
	{
		if (!preg_match("/^[0-9a-z]$/", $value)) {
			$this->wsb_notices[] = array( 'message' => __( 'Checkbox value is not valid.', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
	}

}
