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
		if (!preg_match("/^[0-9A-Za-z .,\-()_ĐŠŽĆČđšžćč&]{2,25}$/", $name)) {
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
		if (!preg_match("/^[0-9A-Za-z \/\-.,()ĐŠŽĆČđšžćč]{4,25}$/", $address)) {
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
		if (!preg_match("/^[A-Za-z .,ĐŠŽĆČđšžćč]{2,35}$/", $city)) {
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
		return true;
	}

	/**
	 * Validate payment model.
	 * @since    1.0.0
	 */
	function is_valid_model($model) 
	{
		if (!preg_match("/^[0-9]{2}$/", $model)) {
			$this->wsb_notices[] = array( 'message' => __( 'Model is not valid', 'wsb-hub3' ), 'type' => 'error' );
			return false;
		}
		return true;
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
